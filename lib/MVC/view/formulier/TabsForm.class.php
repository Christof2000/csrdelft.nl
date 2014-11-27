<?php

require_once 'MVC/view/formulier/Formulier.class.php';

/**
 * TabsForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Formulier met tabbladen.
 * 
 */
class TabsForm extends Formulier {

	private $tabs = array();
	public $vertical = false;
	public $hoverintent = false;

	public function getTabs() {
		return $this->tabs;
	}

	public function hasTab($tab) {
		return isset($this->tabs[$tab]);
	}

	public function addTab($tab) {
		if ($this->hasTab($tab)) {
			return false;
		}
		$this->tabs[$tab] = array();
		return true;
	}

	public function addFields(array $fields, $tab = 'head') {
		$this->addTab($tab);
		$this->tabs[$tab] = array_merge($this->tabs[$tab], $fields);
		parent::addFields($fields);
	}

	/**
	 * Toont het formulier en javascript van alle fields.
	 */
	public function view() {
		echo getMelding();
		echo $this->getFormTag();
		if ($this->getTitel()) {
			echo '<h1 class="Titel">' . $this->getTitel() . '</h1>';
		}
		if ($this->vertical) {
			echo <<<HTML
<style>
	.ui-tabs-vertical { width: 55em; }
	.ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
	.ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
	.ui-tabs-vertical .ui-tabs-nav li a { display: block; width: 100%; }
	.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
	.ui-tabs-vertical .ui-tabs-panel { padding: 2em 1em !important; float: right; width: 40em; }
</style>
HTML;
		}
		// fields above tabs
		if (isset($this->tabs['head'])) {
			foreach ($this->tabs['head'] as $field) {
				$field->view();
			}
			unset($this->tabs['head']);
		}
		// fields below tabs
		if (isset($this->tabs['foot'])) {
			$foot = $this->tabs['foot'];
			unset($this->tabs['foot']);
		}
		// tabs
		if (sizeof($this->tabs) > 0) {
			echo '<br /><div id="' . $this->getFormId() . '-tabs" class="tabs-list"><ul>';
			foreach ($this->tabs as $tab => $fields) {
				echo '<li><a href="#' . $this->getFormId() . '-tab-' . $tab . '" class="tab-item">' . ucfirst($tab) . '</a></li>';
			}
			echo '</ul>';
			foreach ($this->tabs as $tab => $fields) {
				echo '<div id="' . $this->getFormId() . '-tab-' . $tab . '" class="tabs-content">';
				foreach ($fields as $field) {
					$field->view();
				}
				echo '</div>';
			}
			echo '</div><br />';
		}
		// fields below tabs
		if (isset($foot)) {
			foreach ($foot as $field) {
				$field->view();
			}
		}
		echo $this->getScriptTag();
		echo '</form>';
	}

	public function getJavascript() {
		$js = <<<JS

$('#{$this->getFormId()}-tabs').tabs();
JS;
		if ($this->vertical) {
			$js .= <<<JS

$('#{$this->getFormId()}-tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
$('#{$this->getFormId()}-tabs li').removeClass('ui-corner-top').addClass('ui-corner-left');
JS;
		}
		if ($this->hoverintent) {
			$js .= <<<JS
try {
	$('#{$this->getFormId()}-tabs .tab-item').hoverIntent(function() {
		$(this).trigger('click');
	});
} catch(e) {
	// missing js
}
JS;
		}
		return parent::getJavascript() . $js;
	}

}
