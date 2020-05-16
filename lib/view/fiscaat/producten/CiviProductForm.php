<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\model\entity\fiscaat\CiviCategorie;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredBedragField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredJaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CiviProductForm extends ModalForm {
	function __construct(CiviProduct $model) {
		parent::__construct($model, '/fiscaat/producten/opslaan', false, true);

		$fields = [];
		$fields['id'] = new IntField('id', $model->id, 'id');
		$fields['id']->hidden = true;
		$fields[] = new RequiredIntField('status', $model->status, 'Status');
		$fields[] = new RequiredTextField('beschrijving', $model->beschrijving, 'Beschrijving');
		$fields[] = new RequiredIntField('prioriteit', $model->prioriteit, 'Prioriteit');
		$fields[] = new RequiredJaNeeField('beheer', $model->beheer, 'Beheer');
		$fields[] = new RequiredBedragField('prijs', $model->prijs, 'Prijs', '€', 0, 50, 0.50);
		$fields['cat'] = new DoctrineEntityField('categorie', $model->categorie, 'Categorie', CiviCategorie::class, '/fiscaat/categorien/suggesties?q=');
		$fields['cat']->required = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
