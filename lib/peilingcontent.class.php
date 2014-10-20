<?php

require_once 'peiling.class.php';

/**
 * PeilingContent.class.php
 * 
 * Roept template aan.
 */
class PeilingContent extends SmartyTemplateView {

	function PeilingContent(Peiling $peiling) {
		parent::__construct($peiling);
	}

	public function getHtml($beheer = false) {
		$this->smarty->assign('peiling', $this->model);
		$this->smarty->assign('beheer', $beheer);
		return $this->smarty->fetch('peiling.bb.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}
