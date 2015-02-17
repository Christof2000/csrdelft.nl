<?php

/**
 * Gesprek.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Gesprek extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime last message
	 * @var string
	 */
	public $laatste_update;
	/**
	 * Aantal nieuwe berichten sinds laatst gelezen
	 * @var int
	 */
	public $aantal_nieuw;
	/**
	 * Aantal seconden delay
	 * @var int
	 */
	public $auto_update;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'gesprek_id'	 => array(T::Integer, false, 'auto_increment'),
		'laatste_update' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('gesprek_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprekken';

	public function getDeelnemers() {
		return GesprekDeelnemersModel::instance()->getDeelnemersVanGesprek($this);
	}

	public function getDeelnemersFormatted() {
		$deelnemers = '';
		foreach ($this->getDeelnemers() as $deelnemer) {
			if ($deelnemer->uid === LoginModel::getUid()) {
				continue;
			}
			if (!empty($deelnemers)) {
				$deelnemers .= ', ';
			}
			$deelnemers .= ProfielModel::getNaam($deelnemer->uid, 'civitas');
		}
		return $deelnemers;
	}

	public function getBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!is_int($lastUpdate)) {
			throw new Exception('lastUpdate invalid');
		}
		$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
		if ($lastUpdate < $toegevoegd) {
			$lastUpdate = $toegevoegd;
		}
		// Auto update
		if (0 === GesprekDeelnemersModel::instance()->getAantalAndereDeelnemers($deelnemer)) {
			$this->auto_update = false;
		} else {
			$max = 1000 * (int) Instellingen::get('gesprekken', 'max_delay_seconds');

			$berichten = GesprekBerichtenModel::instance()->find('gesprek_id = ? AND auteur_uid != ?', array($deelnemer->gesprek_id, $deelnemer->uid), null, 'bericht_id DESC', 2);
			$laatste = $berichten->fetch();
			$eennalaatste = $berichten->fetch();

			if ($laatste) {
				if ($eennalaatste) {
					$diff = strtotime($laatste->moment) - strtotime($eennalaatste->moment);
				} else {
					$diff = time() - strtotime($laatste->moment);
				}
				$this->auto_update = 1000 * $diff;

				$min = 1000 * (int) Instellingen::get('gesprekken', 'min_delay_seconds');
				if ($this->auto_update < $min) {
					$this->auto_update = $min;
				}
				if ($this->auto_update > $max) {
					$this->auto_update = $max;
				}
			} else {
				$this->auto_update = $max;
			}
		}
		// Update deelnemer
		$deelnemer->gelezen_moment = getDateTime();
		GesprekDeelnemersModel::instance()->update($deelnemer);
		return GesprekBerichtenModel::instance()->getBerichtenSinds($this, $lastUpdate);
	}

	public function getAantalNieuweBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!isset($this->aantal_nieuw)) {
			$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
			if ($lastUpdate < $toegevoegd) {
				$lastUpdate = $toegevoegd;
			}
			$this->aantal_nieuw = GesprekBerichtenModel::instance()->getAantalBerichtenSinds($this, $lastUpdate);
		}
		return $this->aantal_nieuw;
	}

}
