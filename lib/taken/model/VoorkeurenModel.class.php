<?php

require_once 'taken/model/entity/CorveeVoorkeur.class.php';
require_once 'taken/model/CorveeRepetitiesModel.class.php';

/**
 * VoorkeurenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class VoorkeurenModel {

	public static function getEetwens(\Lid $lid) {
		return $lid->getProperty('eetwens');
	}

	public static function setEetwens(\Lid $lid, $eetwens) {
		$lid->setProperty('eetwens', $eetwens);
		if (!$lid->save()) {
			throw new Exception('Eetwens opslaan mislukt');
		}
	}

	/**
	 * Geeft de ingeschakelde voorkeuren voor een lid terug en ook
	 * de voorkeuren die het lid nog kan inschakelen.
	 * Dat laatste kan alleen voor het ingelogde lid.
	 * Voor elk ander lid worden de permissies niet gefilterd.
	 * 
	 * @param string $uid
	 * @param boolean $alleenIngeschakeld 
	 * @return CorveeVoorkeur[]
	 */
	public static function getVoorkeurenVoorLid($uid, $alleenIngeschakeld = false) {
		$repById = CorveeRepetitiesModel::getVoorkeurbareRepetities(true); // grouped by crid
		$result = array();
		$voorkeuren = self::loadVoorkeuren(null, $uid);
		foreach ($voorkeuren as $voorkeur) {
			$crid = $voorkeur->getCorveeRepetitieId();
			if (array_key_exists($crid, $repById)) { // ingeschakeld en voorkeurbaar
				$voorkeur->setCorveeRepetitie($repById[$crid]);
				$voorkeur->setVanLid($uid);
				$result[$crid] = $voorkeur;
			}
		}
		foreach ($repById as $crid => $repetitie) {
			if (!$alleenIngeschakeld && !array_key_exists($crid, $result)) { // uitgeschakeld en voorkeurbaar
				if ($repetitie->getCorveeFunctie()->kwalificatie_benodigd) {
					require_once 'MVC/model/taken/KwalificatiesModel.class.php';
					if (!KwalificatiesModel::instance()->isLidGekwalificeerdVoorFunctie($uid, $repetitie->getFunctieId())) {
						continue;
					}
				}
				$voorkeur = new CorveeVoorkeur($crid, null);
				$voorkeur->setCorveeRepetitie($repetitie);
				$voorkeur->setVanLid($uid);
				$result[$crid] = $voorkeur;
			}
		}
		ksort($result);
		return $result;
	}

	public static function getHeeftVoorkeur($crid, $uid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Get heeft voorkeur faalt: Invalid $crid =' . $crid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_voorkeuren WHERE crv_repetitie_id=? AND lid_id=?)';
		$values = array($crid, $uid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	/**
	 * Bouwt matrix voor alle repetities en voorkeuren van alle leden
	 * 
	 * @return CorveeVoorkeur[uid][crid]
	 */
	public static function getVoorkeurenMatrix() {
		$matrix = array();
		$repById = CorveeRepetitiesModel::getVoorkeurbareRepetities(true); // grouped by crid
		$leden_voorkeuren = self::loadLedenVoorkeuren();
		foreach ($leden_voorkeuren as $lv) {
			$crid = $lv['crid'];
			$uid = $lv['uid'];
			if ($lv['voorkeur']) {
				$voorkeur = new CorveeVoorkeur($crid, $uid);
			} else {
				$voorkeur = new CorveeVoorkeur($crid, null);
			}
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			$voorkeur->setVanLid($uid);
			$matrix[$uid][$crid] = $voorkeur;
			ksort($matrix[$uid]);
		}
		return array($matrix, $repById);
	}

	private static function loadLedenVoorkeuren() {
		$sql = 'SELECT uid, crv_repetitie_id AS crid,';
		$sql.= ' (EXISTS ( SELECT * FROM crv_voorkeuren WHERE crv_repetitie_id = crid AND lid_id = uid )) AS voorkeur';
		$sql.= ' FROM lid, crv_repetities';
		$sql.= ' WHERE voorkeurbaar = true AND lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")'; // alleen leden
		$sql.= ' ORDER BY achternaam, voornaam ASC';
		$db = \Database::instance();
		$values = array();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll();
		return $result;
	}

	public static function getVoorkeurenVoorRepetitie($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Get voorkeuren voor repetitie faalt: Invalid $crid =' . $crid);
		}
		return self::loadVoorkeuren($crid);
	}

	private static function loadVoorkeuren($crid = null, $uid = null) {
		if (is_int($crid) && $uid !== null) {
			throw new Exception('Load voorkeuren faalt: both $crid AND $uid provided');
		}
		$sql = 'SELECT crv_repetitie_id, lid_id';
		$sql.= ' FROM crv_voorkeuren';
		$values = array();
		if (is_int($crid)) {
			$sql.= ' WHERE crv_repetitie_id=?';
			$values[] = $crid;
		}
		if ($uid !== null) {
			$sql.= ' WHERE lid_id=?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\CorveeVoorkeur');
		return $result;
	}

	public static function inschakelenVoorkeur($crid, $uid) {
		if (self::getHeeftVoorkeur($crid, $uid)) {
			throw new Exception('Voorkeur al ingeschakeld');
		}
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		if (!$repetitie->getIsVoorkeurbaar()) {
			throw new Exception('Niet voorkeurbaar');
		}
		return self::newVoorkeur($crid, $uid);
	}

	/**
	 * Slaat voorkeur op voor de opgegeven corvee-repetitie voor een specifiek lid.
	 * 
	 * @param int $crid
	 * @param String $uid
	 * @return CorveeVoorkeur
	 */
	private static function newVoorkeur($crid, $uid) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT IGNORE INTO crv_voorkeuren';
			$sql.= ' (crv_repetitie_id, lid_id)';
			$values = array($crid, $uid);
			$sql.= ' VALUES (?, ?)';
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new Exception('New corvee-voorkeur faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
			return new CorveeVoorkeur($crid, $uid);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function uitschakelenVoorkeur($crid, $uid) {
		if (!self::getHeeftVoorkeur($crid, $uid)) {
			throw new Exception('Voorkeur al uitgeschakeld');
		}
		self::deleteVoorkeuren($crid, $uid);
		return new CorveeVoorkeur($crid, null);
	}

	/**
	 * Called when a CorveeRepetitie is being deleted.
	 * This is only possible after all CorveeVoorkeuren are deleted of this CorveeRepetitie (db foreign key)
	 * 
	 * @return int amount of deleted voorkeuren
	 */
	public static function verwijderVoorkeuren($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Verwijder voorkeuren faalt: Invalid $crid =' . $crid);
		}
		return self::deleteVoorkeuren($crid);
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * 
	 * @return int amount of deleted voorkeuren
	 */
	public static function verwijderVoorkeurenVoorLid($uid) {
		$voorkeuren = self::getVoorkeurenVoorLid($uid);
		$aantal = 0;
		foreach ($voorkeuren as $voorkeur) {
			$aantal += self::deleteVoorkeuren($voorkeur->getCorveeRepetitieId(), $uid);
		}
		if (sizeof($voorkeuren) !== $aantal) {
			throw new Exception('Niet alle voorkeuren zijn uitgeschakeld!');
		}
		return $aantal;
	}

	private static function deleteVoorkeuren($crid, $uid = null) {
		$sql = 'DELETE FROM crv_voorkeuren';
		$sql.= ' WHERE crv_repetitie_id=?';
		$values = array($crid);
		if ($uid !== null) {
			$sql.= ' AND lid_id=?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($uid !== null && $query->rowCount() !== 1) {
			throw new Exception('Delete voorkeuren faalt: $query->rowCount() =' . $query->rowCount());
		}
		return $query->rowCount();
	}

}

?>