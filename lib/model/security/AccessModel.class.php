<?php

require_once 'model/entity/security/AccessRoles.enum.php';
require_once 'model/security/LoginModel.class.php';
require_once 'model/LichtingModel.class.php';
require_once 'model/VerticalenModel.class.php';

/**
 * AccessModel.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * RBAC met MAC en DAC implementatie.
 * 
 * @see http://csrc.nist.gov/groups/SNS/rbac/faq.html
 * 
 */
class AccessModel extends CachedPersistenceModel {

	const orm = 'AccessControl';

	protected static $instance;

	public static function get($environment, $action, $resource) {
		$ac = self::instance()->getAccessControl($environment, $action, $resource);
		if ($ac) {
			return $ac->subject;
		}
		return null;
	}

	/**
	 * @param Account $subject Het lid dat de gevraagde permissies zou moeten bezitten.
	 * @param string $permission Gevraagde permissie(s).
	 * @param boolean $allowAuthByToken Of het subject geauthenticeerd mag zijn door een token,
	 * 										anders werkt het alsof gebruiker x999 is.
	 * 
	 * Met deze functies kan op één of meerdere permissies worden getest,
	 * onderling gescheiden door komma's. Als een lid één van de
	 * permissies 'heeft', geeft de functie true terug. Het is dus een
	 * logische OF tussen de verschillende te testen permissies. Een
	 * permissie kan met een uitroepteken geïnverteerd worden.
	 * 
	 * Voorbeeldjes:
	 *  groep:novcie				geeft true leden van de h.t. NovCie.
	 *  groep:pubcie,groep:bestuur	geeft true voor leden van h.t. bestuur en h.t. novcie
	 *  groep:SocCie>Fiscus			geeft true voor h.t. Soccielid met functie fiscus
	 *  geslacht:m					geeft true voor alle mannelijke leden
	 *  verticale:d					geeft true voor alle leden van verticale d.
	 *  !lichting:2009				geeft true voor iedereen behalve lichting 2009.
	 * 
	 * Gecompliceerde voorbeeld:
	 * 		groep:novcie+groep:maalcie|1337,groep:bestuur
	 * 
	 * Equivalent met haakjes:
	 * 		(groep:novcie AND (groep:maalcie OR 1337)) OR groep:bestuur
	 * 
	 * Geeft toegang aan:
	 * 		de mensen die én in de NovCie zitten én in de MaalCie zitten
	 * 		of mensen die in de NovCie zitten en lidnummer 1337 hebben
	 * 		of mensen die in het bestuur zitten
	 * 
	 */
	public static function mag(Account $subject, $permission, $allowAuthByToken = false) {

		// Als voor het ingelogde lid een permissie gevraagd wordt
		// en deze sessie is ingelogd per token: doe extra check of dat mag.
		// Alleen als inloggen per token ($allowAuthByToken) toegestaan is
		// testen we met de permissies van het per token ingelogde account,
		// anders met niet-ingelogd.
		if (LoginModel::instance()->isAuthenticatedByToken() AND $subject->uid == LoginModel::getUid() AND ! $allowAuthByToken) {
			$subject = AccountModel::get('x999');
		}

		// case insensitive
		return self::instance()->hasPermission($subject, strtoupper($permission));
	}

	/**
	 * Partially ordered Role Hierarchy:
	 * 
	 * A subject can have multiple roles.	<- NIET ondersteund met MAC, wel met DAC
	 * A role can have multiple subjects.
	 * A role can have many permissions.
	 * A permission can be assigned to many roles.
	 * An operation can be assigned many permissions.
	 * A permission can be assigned to many operations.
	 */
	private $roles = array();
	/**
	 * Permissies die we gebruiken om te vergelijken met de permissies van een gebruiker.
	 */
	private $permissions = array();
	/**
	 * Geldige prefixes voor rechten
	 * @var array
	 */
	private static $prefix = array('GROEP', 'VERTICALE', 'VERTICALELEIDER', 'GESLACHT', 'LICHTING', 'LIDJAAR', 'OUDERJAARS', 'EERSTEJAARS');

	protected function __construct() {
		parent::__construct('security/');
		$this->loadPermissions();
	}

	private function getAccessControl($environment, $action, $resource) {
		return $this->retrieveByPrimaryKey(array($environment, $action, $resource));
	}

	public function getDefaultPermissionRole($lidstatus) {
		switch ($lidstatus) {
			case LidStatus::Kringel:
			case LidStatus::Noviet:
			case LidStatus::Lid:
			case LidStatus::Gastlid: return AccessRoles::Lid;
			case LidStatus::Oudlid:
			case LidStatus::Erelid: return AccessRoles::Oudlid;
			case LidStatus::Commissie:
			case LidStatus::Overleden:
			case LidStatus::Exlid:
			case LidStatus::Nobody: return AccessRoles::Nobody;
			default: throw new Exception('Ongeldige LidStatus');
		}
	}

	public function getPermissionSuggestions() {
		$valid = array_keys($this->permissions);
		$valid[] = 'groep:1234';
		$valid[] = 'groep:KorteNaam';
		$valid[] = 'geslacht:m';
		$valid[] = 'geslacht:v';
		$valid[] = 'ouderjaars';
		$valid[] = 'eerstejaars';
		foreach (VerticalenModel::instance()->prefetch() as $verticale) {
			$valid[] = 'verticale:' . $verticale->naam;
		}
		$jong = LichtingModel::getJongsteLichting();
		for ($jaar = $jong; $jaar > $jong - 7; $jaar--) {
			$valid[] = 'lichting:' . $jaar;
		}
		return $valid;
	}

	public function isValidPerm($permission) {
		// case insensitive
		$permission = strtoupper($permission);

		// Is de gevraagde permissie het uid van de gevraagde gebruiker?
		if (AccountModel::isValidUid(strtolower($permission))) {
			return true;
		}

		// Is de gevraagde permissie voorgedefinieerd?
		if (isset($this->permissions[$permission])) {
			return true;
		}

		// splits permissie in type, waarde en rol
		$p = explode(':', $permission);
		if (in_array($p[0], self::$prefix) AND sizeof($p) <= 3) {
			if (isset($p[1]) AND $p[1] == '') {
				return false;
			}
			if (isset($p[2]) AND $p[2] == '') {
				return false;
			}
			return true;
		}

		return false;
	}

	public function isValidRole($role) {
		if (isset($this->roles[$role])) {
			return true;
		}
		return false;
	}

	/**
	 * Hier staan de 'vaste' permissies, die gegeven worden door de PubCie.
	 * In tegenstelling tot de variabele permissies zoals lidmaatschap van een groep.
	 * 
	 * READ = Rechten om het onderdeel in te zien
	 * POST = Rechten om iets toe te voegen
	 * MOD  = Moderate rechten, dus verwijderen enzo
	 * 
	 * Let op: de rechten zijn cumulatief (bijv: 7=4+2+1, 3=2+1)
	 * als je hiervan afwijkt, kun je (bewust) niveau's uitsluiten (bijv 5=4+1, sluit 2 uit)
	 * de levels worden omgezet in een karakter met die ASCII waarde (dit zijn vaak niet-leesbare symbolen, bijv #8=backspace)
	 * elke karakter van een string representeert een onderdeel
	 *
	 */
	private function loadPermissions() {
		// see if cached
		$key = 'permissions-' . getlastmod();
		if ($this->isCached($key, true) AND $this->isCached('roles', true)) {
			$this->permissions = $this->getCached($key, true);
			$this->roles = $this->getCached('roles', true);
			return;
		}

		// build permissions
		$this->permissions = array(
			'P_PUBLIC'			 => $this->createPermStr(0, 0), // Iedereen op het Internet
			'P_LOGGED_IN'		 => $this->createPermStr(1, 0), // Eigen profiel raadplegen
			'P_PROFIEL_EDIT'	 => $this->createPermStr(1 + 2, 0), // Eigen gegevens aanpassen
			'P_ALLEEN_OUDLID'	 => $this->createPermStr(4, 0), // Specifiek voor oudleden [[let op: niet cumulatief]]
			'P_VERJAARDAGEN'	 => $this->createPermStr(1, 1), // Verjaardagen van leden zien
			'P_LEDEN_READ'		 => $this->createPermStr(1 + 2, 1), // Gegevens van leden raadplegen
			'P_OUDLEDEN_READ'	 => $this->createPermStr(1 + 2 + 4, 1), // Gegevens van oudleden raadplegen
			'P_LEDEN_MOD'		 => $this->createPermStr(1 + 2 + 4 + 8, 1), // (Oud)ledengegevens aanpassen
			'P_FORUM_READ'		 => $this->createPermStr(1, 2), // Forum lezen
			'P_FORUM_POST'		 => $this->createPermStr(1 + 2, 2), // Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD'		 => $this->createPermStr(1 + 2 + 4, 2), // Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_FORUM_BELANGRIJK' => $this->createPermStr(8, 2), // Forum belangrijk (de)markeren  [[let op: niet cumulatief]]
			'P_FORUM_ADMIN'		 => $this->createPermStr(16, 2), // Forum-admin mag deel-fora aanmaken en rechten wijzigen  [[let op: niet cumulatief]]
			'P_AGENDA_READ'		 => $this->createPermStr(1, 3), // Agenda bekijken
			'P_AGENDA_ADD'		 => $this->createPermStr(1 + 2, 3), // Items toevoegen aan de agenda
			'P_AGENDA_MOD'		 => $this->createPermStr(1 + 2 + 4, 3), // Items beheren in de agenda
			'P_DOCS_READ'		 => $this->createPermStr(1, 4), // Documenten-rubriek lezen
			'P_DOCS_POST'		 => $this->createPermStr(1 + 2, 4), // Documenten verwijderen of erbij plaatsen
			'P_DOCS_MOD'		 => $this->createPermStr(1 + 2 + 4, 4), // Documenten aanpassen
			'P_ALBUM_READ'		 => $this->createPermStr(1, 5), // Foto-album bekijken
			'P_ALBUM_DOWN'		 => $this->createPermStr(1 + 2, 5), // Foto-album downloaden
			'P_ALBUM_ADD'		 => $this->createPermStr(1 + 2 + 4, 5), // Fotos uploaden en albums toevoegen
			'P_ALBUM_MOD'		 => $this->createPermStr(1 + 2 + 4 + 8, 5), // Foto-albums aanpassen
			'P_ALBUM_DEL'		 => $this->createPermStr(1 + 2 + 4 + 8 + 16, 5), // Fotos uit fotoalbum verwijderen
			'P_BIEB_READ'		 => $this->createPermStr(1, 6), // Bibliotheek lezen
			'P_BIEB_EDIT'		 => $this->createPermStr(1 + 2, 6), // Bibliotheek wijzigen
			'P_BIEB_MOD'		 => $this->createPermStr(1 + 2 + 4, 6), // Bibliotheek zowel wijzigen als lezen
			'P_NEWS_POST'		 => $this->createPermStr(1, 7), // Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD'		 => $this->createPermStr(1 + 2, 7), // Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_NEWS_PUBLISH'	 => $this->createPermStr(1 + 2 + 4, 7), // Nieuws publiceren en rechten bepalen
			'P_MAAL_IK'			 => $this->createPermStr(1, 8), // kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_MAAL_MOD'		 => $this->createPermStr(1 + 2, 8), // mag maaltijden beheren (MaalCie P)
			'P_MAAL_SALDI'		 => $this->createPermStr(1 + 2 + 4, 8), // mag het MaalCie saldo aanpassen van iedereen (MaalCie fiscus)
			'P_CORVEE_IK'		 => $this->createPermStr(1, 9), // kan voorkeuren aangeven voor corveetaken
			'P_CORVEE_MOD'		 => $this->createPermStr(1 + 2, 9), // mag corveetaken beheren (CorveeCaesar)
			'P_CORVEE_SCHED'	 => $this->createPermStr(1 + 2 + 4, 9), // mag de automatische corvee-indeler beheren
			'P_MAIL_POST'		 => $this->createPermStr(1, 10), // mag berichtjes in de courant rossen
			'P_MAIL_COMPOSE'	 => $this->createPermStr(1 + 2, 10), // mag alle berichtjes in de courant bewerken, en volgorde wijzigen
			'P_MAIL_SEND'		 => $this->createPermStr(1 + 2 + 4, 10), // mag de courant verzenden
			'P_ADMIN'			 => $this->createPermStr(1, 11) // Super-admin
		);
		/**
		 * Deze waarden worden samengesteld uit bovenstaande permissies en
		 * worden in de gebruikersprofielen gebruikt als aanduiding voor
		 * welke permissie-groep (Role) de gebruiker in zit (max. 1 momenteel).
		 */
		$p = $this->permissions;

		// Permission Assignment:
		$this->roles = array(
			AccessRoles::Nobody	 => $p['P_PUBLIC'] | $p['P_FORUM_READ'] | $p['P_AGENDA_READ'] | $p['P_ALBUM_READ'],
			AccessRoles::Lid	 => $p['P_PROFIEL_EDIT'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_AGENDA_READ'] | $p['P_DOCS_READ'] | $p['P_BIEB_READ'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_POST'] | $p['P_NEWS_POST'] | $p['P_ALBUM_MOD']
		);

		// use | $p[] for hierarchical RBAC (inheritance between roles)
		// use & ~$p[] for constrained RBAC (separation of duties)

		$this->roles[AccessRoles::Eter] = $this->roles[AccessRoles::Nobody] | $p['P_LOGGED_IN'] | $p['P_PROFIEL_EDIT'] | $p['P_MAAL_IK'];
		$this->roles[AccessRoles::Oudlid] = $this->roles[AccessRoles::Lid] | $p['P_ALLEEN_OUDLID'];
		$this->roles[AccessRoles::Basfcie] = $this->roles[AccessRoles::Lid] | $p['P_DOCS_MOD'] | $p['P_ALBUM_DEL'] | $p['P_BIEB_MOD'];
		$this->roles[AccessRoles::Maalcie] = $this->roles[AccessRoles::Lid] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'];
		$this->roles[AccessRoles::Bestuur] = $this->roles[AccessRoles::Basfcie] | $this->roles[AccessRoles::Maalcie] | $p['P_LEDEN_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_AGENDA_MOD'] | $p['P_NEWS_MOD'] | $p['P_MAIL_COMPOSE'] | $p['P_ALBUM_DEL'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAIL_COMPOSE'] | $p['P_FORUM_BELANGRIJK'];
		$this->roles[AccessRoles::Pubcie] = $this->roles[AccessRoles::Bestuur] | $p['P_ADMIN'] | $p['P_MAIL_SEND'] | $p['P_CORVEE_SCHED'] | $p['P_FORUM_ADMIN'];

		// save in cache
		$this->setCache($key, $this->permissions, true);
		$this->setCache('roles', $this->roles, true);
	}

	/**
	 * Create permission string with character which has ascii value of request level.
	 *
	 * @param int $level           permissiewaarde
	 * @param int $onderdeelnummer starts at zero
	 * @return string permission string
	 */
	private function createPermStr($level, $onderdeelnummer) {
		$nulperm = str_repeat(chr(0), 15);
		return substr_replace($nulperm, chr($level), $onderdeelnummer, 1);
	}

	private function hasPermission(Account $subject, $permission) {
		// Rechten vergeten?
		if (empty($permission)) {
			return false;
		}

		// Try cache
		$key = 'hasPermission' . crc32(implode('-', array($subject->uid, $permission)));
		if ($this->isCached($key)) {
			return $this->getCached($key);
		}

		// OR
		if (strpos($permission, ',') !== false) {
			// Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
			// gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
			// dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
			// gebruiker heeft hij ook rechten.
			$p = explode(',', $permission);
			$result = false;
			foreach ($p as $perm) {
				$result |= $this->hasPermission($subject, $perm);
			}
		}
		// AND
		elseif (strpos($permission, '+') !== false) {
			// Gecombineerde permissie:
			// gebruiker moet alle permissies bezitten
			$p = explode('+', $permission);
			$result = true;
			foreach ($p as $perm) {
				$result &= $this->hasPermission($subject, $perm);
			}
		}
		// OR (secondary)
		elseif (strpos($permission, '|') !== false) {
			// Mogelijkheid voor OR binnen een AND
			// Hierdoor zijn er geen haakjes nodig in de syntax voor niet al te ingewikkelde statements.
			// Statements waarbij haakjes wel nodig zijn moet je niet willen.
			$p = explode('|', $permission);
			$result = false;
			foreach ($p as $perm) {
				$result |= $this->hasPermission($subject, $perm);
			}
		}
		// Negatie van een permissie (gebruiker mag deze permissie niet bezitten)
		elseif (startsWith($permission, '!')) {
			$result = !$this->hasPermission($subject, substr($permission, 1));
		}
		// Is de gevraagde permissie het uid van de gevraagde gebruiker?
		elseif ($subject->uid == strtolower($permission)) {
			$result = true;
		}
		// Is de gevraagde permissie voorgedefinieerd?
		elseif (isset($this->permissions[$permission])) {
			$result = $this->mandatoryAccessControl($subject, $permission);
		} else {
			$result = $this->discretionaryAccessControl($subject, $permission);
		}

		// Save result in cache
		$this->setCache($key, $result);

		return $result;
	}

	private function mandatoryAccessControl(Account $subject, $permission) {

		// zoek de rechten van de gebruiker op
		$role = $subject->perm_role;

		// ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!$this->isValidRole($role)) {
			return false;
		}

		// zoek de codes op
		$gevraagd = $this->permissions[$permission];
		$lidheeft = $this->roles[$role];

		/**
		 * permissies zijn een string, waarin elk kararakter de
		 * waarde heeft van een permissielevel voor een bepaald onderdeel.
		 * 
		 * de mogelijke *verschillende* permissies voor een onderdeel zijn machten van twee:
		 * 1, 2, 4, 8, etc
		 * elk van deze waardes kan onderscheiden worden in een permissie, ook als je ze met elkaar combineert
		 * bijv.  3=1+2, 7=1+2+4, 5=1+4, 6=2+4, 12=4+8, etc
		 * 
		 * $gevraagd is de gevraagde permissie als string,
		 * de permissies van de gebruiker $lidheeft kunnen we bij $this->lid opvragen
		 * als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
		 * zijn aan de gevraagde permissie. In dat geval bestaat de permissie
		 * van het account dus minimaal uit de gevraagde permissie
		 * 
		 * Bij het AND-en, wordt elke karakter bitwise vergeleken, dat betekent:
		 * - elke karakter van de string omzetten in de ASCII-waarde
		 *   (bijv. ?=63, A=65, a=97, etc zie ook http:* www.ascii.cl/)
		 * - deze ASCII-waarde omzetten in een binaire getal
		 *   (bijv. 2=00010, 4=00100, 5=00101, 14=01110, etc)
		 * - de bits van het binaire getal een-voor-een vergelijken met de bits van het binaire getal uit de
		 *   andere string. Als ze overeenkomen worden ze bewaard.
		 *   (bijv. 3&5=1 => 00011&00101=00001)
		 * 
		 * voorbeeld (met de getallen 0 tot 7 als ASCII-waardes ipv de symbolen, voor de leesbaarheid)
		 * gevraagd:  P_FORUM_MOD : 0000000700
		 * account heeft: R_LID   : 0005544500
		 * AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
		 * 
		 * gevraagd:  P_DOCS_READ : 0000004000
		 * account heeft: R_LID   : 0005544500
		 * AND resultaat          : 0000004000 -> ja!
		 * 
		 */
		$resultaat = $gevraagd & $lidheeft;

		if ($resultaat === $gevraagd) {
			return true;
		}

		return false;
	}

	private function discretionaryAccessControl(Account $subject, $permission) {

		// haal het profiel van de gebruiker op
		$profiel = ProfielModel::get($subject->uid);

		// ga alleen verder als er een geldig profiel wordt teruggegeven
		if (!$profiel) {
			return false;
		}

		// splits permissie in type, waarde en rol
		$p = explode(':', $permission, 3);
		if (isset($p[0])) {
			$prefix = $p[0];
		} else {
			return false;
		}
		if (isset($p[1])) {
			$gevraagd = $p[1];
		} else {
			$gevraagd = false;
		}
		if (isset($p[2])) {
			$role = $p[2];
		} else {
			$role = false;
		}

		switch ($prefix) {

			/**
			 * Behoort een lid tot een bepaalde (h.t.) groep?
			 * Als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer de h.t.
			 * groep met die korte naam erbij, als het getal is uiteraard de groep met dat id.
			 * Met de toevoeging '>Fiscus' kan ook specifieke functie geëist worden binnen een groep.
			 */
			case 'GROEP':

				try {
					require_once 'model/entity/groepen/OldGroep.class.php';

					$groep = new OldGroep($gevraagd); // zoek groep
					if (!$groep->isLid($subject->uid)) {
						return false;
					}

					// wordt er een functie gevraagd?
					if ($role) {
						$functie = $groep->getFunctie($subject->uid);
						if ($role == strtoupper($functie[0])) {
							return true;
						}
					} else {
						return true;
					}
				} catch (Exception $e) {
					// gevraagde groep bestaat niet
				}

				return false;

			/**
			 * Is lid een verticaleleider?
			 */
			case 'VERTICALELEIDER':

				$gevraagd = $profiel->verticale;
				$role = 'LEIDER';

			/**
			 * Behoort een lid tot een bepaalde verticale?
			 */
			case 'VERTICALE':

				// zoek verticale
				$verticale = $profiel->getVerticale();

				if (!$verticale) {
					return false;
				} elseif ($gevraagd == strtoupper($verticale->letter) OR $gevraagd == strtoupper($verticale->naam)) {

					// wordt er een role gevraagd?
					if (!$role) {
						return true;
					} elseif ($role == 'LEIDER' AND $profiel->verticaleleider) {
						return true;
					}
				}

				return false;

			/**
			 * Is lid man of vrouw?
			 */
			case 'GESLACHT':

				if ($gevraagd == strtoupper($profiel->geslacht)) {
					// Niet ingelogd heeft geslacht m dus check of ingelogd
					if ($this->hasPermission($subject, 'P_LOGGED_IN')) {
						return true;
					}
				}

				return false;

			/**
			 * Behoort een lid tot een bepaalde lichting?
			 */
			case 'LIDJAAR':
			case 'LICHTING':

				if ($gevraagd == $profiel->lidjaar) {
					return true;
				}

				return false;

			case 'OUDERJAARS':

				$lidjaar = $profiel->lidjaar;
				// Niet ingelogd heeft lichting 0
				if ($lidjaar > 0 AND LichtingModel::getJongsteLichting() > $lidjaar) {
					return true;
				}

				return false;

			case 'EERSTEJAARS':

				$lidjaar = $profiel->lidjaar;
				// Niet ingelogd heeft lichting 0
				if ($lidjaar > 0 AND LichtingModel::getJongsteLichting() == $lidjaar) {
					return true;
				}

				return false;

			default:
				return false;
		}

		return false;
	}

}