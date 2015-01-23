<?php

require_once 'model/entity/groepen/GroepTab.enum.php';
require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenBeheerTable extends DataTable {

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'opvolg_naam');
		$this->dataUrl = groepenUrl . A::Beheren;
		$this->titel = 'Beheer ' . lcfirst(str_replace('Model', '', get_class($model)));
		$this->hideColumn('samenvatting');
		$this->hideColumn('omschrijving');
		$this->hideColumn('website');
		$this->hideColumn('door_uid');
		$this->searchColumn('naam');
		$this->searchColumn('jaargang');
		$this->searchColumn('status');
		$this->searchColumn('soort');

		$create = new DataTableKnop('== 0', $this->tableId, groepenUrl . A::Aanmaken, 'post popup', 'Toevoegen', 'Nieuwe groep toevoegen', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, groepenUrl . A::Wijzigen, 'post popup', 'Wijzigen', 'Wijzig groep eigenschappen', 'edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, groepenUrl . A::Verwijderen, 'post confirm', 'Verwijderen', 'Definitief verwijderen', 'delete');
		$this->addKnop($delete);
	}

}

class GroepenBeheerData extends DataTableResponse {

	public function getJson($groep) {
		$array = $groep->jsonSerialize();

		$array['detailSource'] = groepenUrl . $groep->id . '/leden';
		$array['samenvatting'] = null;
		$array['omschrijving'] = null;
		$array['website'] = null;
		$array['door_uid'] = null;

		return parent::getJson($array);
	}

}

class GroepForm extends DataTableForm {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, $action, get_class($groep) . ' ' . A::Wijzigen);
		$fields = $this->generateFields();
	}

}

class GroepLedenTable extends DataTable {

	public function __construct(GroepLedenModel $model, Groep $groep) {
		parent::__construct($model::orm, 'Leden van ' . $groep->naam, 'status');
		$this->dataUrl = groepenUrl . $groep->id . '/leden';
		$this->hideColumn('uid', false);
		$this->searchColumn('uid');
		$this->setColumnTitle('uid', 'Lidnaam');
		$this->setColumnTitle('door_uid', 'Aangemeld door');

		$create = new DataTableKnop('== 0', $this->tableId, groepenUrl . $groep->id . '/' . A::Aanmelden, 'post popup', 'Aanmelden', 'Lid toevoegen', 'user_add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Bewerken, 'post popup', 'Bewerken', 'Lidmaatschap bewerken', 'user_edit');
		$this->addKnop($update);

		$delete = new DataTableKnop('>= 1', $this->tableId, groepenUrl . $groep->id . '/' . A::Afmelden, 'post confirm', 'Afmelden', 'Leden verwijderen', 'user_delete');
		$this->addKnop($delete);
	}

}

class GroepLedenData extends DataTableResponse {

	public function getJson($lid) {
		$array = $lid->jsonSerialize();

		$array['uid'] = ProfielModel::getLink($array['uid'], 'civitas');
		$array['door_uid'] = ProfielModel::getLink($array['door_uid'], 'civitas');

		return parent::getJson($array);
	}

}

class GroepLidForm extends DataTableForm {

	public function __construct(GroepLid $lid, $action, array $blacklist = array()) {
		parent::__construct($lid, groepenUrl . $lid->groep_id . '/' . $action, ucfirst($action));
		$fields = $this->generateFields();
		if (!empty($blacklist)) {
			$fields['uid']->blacklist = $blacklist;
			$fields['uid']->required = true;
			$fields['uid']->readonly = false;
		}
		$fields['uid']->hidden = false;
		$fields['door_uid']->required = true;
		$fields['door_uid']->readonly = true;
		$fields['door_uid']->hidden = true;
	}

}

class GroepAanmeldingForm extends InlineForm {

	public function __construct(GroepLid $lid, array $suggestions = array()) {
		parent::__construct($lid, groepenUrl . $lid->groep_id . '/' . A::Bewerken . '/' . $lid->uid);
		$this->field = new TextField('opmerking', $lid->opmerking, 'Functie of opmerking bij lidmaatschap');
		$this->field->suggestions[] = $suggestions;
	}

}

class GroepenView extends SmartyTemplateView {

	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct(GroepenModel $model, $groepen) {
		parent::__construct($groepen);
		$this->pagina = CmsPaginaModel::get($model::orm);
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
		}
		$this->titel = $this->pagina->titel;
	}

	public function view() {
		$this->smarty->display('groepen/menu_pagina.tpl');
		//$this->smarty->display('groepen/inhoudsopgave.tpl'); //FIXME: cannot iterate more than once over PDO statement of groepen
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->model as $groep) {
			$view = new GroepView($groep, GroepTab::Lijst);
			$view->view();
		}
	}

}

class GroepView extends SmartyTemplateView {

	protected $tab;
	protected $tabContent;

	public function __construct(Groep $groep, $groepTab) {
		parent::__construct($groep, $groep->naam);
		$this->tab = $groepTab;
		switch ($this->tab) {
			default:
			case GroepTab::Lijst:
				$this->tabContent = new GroepLijstView($groep);
				break;
			case GroepTab::Pasfotos:
				$this->tabContent = new GroepPasfotosView($groep);
				break;
			case GroepTab::Statistiek:
				$this->tabContent = new GroepStatistiekView($groep);
				break;
			case GroepTab::Emails:
				$this->tabContent = new GroepEmailsView($groep);
				break;
		}
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('groepUrl', groepenUrl . $this->model->id);
		$this->smarty->assign('tab', $this->tab);
		$this->smarty->assign('tabContent', $this->tabContent);
		$this->smarty->display('groepen/groep.tpl');
	}

}

abstract class GroepTabView implements View {

	protected $groep;

	public function __construct(Groep $groep) {
		$this->groep = $groep;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

}

class GroepLijstView extends GroepTabView {

	private $forms = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		if ($groep instanceof Commissie OR $groep instanceof Bestuur) {
			$suggestions = CommissieFunctie::getTypeOptions();
		} else {
			$suggestions = array();
			foreach ($this->groep->getLeden() as $lid) {
				$suggestions[] = $lid->opmerking;
			}
		}
		foreach ($this->groep->getLeden() as $lid) {
			$this->forms[] = new GroepAanmeldingForm($lid, A::Bewerken, $suggestions);
		}
	}

	public function view() {
		echo '<table class="groepLeden"><tbody>';
		foreach ($this->forms as $form) {
			echo '<tr><td>' . ProfielModel::getLink($form->getModel()->uid, 'civitas') . '</td>';
			echo '<td>';
			$form->view();
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

}

class GroepPasfotosView extends GroepTabView {

	public function view() {
		foreach ($this->groep->getLeden() as $lid) {
			echo '<div class="pasfoto">' . ProfielModel::getLink($lid->uid, 'pasfoto') . '</div>';
		}
	}

}

class GroepStatistiekView extends GroepTabView {

	public function view() {
		echo '<table class="groepStats">';
		foreach ($this->groep->getStatistieken() as $title => $stat) {
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
			if (!is_array($stat)) {
				echo '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
		}
		echo '</tbody></table>';
	}

}

class GroepEmailsView extends GroepTabView {

	private $emails = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$this->emails[] = $profiel->getPrimaryEmail();
			}
		}
	}

	public function view() {
		echo '<div class="emails">' . implode(', ', $this->emails) . '</div>';
	}

}
