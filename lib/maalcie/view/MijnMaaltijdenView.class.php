<?php

/**
 * MijnMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends SmartyTemplateView {

	private $aanmeldingen;

	public function __construct(array $maaltijden, array $aanmeldingen = null) {
		parent::__construct($maaltijden, 'Maaltijdenketzer');
		$this->aanmeldingen = $aanmeldingen;
		foreach ($this->model as $maaltijd) {
			$mid = $maaltijd->getMaaltijdId();
			if (!array_key_exists($mid, $this->aanmeldingen)) {
				$this->aanmeldingen[$mid] = false;
			}
		}
	}

	public function view() {
		$this->smarty->assign('standaardprijs', intval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('aanmeldingen', $this->aanmeldingen);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijden.tpl');
	}

}

class MijnMaaltijdView extends SmartyTemplateView {

	private $aanmelding;

	public function __construct(Maaltijd $maaltijd, MaaltijdAanmelding $aanmelding = null) {
		parent::__construct($maaltijd);
		$this->aanmelding = $aanmelding;
	}

	public function view() {
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $this->aanmelding);
		$this->smarty->assign('standaardprijs', intval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijd_lijst.tpl');
	}

}
