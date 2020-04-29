<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\security\AccessModel;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class RechtenField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$this->suggestions[] = ContainerFacade::getContainer()->get(AccessModel::class)->getPermissionSuggestions();

		// TODO: bundelen om simultane verbindingen te sparen
		foreach (array('verticalen', 'lichtingen', 'commissies', 'groepen', 'onderverenigingen', 'woonoorden') as $option) {
			$this->suggestions[ucfirst($option)] = '/groepen/' . $option . '/zoeken/?q=';
		}

		$this->title = 'Met , en + voor respectievelijk OR en AND. Gebruik | voor OR binnen AND (alsof er haakjes omheen staan)';
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$errors = ContainerFacade::getContainer()->get(AccessModel::class)->getPermissionStringErrors($this->value);
		if (!empty($errors)) {
			$this->error = 'Ongeldig: "' . implode('" & "', $errors) . '"';
		}
		return $this->error === '';
	}

}
