<?php



/**
 * VrijstellingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken vrijstelling.
 * 
 */
class VrijstellingFormView extends TemplateView {

	private $_form;
	private $_uid;

	public function __construct($uid = null, $begin = null, $eind = null, $percentage = null) {
		parent::__construct();
		$this->_uid = $uid;

		$formFields[] = new RequiredLidField('lid_id', $uid, 'Naam of lidnummer');
		$formFields[] = new DatumField('begin_datum', $begin, 'Vanaf', date('Y') + 1, date('Y'));
		$formFields[] = new DatumField('eind_datum', $eind, 'Tot en met', date('Y') + 1, date('Y'));
		$formFields[] = new IntField('percentage', $percentage, 'Percentage (%)', $GLOBALS['corvee']['vrijstelling_percentage_max'], $GLOBALS['corvee']['vrijstelling_percentage_min']);

		$this->_form = new Formulier('taken-vrijstelling-form', $GLOBALS['taken_module'] . '/opslaan' . ($uid === null ? '' : '/' . $uid), $formFields);
	}

	public function getTitel() {
		if ($this->_uid === null) {
			return 'Vrijstelling aanmaken';
		}
		return 'Vrijstelling wijzigen';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';
		$this->assign('form', $this->_form);
		if ($this->_uid === null) {
			$this->assign('nocheck', true);
		}
		$this->display('taken/popup_form.tpl');
	}

	public function validate() {
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>