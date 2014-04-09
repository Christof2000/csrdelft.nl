<?php

require_once 'ldap.class.php';
require_once 'memcached.class.php';
require_once 'verticale.class.php';
require_once 'status.class.php';
require_once 'groepen/groep.class.php';

/**
 * lid.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * Lid is een representatie van een lid in de DB. Lid is serializable en wordt door
 * LidCache in memcached gestopt. In principe roept LidCache als enige *kuch*
 * de constructor van Lid aan.
 * 
 * LidCache is een wrappertje om memcached die fijn allemaal Lid-objecten beheert.
 */
class Lid implements Serializable, Agendeerbaar {

	private $uid;
	private $profiel;
	private $kinderen = null;

	public function __construct($uid) {
		if (!$this->isValidUid($uid)) {
			throw new Exception('Geen correct [uid:' . $uid . '] opgegeven.');
		}
		$this->uid = $uid;
		$this->load($uid);
	}

	private function load($uid) {
		$db = MySql::instance();
		$query = "SELECT * FROM lid WHERE uid = '" . $db->escape($uid) . "' LIMIT 1;";
		$lid = $db->getRow($query);
		if (is_array($lid)) {
			$this->profiel = $lid;
			$this->profiel['status'] = new Status($this->profiel['status']);
		} else {
			throw new Exception('Lid [uid:' . $uid . '] kon niet geladen worden.');
		}
	}

	public static function loadByNickname($nick) {
		$db = MySql::instance();
		$query = "SELECT uid FROM lid WHERE nickname='" . $db->escape($nick) . "' LIMIT 1";
		$lid = $db->getRow($query);
		if (is_array($lid)) {
			return new Lid($lid['uid']);
		} else {
			return false;
		}
	}

	public function getRssLink() {
		$url = 'http://csrdelft.nl/forum/rss';
		if ($this->getProperty('rssToken') == '') {
			return $url . '.xml';
		} else {
			return $url . '/' . $this->getProperty('rssToken') . '.xml';
		}
	}

	// sla huidige objectstatus op in db, en update het huidige lid in de LidCache
	public function save() {
		$db = MySql::instance();
		$donotsave = array('uid', 'rssToken');

		$queryfields = array();
		foreach ($this->profiel as $veld => $value) {
			if (!in_array($veld, $donotsave)) {
				switch ($veld) {
					case 'instellingen':
						if ($value != '') {
							$value = serialize($value);
						} else {
							continue;
						}
						break;
				}
				$row = $veld . "=";
				if (is_integer($value)) {
					$row.=(int) $value;
				} else {
					$row.="'" . $db->escape($value) . "'";
				}
				$queryfields[] = $row;
			}
		}

		$query = 'UPDATE lid SET ' . implode(', ', $queryfields) . " WHERE uid='" . $this->getUid() . "';";

		if ($db->query($query) AND LidCache::updateLid($this->getUid())) {
			//als er een patroon is die ook even updaten in de cache, zodat de kindertjes kloppen.
			if ($this->getPatroon() instanceof Lid) {
				LidCache::updateLid($this->getPatroon()->getUid());
			}
			return true;
		} else {
			return false;
		}
	}

	public function logChange($diff) {
		if ($this->hasProperty('changelog')) {
			$this->profiel['changelog'] = $diff . $this->profiel['changelog'];
		} else {
			$this->profiel['changelog'] = $diff;
		}
	}

	/*
	 * Sla huidige objectstatus op in LDAP
	 */

	public function save_ldap() {
		require_once 'ldap.class.php';

		$ldap = new LDAP();

		# Alleen leden, gastleden, novieten en kringels staan in LDAP ( en Knorrie öO~ en Gerrit Uitslag )
		if (preg_match('/^S_(LID|GASTLID|NOVIET|KRINGEL|CIE)$/', $this->getStatus()) or $this->getUid() == '9808' or $this->getUid() == '0431') {

			# ldap entry in elkaar snokken
			$entry = array();
			$entry['uid'] = $this->getUid();
			$entry['givenname'] = $this->profiel['voornaam'];
			$entry['sn'] = $this->profiel['achternaam'];
			if (substr($entry['uid'], 0, 2) == 'x2') {
				$entry['cn'] = $entry['sn'];
			} else {
				$entry['cn'] = $this->getNaam();
			}
			$entry['mail'] = $this->getEmail();
			$entry['homephone'] = $this->profiel['telefoon'];
			$entry['mobile'] = $this->profiel['mobiel'];
			$entry['homepostaladdress'] = implode('$', array($this->profiel['adres'], $this->profiel['postcode'], $this->profiel['woonplaats']));
			$entry['o'] = 'C.S.R. Delft';
			$entry['mozillanickname'] = $this->getNickname();
			$entry['mozillausehtmlmail'] = 'FALSE';
			$entry['mozillahomestreet'] = $this->profiel['adres'];
			$entry['mozillahomelocalityname'] = $this->profiel['woonplaats'];
			$entry['mozillahomepostalcode'] = $this->profiel['postcode'];
			$entry['mozillahomecountryname'] = $this->profiel['land'];
			$entry['mozillahomeurl'] = $this->profiel['website'];
			$entry['description'] = 'Ledenlijst C.S.R. Delft';
			$entry['userPassword'] = $this->profiel['password'];


			$woonoord = $this->getWoonoord();
			if ($woonoord instanceof Groep) {
				$entry['ou'] = $woonoord->getNaam();
			}

			# lege velden er uit gooien
			foreach ($entry as $i => $e) {
				if ($e == '') {
					unset($entry[$i]);
				}
			}

			# bestaat deze uid al in ldap? dan wijzigen, anders aanmaken
			if ($ldap->isLid($entry['uid'])) {
				$ldap->modifyLid($entry['uid'], $entry);
			} else {
				$ldap->addLid($entry['uid'], $entry);
			}
		} else {
			# Als het een andere status is even kijken of de uid in ldap voorkomt, zo ja wissen
			if ($ldap->isLid($this->getUid())) {
				$ldap->removeLid($this->getUid());
			}
		}
		$ldap->disconnect();
		return true;
	}

	/*
	 * Om niet overal getters en setters voor te hoeven maken, en om een
	 * generiek aansprekfunctie te hebben voor het profiel, de volgende
	 * functies:
	 */

	public function hasProperty($key) {
		return array_key_exists($key, $this->profiel);
	}

	public function getProperty($key) {
		if (!$this->hasProperty($key)) {
			throw new Exception($key . ' bestaat niet in profiel');
		}
		return $this->profiel[$key];
	}

	public function setProperty($property, $contents) {
		$disallowedProps = array('uid');
		if (!array_key_exists($property, $this->profiel)) {
			return false;
		}
		if (in_array($property, $disallowedProps)) {
			return false;
		}
		if (is_string($contents)) {
			$contents = trim($contents);
		}
		switch ($property) {
			case 'password':
				$this->profiel[$property] = makepasswd($contents);
				break;
			case 'status':
				//TODO wat als een niet-bestaand?
				try {
					$this->profiel[$property] = new Status($contents);
				} catch (Exception $e) {
					$this->profiel[$property] = null;
				}
				break;
			default:
				$this->profiel[$property] = $contents;
		}
		return true;
	}

	public function getUid() {
		return $this->profiel['uid'];
	}

	public function getGeslacht() {
		return $this->profiel['geslacht'];
	}

	public function getProfiel() {
		return $this->profiel;
	}

	public function getNaam() {
		return $this->getNaamLink('full', 'plain');
	}

	public function getNickname() {
		return $this->profiel['nickname'];
	}

	public function getEmail() {
		return $this->profiel['email'];
	}

	public function getAdres() {
		return $this->profiel['adres'] . ' ' . $this->profiel['postcode'] . ' ' . $this->profiel['woonplaats'];
	}

	public function getMoot() {
		return $this->profiel['moot'];
	}

	public function getLichting() {
		return (int) $this->profiel['lidjaar'];
	}

	public function isJarig() {
		return substr($this->profiel['gebdatum'], 5, 5) == date('m-d');
	}

	public function getGeboortedatum() {
		return $this->profiel['gebdatum'];
	}

	public function getJarigOver() {
		$verjaardag = strtotime(date('Y') . '-' . date('m-d', strtotime($this->getGeboortedatum())));
		$nu = strtotime(date('Y-m-d'));
		if ($verjaardag < $nu) {
			$verjaardag = strtotime('+1 year', $verjaardag);
		}

		$dagen = round(($verjaardag - $nu) / 86400);

		if ($dagen == 0) {
			return true;
		} else {
			return $dagen;
		}
	}

	/**
	 * Voor de google-export, een formatted adres.
	 */
	public function getFormattedAddress($ouders = false) {
		$ouders ? $prefix = 'o_' : $prefix = '';
		return
				$this->getProperty($prefix . 'adres') . "\n" .
				$this->getProperty($prefix . 'postcode') . " " . $this->getProperty($prefix . 'woonplaats') . "\n" .
				$this->getProperty($prefix . 'land');
	}

	/**
	 * implements Agendeerbaar
	 *
	 * We maken een lid Agendeerbaar, zodat het in de agenda kan. Het is
	 * een beetje vieze hack omdat Agendeerbaar een enkele activiteit
	 * verwacht, terwijl een verjaardag een periodieke activiteit (elk
	 * jaar) is.
	 */
	public function getBeginMoment() {
		$jaar = date('Y');
		if (isset($GLOBALS['agenda_jaar'], $GLOBALS['agenda_maand'])) { //FIEES, Patrick.
			/*
			 * Punt is dat we het goede (opgevraagde) jaar erbij moeten zetten,
			 * anders gaat het mis op randen van weken en jaren.
			 * De maand is ook nodig, anders gaat het weer mis met de weken in januari, want dan schuift
			 * alles doordat het jaar nog op het restje van de vorige maand staat.
			 */
			$jaar = $GLOBALS['agenda_jaar'];
			if ($GLOBALS['agenda_maand'] == 1 AND substr($this->profiel['gebdatum'], 5, 2) == $GLOBALS['agenda_maand']) {
				$jaar+=1;
			}
		}
		$datum = $jaar . '-' . substr($this->profiel['gebdatum'], 5, 5) . ' 01:11:11'; // 1 b'vo
		return strtotime($datum);
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 60;
	}

	public function getDuration() {
		return 60 * 24;
	}

	public function getTitel() {
		return $this->getNaamLink('civitas');
	}

	public function getBeschrijving() {
		return $this->getTitel() . ' wordt n';
	}

	public function getLink() {
		return $this->getNaamLink('civitas', 'link');
	}

	public function isHeledag() {
		return true;
	}

//verjaardagen altijd als whole day event.
	//einde implements Agendeerbaar
	//Verticale: respectievelijk naam, letter en id. Bijvooreeld voor 'Diagonaal', 'D', 4
	public function getVerticale() {
		return Verticale::getNaamById($this->getVerticaleID());
	}

	public function getVerticaleLetter() {
		return Verticale::getLetterById($this->getVerticaleID());
	}

	public function getVerticaleID() {
		return $this->profiel['verticale'];
	}

	public function isKringleider() {
		return $this->profiel['kringleider'] != 'n';
	}

	public function isVerticaan() {
		return $this->profiel['motebal'] == 1;
	}

	public function getKring($link = false) {
		if ($this->getVerticaleLetter() == 'Geen') {
			return 'Geen kring';
		}
		$vertkring = $this->getVerticaleLetter() . '.' . $this->profiel['kring'];

		if ($this->getStatus() == 'S_KRINGEL') {
			$postfix = '(kringel)';
		} elseif ($this->isVerticaan()) {
			$postfix = '(verticaaan)';
		} elseif ($this->isKringleider()) {
			$postfix = '(kringleider)';
		} else {
			$postfix = '';
		}
		if ($link) {
			return '<a href="/communicatie/verticalen#kring' . $vertkring . '" title="Verticale ' . $this->getVerticale() . ' (' . $this->getVerticaleLetter() . ') - kring ' . $this->profiel['kring'] . '">' . $this->getVerticale() . ' ' . $vertkring . '</a> ' . $postfix;
		} else {
			return $vertkring . ' ' . $postfix;
		}
	}

	public function getPassword() {
		return $this->profiel['password'];
	}

	public function checkpw($pass) {
		// Verify SSHA hash
		$ohash = base64_decode(substr($this->getPassword(), 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash == $nhash)
			return true;
		return false;
	}

	public function getPermissies() {
		return $this->profiel['permissies'];
	}

	//Geeft object Status (door de magic functie __toString kan object een string geven als dat gevraagd wordt)
	public function getStatus() {
		return $this->profiel['status'];
	}

	public function isLid() {
		return $this->getStatus()->isLid();
	}

	public function isOudlid() {
		return $this->getStatus()->isOudlid();
	}

	public function getEchtgenootUid() {
		return $this->profiel['echtgenoot'];
	}

	public function getEchtgenoot() {
		if ($this->getEchtgenootUid() != '') {
			return LidCache::getLid($this->getEchtgenootUid());
		} else {
			return null;
		}
	}

	/**
	 * Adressering echtpaar. Als er geen expliciete addressering is
	 * opgegeven, geven we '<voorletters> <achternaam>' terug.
	 */
	public function getAdresseringechtpaar() {
		if ($this->profiel['adresseringechtpaar'] == '') {
			return $this->getNaamLink('voorletters', 'plain');
		} else {
			return $this->profiel['adresseringechtpaar'];
		}
	}

	public function getPatroonUid() {
		return $this->profiel['patroon'];
	}

	public function getPatroon() {
		if ($this->getPatroonUid() != '') {
			return LidCache::getLid($this->getPatroonUid());
		} else {
			return null;
		}
	}

	/**
	 * Kinderen ophalen voor dit lid. Lazy-loading, er komt een array van
	 * leden in het object te staan. Herladen kan geforceerd worden met
	 * $force=true
	 *
	 * PAS OP: Als er twee leden met elkaar als patroon ingevuld staan
	 * gaat het hier mis. Deze functie gaat dan oneindig proberen lid-
	 * objecten in de kinder-array te stoppen, waardoor oneindige recursie
	 * ontstaat. PHP geeft daar geen foutmeldingen van. Uit de bugtracker
	 * van PHP: "This was requested before, and this can NOT be done in a
	 * nice way.", wat je dus krijgt is een 500 internal server error,
	 * met in de apache errorlog iets als "premature end of script headers"
	 *
	 * 2010-08-30 (Jieter) Het lijkt erop dat de fix niet zo moeilijk was,
	 * namelijk checken of een kind toevallig hetzelfde uid als de patroon heeft.
	 */
	public function getKinderen($force = false) {
		if ($this->kinderen === null or $force) {
			$query = "SELECT uid FROM lid WHERE patroon='" . $this->getUid() . "';";
			$result = MySql::instance()->query2array($query);

			$this->kinderen = array();
			if (is_array($result)) {
				foreach ($result as $row) {
					//als het kind gelijk is aan het patroon ontstaat oneindige
					//recursie, dat willen we niet.
					if ($row['uid'] != $this->getPatroonUid()) {
						$this->kinderen[] = LidCache::getLid($row['uid']);
					}
				}
			}
		}
		return $this->kinderen;
	}

	public function getAantalKinderen() {
		if (!is_array($this->getKinderen())) {
			//lazy-loading: bij aanroep van deze methode even forceren
			//dat er een aanroep van this->getKinderen() geweest is.
			$this->getKinderen();
		}
		return count($this->getKinderen());
	}

	/**
	 * CorveeKwalificaties opzoeken en teruggeven van dit lid
	 */
	public function getCorveeKwalificaties() {
		require_once 'MVC/model/taken/KwalificatiesModel.class.php';
		return KwalificatiesModel::instance()->getKwalificatiesVanLid($this->getUid());
	}

	/**
	 * CorveeVrijstelling opzoeken en teruggeven van dit lid
	 */
	public function getCorveeVrijstelling() {
		require_once 'taken/model/VrijstellingenModel.class.php';
		return \VrijstellingenModel::getVrijstelling($this->getUid());
	}

	/**
	 * CorveeVoorkeuren opzoeken en teruggeven van dit lid
	 */
	public function getCorveeVoorkeuren() {
		require_once 'taken/model/VoorkeurenModel.class.php';
		return \VoorkeurenModel::getVoorkeurenVoorLid($this->getUid(), true);
	}

	/**
	 * CorveeTaken opzoeken en teruggeven van dit lid
	 */
	public function getCorveeTaken() {
		require_once 'taken/model/TakenModel.class.php';
		return \TakenModel::getTakenVoorLid($this->getUid());
	}

	//deze willen we hebben om vanuit templates handig instellingen op te halen.
	public function instelling($module, $key) {
		return LidInstellingen::get($module, $key);
	}

	/**
	 * Recente forumberichten
	 */
	public function getRecenteForumberichten() {
		return ForumPostsModel::instance()->getRecenteForumPostsVanLid($this->getUid(), 15);
	}

	/**
	 * Aantal posts op het forum voor deze gebruiker
	 */
	private $forumpostcount = -1;

	public function getForumPostCount() {
		if ($this->forumpostcount == -1) {
			require_once 'MVC/model/ForumModel.class.php';
			$this->forumpostcount = ForumPostsModel::instance()->getAantalForumPostsVoorLid($this->getUid());
		}
		return $this->forumpostcount;
	}

	/**
	 * Als het lid in een h.t. Woonwoord zit, geef dat woonoord terug.
	 */
	public function getWoonoord() {
		$groepen = Groepen::getByTypeAndUid(2, $this->getUid());
		if (is_array($groepen) AND isset($groepen[0]) AND $groepen[0] instanceof Groep) {
			return $groepen[0];
		}
		return false;
	}

	/**
	 * Geef de saldi van het lid terug, vanuit de lid-tabel.
	 * Als het goed is is die waarde actueel.
	 *
	 * (25-11-2010) $alleenRood-parameter weggehaald, werd nergens gebruikt.
	 */
	public function getSaldi() {
		return array(
			array('naam' => 'SocCie', 'saldo' => $this->profiel['soccieSaldo']),
			array('naam' => 'MaalCie', 'saldo' => $this->profiel['maalcieSaldo']));
	}

	/**
	 * Zit het huidige lid in de h.t. groep met de korte naam 'bestuur'?
	 */
	public function isBestuur() {
		$bestuur = new Groep('bestuur');
		return $bestuur->isLid($this->getUid());
	}

	/**
	 * getPasfoto()
	 *
	 * Kijkt of er een pasfoto voor het gegeven uid is, en geef die terug.
	 * Geef anders een standaard-plaatje weer.
	 *
	 * bool $square		Geef een pad naar een vierkante (150x150px) versie terug. (voor google contacts sync)
	 */
	function getPasfotoPath($vierkant = false) {
		$pasfoto = 'pasfoto/geen-foto.jpg';
		foreach (array('png', 'jpeg', 'jpg', 'gif') as $validExtension) {
			if (file_exists(PICS_PATH . '/pasfoto/' . $this->getUid() . '.' . $validExtension)) {
				$pasfoto = 'pasfoto/' . $this->getUid() . '.' . $validExtension;
				break;
			}
		}
		//als het vierkant moet, kijken of de vierkante bestaat, en anders maken.
		if ($vierkant) {
			$vierkant = PICS_PATH . '/pasfoto/' . $this->getUid() . '.vierkant.png';
			if (!file_exists($vierkant)) {
				square_crop(PICS_PATH . '/' . $pasfoto, $vierkant, 150);
			}
			return '/pasfoto/' . $this->getUid() . '.vierkant.png';
		}
		return $pasfoto;
	}

	/**
	 * Geef een url naar een pasfoto terug, of een <img>-tag met die url.
	 */
	function getPasfoto($imgTag = true, $cssClass = 'pasfoto', $vierkant = false) {
		$pasfoto = CSR_PICS . $this->getPasfotoPath($vierkant);

		if ($imgTag === true OR $imgTag === 'small') {
			$html = '<img class="' . mb_htmlentities($cssClass) . '" src="' . $pasfoto . '" ';
			if ($imgTag === 'small') {
				$html.='style="width: 100px;" ';
			}
			$html.='alt="pasfoto van ' . $this->getNaamLink('full', 'plain') . '" />';
			return $html;
		} else {
			return $pasfoto;
		}
	}

	/*
	 * Maak een link met de naam van het huidige lid naar zijn profiel.
	 *
	 * @vorm:	user, nick, bijnaam, streeplijst, full/volledig, civitas, aaidrom
	 * @mode:	link, plain
	 */

	public function getNaamLink($vorm = 'full', $mode = 'plain') {
		if ($this->profiel['voornaam'] != '') {
			$sVolledigeNaam = $this->profiel['voornaam'] . ' ';
		} else {
			$sVolledigeNaam = $this->profiel['voorletters'] . ' ';
		}
		if ($this->profiel['tussenvoegsel'] != '') {
			$sVolledigeNaam.=$this->profiel['tussenvoegsel'] . ' ';
		}
		$sVolledigeNaam.=$this->profiel['achternaam'];


		//als $vorm==='user', de instelling uit het profiel gebruiken voor vorm
		if ($vorm == 'user') {
			$vorm = LidInstellingen::get('forum', 'naamWeergave');
		}
		switch ($vorm) {
			case 'nick':
			case 'bijnaam':
				if ($this->profiel['nickname'] != '') {
					$naam = $this->profiel['nickname'];
				} else {
					$naam = $sVolledigeNaam;
				}
				break;
			//achternaam, voornaam [tussenvoegsel] voor de streeplijst
			case 'streeplijst':
				$naam = $this->profiel['achternaam'] . ', ' . $this->profiel['voornaam'];
				if ($this->profiel['tussenvoegsel'] != '') {
					$naam.=' ' . $this->profiel['tussenvoegsel'];
				}
				break;
			case 'full':
			case 'volledig':
				$naam = $sVolledigeNaam;
				break;
			case 'full_uid':
				$naam = $sVolledigeNaam . ' (' . $this->getUid() . ')';
				break;
			case 'voorletters':
				$naam = $this->profiel['voorletters'] . ' ';
				if ($this->profiel['tussenvoegsel'] != '') {
					$naam.=$this->profiel['tussenvoegsel'] . ' ';
				}
				$naam.=$this->profiel['achternaam'];
				break;
			case 'civitas':
				if ($this->profiel['status'] == 'S_NOVIET') {
					$naam = 'Noviet ' . $this->profiel['voornaam'];
					if ($this->profiel['postfix'] != '') {
						$naam.=' ' . $this->profiel['postfix'];
					}
				} elseif (in_array($this->profiel['status'], array('S_KRINGEL', 'S_NOBODY', 'S_EXLID'))) {
					if (LoginLid::mag('P_LEDEN_READ')) {
						$naam = $this->profiel['voornaam'] . ' ';
					} else {
						$naam = $this->profiel['voorletters'] . ' ';
					}
					if ($this->profiel['tussenvoegsel'] != '') {
						$naam.=$this->profiel['tussenvoegsel'] . ' ';
					}
					$naam.=$this->profiel['achternaam'];
				} else {
					//voor novieten is het Dhr./ Mevr.
					if (LoginLid::instance()->getLid()->getStatus() == 'S_NOVIET') {
						$naam = ($this->getGeslacht() == 'v') ? 'Mevr. ' : 'Dhr. ';
					} else {
						$naam = ($this->getGeslacht() == 'v') ? 'Ama. ' : 'Am. ';
					}
					if ($this->profiel['tussenvoegsel'] != '') {
						$naam.=ucfirst($this->profiel['tussenvoegsel']) . ' ';
					}
					$naam.=$this->profiel['achternaam'];
					if ($this->profiel['postfix'] != '')
						$naam.=' ' . $this->profiel['postfix'];

					//Statuschar weergeven bij oudleden, ereleden en kringels.
					if (in_array($this->profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_KRINGEL'))) {
						$naam.=' ' . $this->getStatus()->getChar();
					}
				}
				break;
			//Voor een 1 aprilgrap ooit.
			case 'aaidrom':
				$voornaam = strtolower($this->profiel['voornaam']);
				$achternaam = strtolower($this->profiel['achternaam']);

				$voor = array();
				preg_match('/^([^aeiuoy]*)(.*)$/', $voornaam, $voor);
				$achter = array();
				preg_match('/^([^aeiuoy]*)(.*)$/', $achternaam, $achter);

				$nwvoor = ucwords($achter[1] . $voor[2]);
				$nwachter = ucwords($voor[1] . $achter[2]);

				$naam = sprintf("%s %s%s", $nwvoor, ($this->profiel['tussenvoegsel'] != '') ? $this->profiel['tussenvoegsel'] . ' ' : '', $nwachter);
				break;
			case 'pasfoto':
				if ($mode == 'link') {
					$naam = $this->getPasfoto(true, 'lidfoto');
				} else {
					$naam = '$vorm [pasfoto] alleen toegestaan in linkmodus';
				}
				break;
			case 'leeg':
				$naam = '';
				break;
			default:
				$naam = 'Formaat in $vorm is onbekend.';
		}
		//niet ingelogged nooit een link laten zijn.
		$nolinks = array('x999', 'x101', 'x027', 'x222', '4444');
		if (in_array($this->getUid(), $nolinks) || !LoginLid::mag('P_LEDEN_READ')) {
			$mode = 'plain';
		}
		if ($mode === 'visitekaartje' || $mode === 'link') {

			if ($vorm !== 'pasfoto' && $this->getLichting() === 2013) {
				$naam = CsrUbb::parse('[neuzen]' . $naam . '[/neuzen]');
			}
			$k = '';
			$l = '<a href="' . CSR_ROOT . 'communicatie/profiel/' . $this->getUid() . '" title="' . $sVolledigeNaam . '" class="lidLink ' . $this->profiel['status'] . '">';

			if (($vorm === 'leeg')) {// || $mode === 'visitekaartje') && LidInstellingen::get('layout', 'visitekaartjes') == 'ja') {
				$v = str_replace(' ', '', str_replace('.', '', microtime()));
				$k = '<div id="k' . $v . '" class="visitekaartje';
				if ($this->isJarig()) {
					$k.= ' jarig';
				}
				if ($vorm === 'leeg') {
					$k.= '" style="display: block; position: static;';
				} else {
					$k.= ' init';
				}
				$k.= '">' . $this->getPasfoto('small', 'lidfoto');
				$k.= '<div class="uid uitgebreid">(';
				if (LoginLid::instance()->maySuTo($this)) {
					$k.= '<a href="/su/' . $this->getUid() . '" title="Su naar dit lid">' . $this->getUid() . '</a>';
				} else {
					$k.= $this->getUid();
				}
				$k.= ')</div>';
				$k.= '<p class="naam">' . $l . $sVolledigeNaam;
				if (!$this->isLid()) {
					$k.= '&nbsp;' . $this->getStatus()->getChar();
				}
				$k.= '</a></p><p style="word-break: break-all;"><a href="mailto:' . $this->profiel['email'] . '">' . $this->profiel['email'] . '</a><br />';
				$k.= $this->profiel['mobiel'] . '</p>';
				$k.= '<p>' . $this->profiel['adres'] . '<br />';
				$k.= $this->profiel['postcode'] . ' ' . $this->profiel['woonplaats'] . '</p>';
				$k.= '<p class="uitgebreid">' . $this->profiel['lidjaar'] . ' ' . $this->getVerticale() . '</p>';
				$k.= '</div>';
				if ($vorm === 'leeg') {
					$naam = $k . $naam;
				} else {
					$naam = $k . '<span id="v' . $v . '" class="init visite">' . $l . $naam . '</a></span>';
				}
				return '<div style="display: inline-block;">' . $naam . '</div>';
			}
			return $l . $naam . '</a>';
		} else {
			return $naam;
		}
	}

	/**
	 * Controleer of een lid al in de google-contacts-lijst staat.
	 */
	public function isInGoogleContacts() {
		require_once 'googlesync.class.php';
		if (!GoogleSync::isAuthenticated()) {
			return null;
		}
		$sync = GoogleSync::instance();

		return $sync->existsInGoogleContacts($this->getNaam());
	}

	/*
	 * We kunnen het lid-object type-casten naar string, dan wordt de
	 * naam weeergegeven. We kunnen aangeven op wat voor manier dat moet,
	 * pasfotos kunnen dus ook.
	 *
	 * Gevolg hiervan is dat we in Smarty ook {$lid} kunnen doen en in
	 * PHP gewoon echo $lid;
	 */

	public $tsVorm = 'full'; //kan zijn full, user, nick, streeplijst
	public $tsMode = 'plain'; //kan zijn pasfoto, link, html, plain;

	public function __toString() {
		if ($this->tsMode == 'pasfoto') {
			$this->getPasfoto(true);
		} else {
			return $this->getNaamLink($this->tsVorm, $this->tsMode);
		}
	}

	/**
	 * Het lid-object wordt geserialized opgeslagen in de LidCache,
	 * deze twee functies geven expliciet aan hoe dat serializen en
	 * unserializen moet gebeuren.
	 */
	public function serialize() {
		$lid = array(
			'uid' => $this->getUid(),
			'profiel' => $this->getProfiel()
		);

		return serialize($lid);
	}

	public function unserialize($serialized) {
		$lid = unserialize($serialized);
		$this->uid = $lid['uid'];
		$this->profiel = $lid['profiel'];
	}

	/**
	 * Geeft Naamlink voor uid
	 *
	 * 2012-04-18, Jieter: Zou deze niet static moeten zijn?
	 */
	public static function getNaamLinkFromUid($uid = null, $vorm = 'full', $mode = 'plain') {
		if ($uid === null) {
			$uid = LoginLid::instance()->getUid();
		}
		if (Lid::isValidUid($uid)) {
			$lid = LidCache::getLid($uid);
			if ($lid instanceof Lid) {
				return $lid->getNaamLink($vorm, $mode);
			}
		}
		return false;
	}

	/**
	 * Simpel testje voor juistheid van een uid. Dit houdt niet in dat een lid
	 * ook werkelijk bestaat, gebruik daarvoor Lid::exists();
	 */
	public static function isValidUid($uid) {
		return is_string($uid) AND preg_match('/^[a-z0-9]{4}$/', $uid) > 0;
	}

	/**
	 * Bestaat er een lid met uid $uid in de database?
	 */
	public static function exists($uid) {
		if (!Lid::isValidUid($uid))
			return false;
		return LidCache::getLid($uid) instanceof Lid;
	}

	/**
	 * Bestaat er al een lid met de bijnaam $nick in de database?
	 */
	public static function nickExists($nick) {
		return Lid::loadByNickname($nick) instanceof Lid;
	}

	/**
	 * Voeg een nieuw regeltje in de lid-tabel in met alleen een nieuw lid-nummer.
	 * PAS OP: niet multi-user safe.
	 */
	public static function createNew($lichting, $lidstatus) {
		$db = MySql::instance();

		//lichtingid zijn eerste 2 cijfers van lidnummer
		$lichtingid = substr($lichting, 2, 2);

		//volgnummer zijn de laatste 2 cijfers van lidnummer
		$query = "SELECT max(uid) AS uid FROM lid WHERE LEFT(uid, 2)='" . $lichtingid . "' LIMIT 1;";
		$result = $db->query($query);
		if ($db->numRows($result) == 1) {
			$lid = $db->result2array($result);
			$volgnummer = substr($lid[0]['uid'], 2, 2) + 1;
		} else {
			$volgnummer = '1';
		}
		if ($volgnummer > 99) {
			throw new Exception('Teveel leden dit jaar!');
		}
		//lidnummer samenstellen
		$newuid = $lichtingid . sprintf('%02d', $volgnummer);

		//probeer de nieuwe status te maken en zoek daarvoor de permissie
		$status = new Status($lidstatus);
		$perm = Status::getDefaultPermission($lidstatus);

		//alleen bij novieten studiejaar invullen
		$studiejaar = 0;
		if ($status == 'S_NOVIET') {
			$studiejaar = $lichting;
		}

		//opslaan in lid tabel
		$changelog = 'Aangemaakt als ' . $status->getDescription() . ' door [lid=' . LoginLid::instance()->getUid() . '] op [reldate]' . getDatetime() . '[/reldate][br]';

		$query = "
			INSERT INTO lid (uid, lidjaar, studiejaar, status, permissies, changelog, land, o_land)
			VALUE ('" . $newuid . "', '" . $lichting . "', '" . $studiejaar . "', '" . $status . "', '" . $perm . "', '" . $changelog . "', 'Nederland', 'Nederland');";
		if ($db->query($query)) {
			return $newuid;
		} else {
			throw new Exception('Kon geen nieuw uid aanmaken.');
		}
	}

	public static function getVerjaardagen($van, $tot, $limiet = 0) {
		$vanjaar = date('Y', $van);
		$totjaar = date('Y', $tot);
		$van = date('Y-m-d', $van);
		$tot = date('Y-m-d', $tot);

		if ($limiet > 0) {
			$limitclause = "LIMIT " . (int) $limiet;
		} else {
			$limitclause = '';
		}
		$query = "
			SELECT uid, ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) as verjaardag
			FROM lid
			WHERE (
				(CONCAT('" . $vanjaar . "', SUBSTRING(gebdatum, 5))>='" . $van . "' AND CONCAT('" . $vanjaar . "', SUBSTRING(gebdatum, 5))<'" . $tot . "')
			OR
				(CONCAT('" . $totjaar . "', SUBSTRING(gebdatum, 5))>='" . $van . "' AND CONCAT('" . $totjaar . "', SUBSTRING(gebdatum, 5))<'" . $tot . "')
			) AND
			(status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') AND
			NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC, lidjaar, gebdatum, achternaam
			" . $limitclause . ";";

		$leden = MySql::instance()->query2array($query);

		$return = array();
		if (is_array($leden)) {
			foreach ($leden as $uid) {
				$return[] = LidCache::getLid($uid['uid']);
			}
		}
		return $return;
	}

}

/**
 * Lid-objectjes bewaren in Memcached
 */
class LidCache {

	/**
	 * Deze methode gebruiken op Lid-objecten te maken. Er wordt dan
	 * automagisch voor de caching gezorgd.
	 */
	public static function getLid($uid) {
		//kek-2010ers. euhm. we hebben dus een string nodig, niet een int
		$uid = (string) $uid;

		if (!Lid::isValidUid($uid)) {
			return false;
		}

		//kijken of we dit lid al in memcached hebben zitten
		$lid = Memcached::instance()->get($uid);
		if ($lid === false) {
			try {
				//nieuw lid maken, in memcache stoppen en teruggeven.
				$lid = new Lid($uid);
				Memcached::instance()->set($uid, serialize($lid));
				return $lid;
			} catch (Exception $e) {
				return null;
			}
		}
		return unserialize($lid);
	}

	/**
	 * Weggooien van een lid uit de cache.
	 */
	public static function flushLid($uid) {
		if (!Lid::isValidUid($uid)) {
			return false;
		}
		return Memcached::instance()->delete($uid);
	}

	/**
	 * Weggooien, en meteen weer opnieuw inladen.
	 */
	public static function updateLid($uid) {
		self::flushLid($uid);
		Memcached::instance()->set($uid, serialize(new Lid($uid)));
		return true;
	}

	public static function flushAll() {
		return Memcached::instance()->flush();
	}

}

/**
 * Dit is de oude zoekfunctie, er is vervanging in lib/lid/class.lidzoeker.php,
 * maar deze functie wordt o.a. nog gebruikt door lib/include.common.php:namen2uid()
 * dus ze blijft hier nog even staan.
 *
 * TODO dus: een statische functie bouwen in lidZoeker die dit overneemt.
 */
class Zoeker {

	static function zoekLeden($zoekterm, $zoekveld, $verticale, $sort, $zoekstatus = '', $velden = array(), $aantalresultaten = null) {
		$db = MySql::instance();
		$leden = array();
		$zoekfilter = '';

		# mysql escape dingesen
		$zoekterm = trim($db->escape($zoekterm));
		$zoekveld = trim($db->escape($zoekveld));
		/* TODO: velden checken op rare dingen. Niet dat de velden() array nu buiten code opgegeven kan worden, maar het moet nog wel
		  foreach ($velden as &$veld) {
		  $veld = trim, escape, lalala
		  } */

		//Zoeken standaard in voornaam, achternaam, bijnaam en uid.
		if ($zoekveld == 'naam' AND !preg_match('/^\d{2}$/', $zoekterm)) {
			if (preg_match('/ /', trim($zoekterm))) {
				$zoekdelen = explode(' ', $zoekterm);
				$iZoekdelen = count($zoekdelen);
				if ($iZoekdelen == 2) {
					$zoekfilter = "( voornaam LIKE '%" . $zoekdelen[0] . "%' AND achternaam LIKE '%" . $zoekdelen[1] . "%' ) OR";
					$zoekfilter.="( voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
                                        nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%' )";
				} else {
					$zoekfilter = "( voornaam LIKE '%" . $zoekdelen[0] . "%' AND achternaam LIKE '%" . $zoekdelen[$iZoekdelen - 1] . "%' )";
				}
			} else {
				$zoekfilter = "
					voornaam LIKE '%{$zoekterm}%' OR achternaam LIKE '%{$zoekterm}%' OR
					nickname LIKE '%{$zoekterm}%' OR uid LIKE '%{$zoekterm}%'";
			}
		} elseif ($zoekveld == 'adres') {
			$zoekfilter = "adres LIKE '%{$zoekterm}%' OR woonplaats LIKE '%{$zoekterm}%' OR
				postcode LIKE '%{$zoekterm}%' OR REPLACE(postcode, ' ', '') LIKE '%" . str_replace(' ', '', $zoekterm) . "%'";
		} else {
			if (preg_match('/^\d{2}$/', $zoekterm) AND ($zoekveld == 'uid' OR $zoekveld == 'naam')) {
				//zoeken op lichtingen...
				$zoekfilter = "SUBSTRING(uid, 1, 2)='" . $zoekterm . "'";
			} else {
				$zoekfilter = "{$zoekveld} LIKE '%{$zoekterm}%'";
			}
		}

		$sort = $db->escape($sort);

		# In welke status wordt gezocht, is afhankelijk van wat voor rechten de
		# ingelogd persoon heeft. 
		#
		# R_LID en R_OUDLID hebben beide P_LEDEN_READ en P_OUDLEDEN_READ en kunnen 
		# de volgende afkortingen gebruiken:
		#  - '' (lege string) of alleleden: novieten, (gast)leden, kringels, ere- en oudleden
		#  - leden :  						novieten, (gast)leden en kringels
		#  - oudleden : 					oud- en ereleden
		#  - allepersonen:					novieten, (gast)leden, kringels, oud- en ereleden, overleden leden en nobodies (alleen geen commissies)
		# én alleen voor OUDLEDENMOD:
		#  - nobodies : 					alleen nobodies 

		$statusfilter = '';
		if ($zoekstatus == 'alleleden') {
			$zoekstatus = '';
		}
		if ($zoekstatus == 'allepersonen') {
			$zoekstatus = array('S_NOVIET', 'S_LID', 'S_GASTLID', 'S_OUDLID', 'S_ERELID', 'S_KRINGEL', 'S_OVERLEDEN', 'S_NOBODY', 'S_EXLID');
		}
		if (is_array($zoekstatus)) {
			//we gaan nu gewoon simpelweg statussen aan elkaar plakken. LET OP: deze functie doet nu
			//geen controle of een gebruiker dat mag, dat moet dus eerder gebeuren.
			$statusfilter = "status='" . implode("' OR status='", $zoekstatus) . "'";
		} else {
			# we zoeken in leden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet oudleden alleen heeft gekozen
			if (
					(LoginLid::mag('P_LEDEN_READ') and !LoginLid::mag('P_OUDLEDEN_READ') ) or
					(LoginLid::mag('P_LEDEN_READ') and LoginLid::mag('P_OUDLEDEN_READ') and $zoekstatus != 'oudleden')
			) {
				$statusfilter .= "status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL'";
			}
			# we zoeken in oudleden als
			# 1. ingelogde persoon dat alleen maar mag of
			# 2. ingelogde persoon leden en oudleden mag zoeken, maar niet leden alleen heeft gekozen
			if (
					(!LoginLid::mag('P_LEDEN_READ') and LoginLid::mag('P_OUDLEDEN_READ') ) or
					(LoginLid::mag('P_LEDEN_READ') and LoginLid::mag('P_OUDLEDEN_READ') and $zoekstatus != 'leden')
			) {
				if ($statusfilter != '')
					$statusfilter .= " OR ";
				$statusfilter .= "status='S_OUDLID' OR status='S_ERELID'";
			}
			# we zoeken in nobodies als
			# de ingelogde persoon dat mag EN daarom gevraagd heeft
			if (LoginLid::mag('P_OUDLEDEN_MOD') and $zoekstatus === 'nobodies') {
				# alle voorgaande filters worden ongedaan gemaakt en er wordt alleen op nobodies gezocht
				$statusfilter = "status='S_NOBODY' OR status='S_EXLID'";
			}
		}

		# als er een specifieke moot is opgegeven, gaan we alleen in die moot zoeken
		$mootfilter = ($verticale != 'alle') ? 'AND verticale=\'' . $verticale . '\' ' : '';
		# is er een maximum aantal resultaten gewenst
		$limiet = ($aantalresultaten === null) ? '' : 'LIMIT ' . (int) $aantalresultaten;

		# controleer of we ueberhaupt wel wat te zoeken hebben hier
		if ($statusfilter != '') {
			# standaardvelden
			if (empty($velden)) {
				$velden = array('uid', 'nickname', 'voornaam', 'tussenvoegsel', 'achternaam', 'postfix', 'adres', 'postcode', 'woonplaats', 'land', 'telefoon',
					'mobiel', 'email', 'geslacht', 'voornamen', 'icq', 'msn', 'skype', 'jid', 'website', 'beroep', 'studie', 'studiejaar', 'lidjaar',
					'gebdatum', 'moot', 'kring', 'kringleider', 'motebal', 'verticale',
					'o_adres', 'o_postcode', 'o_woonplaats', 'o_land', 'o_telefoon',
					'kerk', 'muziek', 'eetwens', 'status');
			}

			# velden kiezen om terug te geven
			$velden_sql = implode(', ', $velden);
			$velden_sql = str_replace('corvee_punten_totaal', 'corvee_punten+corvee_punten_bonus AS corvee_punten_totaal', $velden_sql);
			$sZoeken = "
				SELECT
					" . $velden_sql . "
				FROM
					lid
				WHERE
					(" . $zoekfilter . ")
				AND
					($statusfilter)
				{$mootfilter}
				ORDER BY
					{$sort}
				{$limiet}";
			$result = $db->select($sZoeken);
			if ($result !== false and $db->numRows($result) > 0) {
				while ($lid = $db->next($result))
					$leden[] = $lid;
			}
		}

		return $leden;
	}

}

?>
