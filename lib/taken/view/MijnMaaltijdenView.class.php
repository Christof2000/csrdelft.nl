<?php

/**
 * MijnMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends TemplateView {

	public function __construct(array $maaltijden, array $aanmeldingen = null) {
		parent::__construct($maaltijden, 'Maaltijdenketzer');

		$toonlijst = array();
		foreach ($this->model as $maaltijd) {
			$mid = $maaltijd->getMaaltijdId();
			$toonlijst[$mid] = MijnMaaltijdenController::magMaaltijdlijstTonen($maaltijd);
			if (!array_key_exists($mid, $aanmeldingen)) {
				$aanmeldingen[$mid] = false;
			}
		}
		$this->smarty->assign('toonlijst', $toonlijst);
		$this->smarty->assign('standaardprijs', sprintf('%.2f', floatval(Instellingen::get('maaltijden', 'standaard_prijs'))));
		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('aanmeldingen', $aanmeldingen);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/maaltijd/mijn_maaltijden.tpl');
	}

}

class MijnMaaltijdView extends TemplateView {

	public function __construct(Maaltijd $maaltijd, MaaltijdAanmelding $aanmelding = null) {
		parent::__construct($maaltijd);
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $aanmelding);
		$this->smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($maaltijd));
		$this->smarty->assign('standaardprijs', sprintf('%.2f', floatval(Instellingen::get('maaltijden', 'standaard_prijs'))));
	}

	public function view() {
		$this->smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
	}

}
