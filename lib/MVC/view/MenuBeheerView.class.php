<?php

/**
 * MenuBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class MenuBeheerView extends TemplateView {

	public function __construct(MenuItem $tree_root, array $menus) {
		parent::__construct($tree_root);
		if ($tree_root->tekst === '') {
			$this->model = false;
		}
		$this->smarty->assign('root', $this->model);
		$this->smarty->assign('menus', $menus);
	}

	public function getTitel() {
		if ($this->model) {
			return 'Beheer ' . $this->model->tekst . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		$this->smarty->display('MVC/menu/beheer/menu_tree.tpl');
	}

}

class MenuItemView extends TemplateView {

	public function __construct(MenuItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('MVC/menu/beheer/menu_item.tpl');
	}

}

class MenuItemFormView extends PopupForm {

	public function __construct(MenuItem $item, $actie) {
		parent::__construct($item, 'menu-item-form', '/menubeheer/' . $actie . '/' . $item->item_id);
		$this->titel = 'Menu-item ' . $actie;
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}
		$this->css_classes[] = 'ReloadPage';

		$fields['pid'] = new RequiredIntField('parent_id', $item->parent_id, 'Parent ID', 0);
		$fields['pid']->title = 'Item id van element 1 niveau hoger';

		$fields['prio'] = new IntField('prioriteit', $item->prioriteit, 'Volgorde');
		$fields['prio']->title = 'Sortering van items';

		$fields[] = new TextField('tekst', $item->tekst, 'Korte aanduiding', MenuItem::getMaxLength('tekst'));

		$fields['url'] = new TextField('link', $item->link, 'Link');
		$fields['url']->title = 'URL als er op het menu item geklikt wordt';

		$fields['r'] = new TextField('rechten_bekijken', $item->rechten_bekijken, 'Rechten');
		$fields['r']->title = 'Wie mag dit menu item zien';

		$fields['z'] = new SelectField('zichtbaar', ($item->zichtbaar ? '1' : '0'), 'Tonen', array('1' => 'Zichtbaar', '0' => 'Verborgen'));
		$fields['z']->title = 'Wel of niet tonen';

		$fields[] = new SubmitResetCancel();
		$this->addFields($fields);
	}

}
