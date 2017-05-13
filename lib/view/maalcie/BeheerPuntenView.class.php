<?php
namespace CsrDelft\view\maalcie;
use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerPuntenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle corveepunten van alle leden.
 * 
 */
class BeheerPuntenView extends SmartyTemplateView {

	private $functies;

	public function __construct(array $matrix, array $functies) {
		parent::__construct($matrix, 'Beheer corveepunten');
		$this->functies = $functies;
	}

	public function view() {
		$this->smarty->assign('matrix', $this->model);
		$this->smarty->assign('functies', $this->functies);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveepunt/beheer_punten.tpl');
	}

}

class BeheerPuntenLidView extends SmartyTemplateView {

	public function __construct(array $puntenlijst) {
		parent::__construct($puntenlijst);
	}

	public function view() {
		$this->smarty->assign('puntenlijst', $this->model);
		$this->smarty->display('maalcie/corveepunt/beheer_punten_lijst.tpl');
	}

}
