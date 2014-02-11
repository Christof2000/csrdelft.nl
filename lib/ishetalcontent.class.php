<?php

class IsHetAlContent extends TemplateView {

	private $opties = array('dies', 'jarig', 'vrijdag', 'donderdag', 'zondag', 'borrel', 'lezing', 'lunch', 'avond');
	private $ja = false; //ja of nee.

	public function __construct($ishetal) {
		parent::__construct($ishetal);
		if ($this->model == 'willekeurig') {
			$this->model = $this->opties[array_rand($this->opties)];
		}
		switch ($this->model) {
			case 'dies' :
				$begin = strtotime('2014-02-11');
				$einde = strtotime('2014-02-21');
				$nu = strtotime(date('Y-m-d'));
				if ($nu > $einde) {
					$begin = strtotime('+1 year', $begin);
				}
				$dagen = round(($begin - $nu) / 86400);
				if ($dagen <= 0) {
					$this->ja = true;
				} else {
					$this->ja = $dagen;
				}
				break;
			case 'jarig': $this->ja = LoginLid::instance()->getLid()->getJarigOver();
				break;
			case 'lunch': $this->ja = (date('Hi') > '1245' AND date('Hi') < '1345');
				break;
			case 'avond': $this->ja = (date('Hi') > '1700');
				break;
			case 'vrijdag': $this->ja = (date('w') == 5);
				break;
			case 'donderdag': $this->ja = (date('w') == 4);
				break;
			case 'zondag': $this->ja = (date('w') == 0);
				break;
			case 'studeren':
				if (isset($_COOKIE['studeren'])) {
					$this->ja = (time() > ($_COOKIE['studeren'] + 5 * 60) AND date('w') != 0);
					$tijd = $_COOKIE['studeren'];
				} else {
					$tijd = time();
				}
				setcookie('studeren', $tijd, time() + 30 * 60);
				break;
			default:
				require_once 'MVC/model/AgendaModel.class.php';
				$agenda = new AgendaModel();
				$vandaag = $agenda->zoekWoordAgenda($this->model);
				if ($vandaag instanceof AgendaItem) {
					if ($this->model == 'borrel') {
						$this->ja = time() > $vandaag->getBeginMoment();
					} else {
						$this->ja = time() > $vandaag->getBeginMoment() AND time() < $vandaag->getEindMoment();
					}
				}
				break;
		}
	}

	public function view() {
		switch ($this->model) {
			case 'jarig':
				echo '<div id="ishetal">Ben ik al jarig?<br />';
				break;
			case 'studeren':
				echo '<div id="ishetal">Moet ik alweer studeren?<br />';
				break;
			case 'borrel':
			case 'lezing':
				echo '<div id="ishetal">Is er een ' . $this->model . '?<br />';
				break;
			default:
				echo '<div id="ishetal">Is het al ' . $this->model . '?<br />';
				break;
		}

		if ($this->ja === true) {
			echo '<div class="ja">JA!</div>';
		} else {
			if ($this->model == 'jarig' || $this->model == 'dies') {
				echo '<div class="nee">OVER ' . $this->ja . ' DAGEN!</div>';
			} else {
				echo '<div class="nee">NEE.</div>';
			}
		}
		echo '</div><br />';
	}

}
