<?php

require_once 'model/maalcie/CorveeVoorkeurenModel.class.php';
require_once 'view/maalcie/MijnVoorkeurenView.class.php';
require_once 'view/maalcie/forms/EetwensForm.class.php';

/**
 * MijnVoorkeurenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MijnVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'mijn' => 'P_CORVEE_IK'
			);
		} else {
			$this->acl = array(
				'inschakelen'	 => 'P_CORVEE_IK',
				'uitschakelen'	 => 'P_CORVEE_IK',
				'eetwens'		 => 'P_CORVEE_IK'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = intval($this->getParam(3));
		}
		parent::performAction(array($crid));
	}

	public function mijn() {
		$voorkeuren = CorveeVoorkeurenModel::instance()->getVoorkeurenVoorLid(LoginModel::getUid(), true);
		$this->view = new MijnVoorkeurenView($voorkeuren);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function inschakelen($crid) {
		$voorkeur = CorveeVoorkeurenModel::instance()->inschakelenVoorkeur($crid, LoginModel::getUid());
		$this->view = new MijnVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid) {
		$voorkeur = CorveeVoorkeurenModel::instance()->uitschakelenVoorkeur($crid, LoginModel::getUid());
		$this->view = new MijnVoorkeurView($voorkeur);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			CorveeVoorkeurenModel::instance()->setEetwens(LoginModel::getProfiel(), $form->getField()->getValue());
		}
		$this->view = $form;
	}

}
