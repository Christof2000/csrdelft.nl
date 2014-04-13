<?php

/*
 * class.groepcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Een verzameling contentclassen voor de groepenketzer.
 *
 * Groepcontent					Weergeven van een groep & bewerken en etc.
 * Groepencontent				Weergeven van een groepenoverzicht
 * Groepengeschiedeniscontent	Weergeven van een mooie patchwork van groepjes.
 * GroepenProfielConcent		Weergeven van groepenlijstje in profiel
 * GroepUbbContent				Weergeven van enkele zaken van een groep met een ubb-tag
 */

require_once 'groepen/groep.class.php';
require_once 'lichting.class.php';

class Groepcontent extends TemplateView {

	private $action = 'view';

	public function setAction($action) {
		$this->action = $action;
	}

	public function getTitel() {
		return $_GET['gtype'] . ' - ' . $this->model->getNaam();
	}

	/*
	 * Deze functie geeft een formulierding voor het eenvoudig toevoegen van leden
	 * aan een bepaalde groep.
	 */

	private function getLidAdder() {
		if (isset($_POST['rawNamen']) AND trim($_POST['rawNamen']) != '') {
			$return = '';

			//uitmaken waarin we allemaal zoeken, standaard in de normale leden, wellicht
			//ook in oudleden en nobodies
			$zoekin = array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL');
			if (isset($_POST['filterOud'])) {
				$zoekin[] = 'S_OUDLID';
				$zoekin[] = 'S_ERELID';
			}
			if (isset($_POST['filterNobody']) AND $this->model->isAdmin()) {
				$zoekin[] = 'S_NOBODY';
				$zoekin[] = 'S_EXLID';
				$zoekin[] = 'S_OVERLEDEN';
				$zoekin[] = 'S_CIE';
			}

			$leden = namen2uid($_POST['rawNamen'], $zoekin);

			if (is_array($leden) AND count($leden) != 0) {
				$return.='<table border="0">';

				foreach ($leden as $aGroepUid) {
					if (isset($aGroepUid['uid'])) {
						//naam is gevonden en uniek, dus direct goed.
						$return.='<tr>';
						$return.='<td><input type="hidden" name="naam[]" value="' . $aGroepUid['uid'] . '" />' . $aGroepUid['naam'] . '</td>';
					} else {
						//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
						if (count($aGroepUid['naamOpties']) > 0) {
							$return.='<tr><td><select name="naam[]" class="tekst">';
							foreach ($aGroepUid['naamOpties'] as $aNaamOptie) {
								$return.='<option value="' . $aNaamOptie['uid'] . '">' . $aNaamOptie['naam'] . '</option>';
							}
							$return.='</select></td>';
						}//dingen die niets opleveren wordt niets voor weergegeven.
					}
					if ($this->model->magBewerken()) {
						$return.='<td><input type="text" maxlength="25" name="functie[]" /></td></tr>';
					} else {
						$return.='<td>' . $this->getFunctieSelector() . '</td></tr>';
					}
				}
				$return.='</table>';
				return $return;
			}
		}
		return false;
	}

	/*
	 * Niet-admins kunnen kiezen uit een van te voren vastgesteld lijstje met functies, zodat
	 * we  niet allerlei onzinnamen krijgen zoals Kücherführer enzo.
	 */

	private function getFunctieSelector($uid = '') {
		$return = '';
		$aFuncties = array('Q.Q.', 'Praeses', 'Fiscus', 'Redacteur', 'Computeur', 'Archivaris',
			'Bibliothecaris', 'Statisticus', 'Fotocommissaris', '', 'Koemissaris', 'Regisseur',
			'Lichttechnicus', 'Geluidstechnicus', 'Adviseur', 'Internetman', 'Posterman',
			'Corveemanager', 'Provisor', 'HO', 'HJ', 'Onderhuurder');
		sort($aFuncties);
		$return.='<select name="functie[]" class="tekst">';
		foreach ($aFuncties as $sFunctie) {
			$return.='<option value="' . $sFunctie . '"';
			if ($sFunctie == $this->model->getFunctie($uid)) {
				$return.='selected="selected"';
			}
			$return.='>' . $sFunctie . '</option>';
		}
		$return.='</select>';
		return $return;
	}

	public function getAanmeldfilters() {
		$filters = array(
			'' => 'Niet aanmeldbaar',
			'P_LOGGED_IN' => 'Alle leden',
			'geslacht:m' => 'Alleen mannen',
			'geslacht:v' => 'Alleen vrouwen');

		//verticalen.
		foreach (Verticale::getLetters() as $key => $verticale) {
			if ($verticale == 'Geen') {
				continue;
			}
			$filter = 'verticale:' . $verticale;
			$filters[$filter] = 'Verticale ' . Verticale::getNaamById($key);
		}

		//lichtingen
		$nu = Lichting::getJongsteLichting();
		for ($lichting = $nu; $lichting >= ($nu - 7); $lichting--) {
			$filters['lichting:' . $lichting] = 'Lichting ' . $lichting;
		}

		return $filters;
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('opvolgerVoorganger', $this->model->getOpvolgerVoorganger());
		$this->smarty->assign('action', $this->action);
		$this->smarty->assign('groeptypes', Groepen::getGroeptypes());
		$this->smarty->assign('aanmeldfilters', $this->getAanmeldfilters());
		$oud = null;
		if (array_key_exists('oudegroep', $_SESSION)) {
			$oud = $_SESSION['oudegroep'];
		}
		$this->smarty->assign('oudegroep', $oud);
		if ($this->action == 'addLid') {
			$this->smarty->assign('lidAdder', $this->getLidAdder());
		}
		$this->smarty->display('groepen/groep.tpl');
	}

}

class Groepencontent extends TemplateView {

	private $action = 'view';

	public function setAction($action) {
		$this->action = $action;
	}

	public function getTitel() {
		return 'Groepen - ' . $this->model->getNaam();
	}

	public function view() {
		$this->smarty->assign('groepen', $this->model);
		$this->smarty->assign('gtype', $this->model->getNaam());
		$this->smarty->assign('groeptypes', Groepen::getGroeptypes());
		$this->smarty->assign('action', $this->action);
		$this->smarty->display('groepen/groepen.tpl');
	}

}

class GroepledenContent extends TemplateView {

	private $actie = 'standaard';

	public function __construct(OldGroep $groep, $actie = 'standaard') {
		parent::__construct($groep);
		$this->actie = $actie;
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('groepen/groepleden.tpl');
	}

}

class Groepgeschiedeniscontent extends TemplateView {

	public function getTitel() {
		return 'Groepen - ' . $this->model->getNaam();
	}

	public function view() {
		$jaren = 5;
		$maanden = $jaren * 12;
		echo '<table style="border-collapse: collapse;">';
		echo '<tr>';
		for ($i = 2008; $i >= (2008 - $jaren); $i--) {
			echo '<td style="font-size: 8px; width: 10px;" colspan="12">' . $i . '</td>';
		}
		echo '</tr>';
		echo '<tr>';
		for ($i = 0; $i <= $maanden; $i++) {
			echo '<td style="max-width: 10px;">&nbsp;</td>';
		}
		echo '</tr>';
		foreach ($this->model->getGroepen() as $groep) {
			echo '<tr>';
			$startspacer = 12 - substr($groep->getBegin(), 5, 2);
			if ($startspacer != 0) {
				echo '<td colspan="' . $startspacer . '" style="font-size: 8px; background-color: lightgray;">(' . $startspacer . ')</td>';
			}

			$oudeGr = OldGroep::getGroepgeschiedenis($groep->getSnaam(), $jaren);
			foreach ($oudeGr as $grp) {
				$duration = $grp['duration'];
				if ($duration <= 0) {
					$duration = 12;
				}
				echo '<td colspan="' . $duration . '" style="font-size: 8px; border: 1px solid black; padding: 2px; width: 150px; text-align: left;">';
				echo '<a href="/actueel/groepen/' . $this->model->getNaam() . '/' . $grp['id'] . '">' . $grp['naam'] . '</a>';

				echo '</td>';
			}
			if (count($oudeGr) < $maanden) {
				$spacer = $maanden - count($oudeGr);
				echo '<td colspan="' . $spacer . '" style="background-color: lightgray;">&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

}

/*
 * Weergave van groepen in het profiel.
 */

class GroepenProfielContent extends TemplateView {

	private $display_lower_limit = 8;
	private $display_upper_limit = 12;

	public function getHTML() {
		//per status in een array rammen
		$groepenPerStatus = array();
		foreach (Groepen::getByUid($this->model) as $groep) {
			$groepenPerStatus[$groep->getStatus()][] = $groep;
		}

		$return = '';
		foreach ($groepenPerStatus as $status => $groepen) {
			$return.='<div class="groep' . $status . '">';
			$return.='<h6>' . str_replace(array('ht', 'ot', 'ft'), array('h.t.', 'o.t.', 'f.t.'), $status) . ' groepen:</h6>';
			$return.='<ul class="groeplijst nobullets">';
			$i = 0;
			$style = '';

			//zorg dat als het aantal tussen onder en bovengrens in zit gewoon alles wordt weergegeven,
			$display_limit = $this->display_lower_limit;
			if (count($groepen) > $this->display_lower_limit AND count($groepen) < $this->display_upper_limit) {
				$display_limit = $this->display_upper_limit;
			}

			foreach ($groepen as $groep) {
				if ($i > $display_limit) {
					$style = 'style="display: none;" ';
				}
				//op een of andere manier werkt het hier niet als ik een class-property gebruik,
				//dus daarom maar met inline style.
				$return.='<li ' . $style . '>' . $groep->getLink() . '</li>';
				$i++;
			}

			$return.='</ul>';
			if ($i > $display_limit) {
				$return.='<a onclick="jQuery(this).parent().children(\'ul\').children().show(); jQuery(this).remove();" class="handje">&raquo; meer </a>';
			}

			$return.='</div>';
		}
		return $return;
	}

	public function view() {
		echo $this->getHTML();
	}

}

/*
 * Contentclasse voor de groep-ubb-tag
 */

class GroepUbbContent extends TemplateView {

	public function getHTML() {
		$this->smarty->assign('groep', $this->model);
		return $this->smarty->fetch('groepen/groep.ubb.tpl');
	}

	public function view() {
		echo $this->getHTML();
	}

}

class GroepStatsContent extends TemplateView {

	public function view() {
		$stats = $this->model->getStats();
		if (!is_array($stats)) {
			return;
		}
		echo '<table class="query_table">';
		foreach ($stats as $title => $stat) {
			if (!is_array($stat)) {
				continue;
			}
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
			$rowColor = false;
			foreach ($stat as $row) {
				//kleurtjes omwisselen
				if ($rowColor) {
					$style = 'background-color: #ccc;';
				} else {
					$style = '';
				}
				$rowColor = (!$rowColor);
				echo '<tr>';
				foreach ($row as $column) {
					echo '<td style="width: 50%; ' . $style . '">' . $column . '</td>';
				}
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
	}

}

class GroepEmailContent extends TemplateView {

	public function view() {
		$emails = array();
		$groepleden = $this->model->getLeden();
		if (is_array($groepleden)) {
			foreach ($groepleden as $groeplid) {
				$lid = LidCache::getLid($groeplid['uid']);
				if ($lid instanceof Lid AND $lid->getEmail() != '') {
					$emails[] = $lid->getEmail();
				}
			}
		}
		echo '<div class="emails">' . implode(', ', $emails) . '</div>';
	}

}

?>
