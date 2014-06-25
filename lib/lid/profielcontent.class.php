<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# class.profielcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van een ledenprofiel
# -------------------------------------------------------------------

/**
 * Profiel bekijken
 */
class ProfielContent extends TemplateView {

	function __construct(Lid $lid) {
		parent::__construct($lid, 'Het profiel van ' . $lid->getNaam());
	}

	function view() {
		$profhtml = array();
		foreach ($this->model->getProfiel() as $key => $value) {
			if (!is_array($value) AND $key != 'changelog') {
				$profhtml[$key] = mb_htmlentities($value);
			} elseif ($key == 'changelog') {
				$profhtml[$key] = $value;
			}
		}

		$woonoord = $this->model->getWoonoord();
		if ($woonoord instanceof OldGroep) {
			$profhtml['woonoord'] = '<strong>' . $woonoord->getLink() . '</strong>';
		} else {
			$profhtml['woonoord'] = '';
		}

		require_once 'groepen/groepcontent.class.php';
		$profhtml['groepen'] = new GroepenProfielContent($this->model->getUid());

		if (LoginLid::instance()->getUid() == $this->model->getUid() || LoginLid::mag('P_MAAL_MOD')) {
			$profhtml['recenteAanmeldingen'] = $this->model->getRecenteAanmeldingen();
			$profhtml['abos'] = $this->model->getMaaltijdAbonnementen();
		}

		//de html template in elkaar draaien en weergeven
		$this->smarty->assign('profhtml', $profhtml);

		require_once 'lid/saldi.class.php';
		if (Saldi::magGrafiekZien($this->model->getUid())) {
			$this->smarty->assign('saldografiek', Saldi::getDatapoints($this->model->getUid(), 60));
		}

		$this->smarty->assign('corveepunten', $this->model->getProperty('corvee_punten'));
		$this->smarty->assign('corveebonus', $this->model->getProperty('corvee_punten_bonus'));
		$this->smarty->assign('corveetaken', $this->model->getCorveeTaken());
		$this->smarty->assign('corveevoorkeuren', $this->model->getCorveeVoorkeuren());
		$this->smarty->assign('corveevrijstelling', $this->model->getCorveeVrijstelling());
		$this->smarty->assign('corveekwalificaties', $this->model->getCorveeKwalificaties());

		require_once 'bibliotheek/catalogus.class.php';
		$this->smarty->assign('boeken', Catalogus::getBoekenByUid($this->model->getUid(), 'eigendom'));
		$this->smarty->assign('gerecenseerdeboeken', Catalogus::getBoekenByUid($this->model->getUid(), 'gerecenseerd'));

		$this->smarty->assign('isAdmin', LoginLid::mag('P_ADMIN'));
		//TODO check role vs permission R_BESTUUR
		$this->smarty->assign('isBestuur', LoginLid::mag('R_BESTUUR'));
		$this->smarty->assign('isLidMod', LoginLid::mag('P_LEDEN_MOD'));

		//eigen profiel niet cachen, dan krijgen we namelijk rare dingen
		//dat we andermans saldo's zien enzo
		if (LoginLid::instance()->isSelf($this->model->getUid())) {
			$this->caching = false;
		}

		$this->smarty->assign('profiel', new Profiel($this->model));

		$template = 'profiel/profiel.tpl';
		$this->smarty->display($template, $this->model->getUid());
	}

}

/**
 * Profiel bewerken formulierpagina
 */
class ProfielEditContent extends TemplateView {

	public function __construct($profiel, $actie) {
		parent::__construct($profiel, 'profiel van ' . $profiel->getLid()->getNaam() . ' bewerken.');
		$this->smarty->assign('profiel', $this->model);
		$this->smarty->assign('actie', $actie);
	}

	public function view() {
		$this->smarty->display('profiel/bewerken.tpl');
	}

}

/**
 * Lidstatus-wijzigingsformulierpagina
 */
class ProfielStatusContent extends TemplateView {

	public function __construct($profiel, $actie) {
		parent::__construct($profiel, 'lidstatus van ' . $profiel->getLid()->getNaam() . ' aanpassen.');
		$this->smarty->assign('profiel', $this->model);
		$this->smarty->assign('actie', $actie);
	}

	public function view() {
		$gelijknamigenovieten = Zoeker::zoekLeden($this->model->getLid()->getProperty('voornaam'), 'voornaam', 'alle', 'achternaam', array('S_NOVIET'), array('uid'));
		$gelijknamigeleden = Zoeker::zoekLeden($this->model->getLid()->getProperty('achternaam'), 'achternaam', 'alle', 'lidjaar', array('S_LID', 'S_GASTLID'), array('uid'));
		$this->smarty->assign('gelijknamigenovieten', $gelijknamigenovieten);
		$this->smarty->assign('gelijknamigeleden', $gelijknamigeleden);
		$this->smarty->display('profiel/wijzigstatus.tpl');
	}

}

/**
 * Commissievoorkeuren formulierpagina
 */
class ProfielVoorkeurContent extends TemplateView {

	public function __construct($profiel, $actie) {
		parent::__construct($profiel, 'voorkeur van ' . $profiel->getLid()->getNaam() . ' aanpassen.');
		$this->smarty->assign('profiel', $this->model);
		$this->smarty->assign('actie', $actie);
	}

	public function view() {
		$this->smarty->display('profiel/wijzigvoorkeur.tpl');
	}

}
