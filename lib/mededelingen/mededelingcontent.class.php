<?php

class MededelingContent extends TemplateView {

	public function __construct(Mededeling $mededeling, $prullenbak = false) {
		parent::__construct($mededeling, 'Mededelingen');
		$this->smarty->assign('mededeling', $this->model);
		$this->smarty->assign('prullenbak', $prullenbak);
		$this->smarty->assign('mededelingen_path', MEDEDELINGEN_ROOT);
		$this->smarty->assign('prioriteiten', Mededeling::getPrioriteiten());
		$this->smarty->assign('datumtijdFormaat', '%Y-%m-%d %H:%M');
		$this->smarty->assign('aantalTopMostBlock', MededelingenContent::aantalTopMostBlock);
		// Een standaard vervaltijd verzinnen indien nodig.
		if ($this->model->getVervaltijd() === null) {
			$standaardVervaltijd = new DateTime(getDateTime());
			$standaardVervaltijd = $standaardVervaltijd->format('Y-m-d 23:59');
			$this->smarty->assign('standaardVervaltijd', $standaardVervaltijd);
		}
	}

	public function view() {
		$this->smarty->display('mededelingen/mededeling.tpl');
	}

}
