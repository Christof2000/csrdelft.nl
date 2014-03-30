<?php

require_once 'MVC/model/MenuModel.class.php';
require_once 'MVC/view/MenuBeheerView.class.php';

/**
 * MenuBeheerController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuBeheerController extends AclController {

	public function __construct($query) {
		parent::__construct($query, MenuModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_ADMIN'
			);
		} else {
			$this->acl = array(
				'toevoegen' => 'P_ADMIN',
				'bewerken' => 'P_ADMIN',
				'verwijderen' => 'P_ADMIN'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function beheer($menu_naam = '') {
		$menu_naam = str_replace('%20', ' ', $menu_naam); // FIXME
		$body = new MenuBeheerView($this->model->getMenuTree($menu_naam, true), $this->model->getAlleMenus());
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('menubeheer.css');
	}

	public function toevoegen($parent_id) {
		$item = $this->model->newMenuItem((int) $parent_id);
		$this->view = new MenuItemFormView($item, $this->action, (int) $parent_id); // fetches POST values itself
		if ($this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = (int) $id;
			setMelding('Toegevoegd', 1);
			$this->view = new MenuItemView($item);
		}
		// ReloadPage
	}

	public function bewerken($item_id) {
		$item = $this->model->getMenuItem($item_id);
		$this->view = new MenuItemFormView($item, $this->action, $item->item_id); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				setMelding('Bijgewerkt', 1);
			} else {
				setMelding('Geen wijzigingen', 0);
			}
			$this->view = new MenuItemView($item);
		}
		// ReloadPage
	}

	public function verwijderen($item_id) {
		$item = $this->getMenuItem($item_id);
		if ($this->model->removeMenuItem($item)) {
			setMelding('Verwijderd', 1);
			$this->view = new MenuItemView($item);
		}
		// ReloadPage
	}

}
