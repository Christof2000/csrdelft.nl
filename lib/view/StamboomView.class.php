<?php

/**
 * StamboomView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * Geeft een stamboom weer vanaf een startuid. Met de patroonlinkjes kan
 * je doorklikken naar boven. Verder niet zo'n spannend ding, zou een
 * stuk mooier gelayout kunnen worden...
 */
class StamboomView implements View {

	/**
	 * Root-lid
	 * @var Profiel
	 */
	private $root;
	private $kinderen = 0;

	public function __construct($uid, $levels = 3) {
		if (!AccountModel::isValidUid($uid)) {
			$uid = LoginModel::getUid();
		}
		$this->root = ProfielModel::get($uid);
	}

	public function getModel() {
		return $this->root;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><img src="/plaetjes/knopjes/people-16.png" class="module-icon"></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Stamboom voor het geslacht van ' . $this->root->getNaam();
	}

	private function viewNode(Profiel $profiel, $viewPatroon = false) {
		echo '<div class="node">';
		echo '<div class="lid">';
		echo $profiel->getLink('pasfoto');
		echo $profiel->getLink('civitas');
		$aantalKinderen = count($profiel->getKinderen());
		if ($aantalKinderen == 1) {
			echo '<span class="small"> (1 kind)</span>';
		} elseif ($aantalKinderen > 1) {
			echo '<span class="small"> (' . $aantalKinderen . ' kinderen)</span>';
		}

		if ($viewPatroon) {
			$patroon = ProfielModel::get($profiel->patroon);
			if ($patroon) {
				echo '<br /><a href="/leden/stamboom/' . $patroon->uid . '" title="Stamboom van ' . htmlspecialchars($patroon->getNaam()) . '"> &uArr; ' . $patroon->getNaam('civitas') . '</a>';
			}
		}
		echo '</div>';

		if ($profiel->hasKinderen()) {
			echo '<div class="kinderen">';
			foreach ($profiel->getKinderen() as $kind) {
				$this->kinderen++;
				$this->viewNode($kind);
			}
			echo '<div class="clear"></div>';
			echo '</div>';
		}

		echo '</div>';
	}

	public function view() {
		$this->viewNode($this->root, true); // set count kinderen
		echo '<h3>Omvang van het nageslacht van ' . $this->root->getNaam() . ': ' . $this->kinderen . '</h3>';
	}

}
