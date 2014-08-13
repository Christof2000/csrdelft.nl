<?php

/**
 * LoginController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de agenda.
 */
class LoginController extends AclController {

	public function __construct($query) {
		parent::__construct($query, LoginModel::instance());
		$this->acl = array(
			'login'	 => 'P_PUBLIC',
			'logout' => 'P_LOGGED_IN',
			'su'	 => 'P_ADMIN',
			'endSu'	 => 'P_LOGGED_IN',
			'pauper' => 'P_PUBLIC'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		parent::performAction($this->getParams(2));
	}

	public function login() {
		require_once 'MVC/view/LoginView.class.php';
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();
		$this->model->setPauper($values['mobiel']);
		if ($form->validate()) {
			if ($this->model->login($values['user'], $values['pass'], !$values['mobiel'])) {
				invokeRefresh($values['url']);
			}
		}
		invokeRefresh(CSR_ROOT); // login gefaald
	}

	public function logout() {
		$this->model->logout();
		invokeRefresh(CSR_ROOT);
	}

	public function su($uid) {
		$this->model->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'full', 'plain') . '!', 1);
		invokeRefresh(Instellingen::get('stek', 'referer'));
	}

	public function endSu() {
		if (!$this->model->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			LoginModel::instance()->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		invokeRefresh(Instellingen::get('stek', 'referer'));
	}

	public function pauper($terug) {
		if ($terug === 'terug') {
			$this->model->setPauper(false);
			invokeRefresh(CSR_ROOT);
		} else {
			$this->model->setPauper(true);
		}

		require_once 'MVC/model/CmsPaginaModel.class.php';
		require_once 'MVC/view/CmsPaginaView.class.php';

		$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
		$this->view = new CsrLayoutPage($body);
	}

}
