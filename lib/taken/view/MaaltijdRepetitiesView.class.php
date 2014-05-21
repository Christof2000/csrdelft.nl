<?php

/**
 * MaaltijdRepetitiesView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle maaltijd-repetities om te beheren.
 * 
 */
class MaaltijdRepetitiesView extends TemplateView {

	public function __construct(array $repetities) {
		parent::__construct($repetities, 'Beheer maaltijdrepetities');
		$this->smarty->assign('repetities', $this->model);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetities.tpl');
	}

}

class MaaltijdRepetitieView extends TemplateView {

	public function __construct(MaltijdRepetitie $repetitie) {
		parent::__construct($repetitie);
		$this->smarty->assign('repetitie', $this->model);
	}

	public function view() {
		echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		$this->smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
	}

}
