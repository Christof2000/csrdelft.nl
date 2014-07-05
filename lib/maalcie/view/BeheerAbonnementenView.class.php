<?php

/**
 * BeheerAbonnementenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle abonnementen en waarschuwingen.
 * 
 */
class BeheerAbonnementenView extends TemplateView {

	public function __construct(array $matrix, array $repetities, $alleenWaarschuwingen = false, $ingeschakeld = null) {
		parent::__construct($matrix, 'Beheer abonnementen');

		$field = new LidField('voor_lid', null, 'Toon abonnementen van persoon:', 'allepersonen');
		$form = new Formulier(null, 'taken-subform-abos', Instellingen::get('taken', 'url') . '/voorlid');
		$form->addFields(array($field));
		$this->smarty->assign('form', $form);

		$status = 'abo';
		if (is_bool($ingeschakeld)) {
			$status = ($ingeschakeld ? 'in' : 'abo'); // uit
		}
		if ($alleenWaarschuwingen) {
			$status = 'waarschuwing';
		}
		$this->smarty->assign('toon', $status);

		$this->smarty->assign('aborepetities', MaaltijdRepetitiesModel::getAbonneerbareRepetities());
		$this->smarty->assign('repetities', $repetities);
		$this->smarty->assign('matrix', $this->model);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/beheer_abonnementen.tpl');
	}

}

class BeheerAbonnementenLijstView extends TemplateView {

	public function __construct(array $matrix) {
		parent::__construct($matrix);
	}

	public function view() {
		echo '<tr id="taken-melding"><td id="taken-melding-veld">' . SimpleHTML::getMelding() . '</td></tr>';
		foreach ($this->model as $uid => $abonnementen) {
			$this->smarty->assign('uid', $uid);
			$this->smarty->assign('abonnementen', $abonnementen);
			$this->smarty->display('maalcie/abonnement/beheer_abonnement_lijst.tpl');
		}
	}

}

class BeheerAbonnementView extends TemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
		$this->smarty->assign('abonnement', $this->model);
		$this->smarty->assign('lidid', $this->model->getLidId());
		$this->smarty->assign('uid', $this->model->getVanLidId());
	}

	public function view() {
		echo '<td id="taken-melding-veld">' . SimpleHTML::getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/beheer_abonnement_veld.tpl');
	}

}
