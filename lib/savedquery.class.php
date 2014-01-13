<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------

class SavedQuery {

	private $queryID;
	private $beschrijving;
	private $permissie = 'P_ADMIN';
	private $result = null;
	private $resultCount = 0;

	public function __construct($id) {
		$this->queryID = (int) $id;
		$this->load();
	}

	private function load() {
		$db = MySql::instance();
		//query ophalen
		$selectQuery = "
			SELECT
				savedquery, beschrijving, permissie
			FROM
				savedquery
			WHERE
				ID=" . $this->queryID . "
			LIMIT 1;";
		$result = $db->query($selectQuery);

		if ($result !== false AND $db->numRows($result) == 1) {
			$querydata = $db->next($result);

			if ($this->magWeergeven($querydata['permissie'])) {
				//beschrijving opslaan
				$this->beschrijving = $querydata['beschrijving'];
				$this->permissie = $querydata['permissie'];

				//query nog uitvoeren...
				$queryResult = $db->query($querydata['savedquery']);

				if ($queryResult !== false) {
					if ($db->numRows($queryResult) == 0) {
						$this->result[] = array('Leeg resultaatset' => 'Query leverde geen resultaten terug.');
					} else {
						$this->result = $db->result2array($queryResult);
						$this->resultCount = count($this->result);
					}
				} elseif (LoginLid::instance()->hasPermission('P_ADMIN')) {
					$this->result[] = array('Mysql_error' => mysql_error());
				}
			}
		}
	}

	public function getID() {
		return $this->queryID;
	}

	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function getHeaders() {
		if ($this->hasResult()) {
			return array_keys($this->result[0]);
		} else {
			return array();
		}
	}

	public function hasResult() {
		return is_array($this->result);
	}

	public function getResult() {
		return $this->result;
	}

	public function count() {
		return $this->resultCount;
	}

	//Query's mogen worden weergegeven als de permissiestring toegelaten wordt door 
	//Lid::hasPermission()' of als gebruiker P_ADMIN heeft.
	public static function magWeergeven($permissie) {
		$loginlid = LoginLid::instance();
		return $loginlid->hasPermission($permissie) OR $loginlid->hasPermission('P_ADMIN');
	}

	public function magBekijken() {
		return $this->magWeergeven($this->permissie);
	}

	//geef een array terug met de query's die de huidige gebruiker mag bekijken.
	static public function getQueries() {
		$db = MySql::instance();
		$selectQuery = "
			SELECT
				ID, beschrijving, permissie, categorie
			FROM
				savedquery
			ORDER BY categorie, beschrijving;";
		$result = $db->query($selectQuery);
		$return = array();
		while ($data = $db->next($result)) {
			if (self::magWeergeven($data['permissie'])) {
				$return[] = $data;
			}
		}
		return $return;
	}

}

class SavedQueryContent extends TemplateView {

	private $sq;

	public function __construct(SavedQuery $sq = null) {
		parent::__construct();
		$this->sq = $sq;
	}

	public static function render_header($name) {
		switch ($name) {
			case 'uid_naam': return 'Naam';
				break;
			case 'groep_naam': return 'Groep';
				break;
			case 'onderwerp_link': return 'Onderwerp';
				break;
			case 'med_link': return 'Mededeling';
				break;
			default:
				if (substr($name, 0, 10) == 'groep_naam') {
					return substr($name, 11);
				}
		}
		return $name;
	}

	public static function render_field($name, $contents) {
		if ($name == 'uid_naam') {
			return LidCache::getLid($contents)->getNaamLink('full', 'link');
		} elseif ($name == 'onderwerp_link') { //link naar het forum.
			return '<a href="/communicatie/forum/onderwerp/' . $contents . '">' . $contents . '</a>';
		} elseif (substr($name, 0, 10) == 'groep_naam' AND $contents != '') {
			require_once('groepen/groep.class.php');
			return Groep::ids2links($contents, '<br />');
		} elseif ($name == 'med_link') { //link naar een mededeling.
			return '<a href="/actueel/mededelingen/' . $contents . '">' . $contents . '</a>';
		}

		return mb_htmlentities($contents);
	}

	public function render_queryResult() {
		if ($this->sq->hasResult()) {
			$sq = $this->sq;
			$return = $sq->getBeschrijving() . ' (' . $sq->count() . ' regels)<br /><table class="query_table">';

			//table header
			$return.='<tr>';
			foreach ($sq->getHeaders() as $kopje) {
				$return.='<th>' . self::render_header($kopje) . '</th>';
			}
			$return.='</tr>';

			$rowColor = false;
			foreach ($sq->getResult() as $rij) {
				//kleurtjes omwisselen
				$style = $rowColor ? 'style="background-color: #ccc;"' : '';
				$rowColor = (!$rowColor);

				$return.='<tr>';
				foreach ($rij as $key => $veld) {
					$return.='<td ' . $style . '>' . self::render_field($key, $veld) . '</td>';
				}
				$return.='</tr>';
			}
			$return.='</table>';
		} else {
			//foutmelding in geval van geen resultaat, dus of geen query die bestaat, of niet
			//voldoende rechten.
			$return = 'Query (' . $this->sq->getID() . ') bestaat niet, geeft een fout, of u heeft niet voldoende rechten.';
		}
		return $return;
	}

	public function getQueryselector() {
		//als er een query ingeladen is, die highlighten
		$id = $this->sq instanceof SavedQuery ? $this->sq->getID() : 0;

		$return = '<a class="knop" href="#" onclick="$(\'#sqSelector\').toggle();">Laat queryselector zien.</a>';
		$return.='<div id="sqSelector" ';
		if ($id != 0) {
			$return.='class="verborgen"';
		}
		$return.='>';
		$current = '';
		foreach (SavedQuery::getQueries() as $query) {
			if ($current != $query['categorie']) {
				if ($current != '') {
					$return.='</ul></div>';
				}
				$return.='<div class="sqCategorie" style="float: left; width: 450px; margin-right: 20px; margin-bottom: 10px;"><strong>' . $query['categorie'] . '</strong><ul>';
				$current = $query['categorie'];
			}
			$return.='<li><a href="query.php?id=' . $query['ID'] . '">';
			if ($id == $query['ID']) {
				$return.='<em>';
			}
			$return.=mb_htmlentities($query['beschrijving']);
			if ($id == $query['ID']) {
				$return.='</em>';
			}
			$return.='</a></li>';
		}
		$return.='</ul></div></div><div class="clear"></div>';
		return $return;
	}

	public function view() {
		echo '<h1>Opgeslagen query\'s</h1>';
		echo $this->getQueryselector();

		//render query if selected and allowed
		if ($this->sq != null && $this->sq->magBekijken()) {
			echo $this->render_queryResult();
		}
	}

}

?>
