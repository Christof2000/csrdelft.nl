<?php

require_once 'MVC/model/entity/groepen/Groep.abstract.php';

/**
 * GroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GroepenModel extends PersistenceModel {

	protected function __construct() {
		parent::__construct('groepen/');
	}

	public function getById($id) {
		if (!is_int($id) OR $id <= 0) {
			throw new Exception('Invalid groep id');
		}
		$groep = $this->retrieveByPrimaryKeys(array($id));
		if (!$groep instanceof Groep) {
			throw new Exception('Groep bestaat niet');
		}
		return $groep;
	}

}

class GroepLedenModel extends GroepenModel {

	const orm = 'GroepLid';

	protected static $instance;

	public function getLedenVoorGroep(Groep $groep) {
		return $this->find('groep_type = ? AND groep_id = ?', array(get_class($groep), $groep->id), 'lid_sinds ASC')->fetchAll();
	}

	public function getStatistieken(Groep $groep) {
		$uids = array_keys(group_by_distinct('lid_id', $groep->getGroepLeden(), false));
		$count = count($uids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		$stats['Totaal'] = $count;
		$stats['Verticale'] = Database::instance()->sqlSelect(array('verticale.naam', 'count(*)'), 'lid LEFT JOIN verticale ON(lid.verticale = verticale.id)', 'uid IN (' . $in . ')', $uids, null, 'verticale.naam')->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), 'lid', 'uid IN (' . $in . ')', $uids, null, 'geslacht')->fetchAll();
		$stats['Lidjaar'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), 'lid', 'uid IN (' . $in . ')', $uids, null, 'lidjaar')->fetchAll();
		$stats['Opmerking'] = Database::instance()->sqlSelect(array('opmerking', 'count(*)'), GroepLid::getTableName(), 'groep_type = ? AND groep_id = ?', array(get_class($groep), $groep->id), null, 'opmerking')->fetchAll();
		return $stats;
	}

}

class GroepCategorienModel extends GroepenModel {

	const orm = 'GroepCategorie';

	protected static $instance;

}

class CommissiesModel extends GroepenModel {

	const orm = 'Commissie';

	protected static $instance;

}

class BesturenModel extends GroepenModel {

	const orm = 'Bestuur';

	protected static $instance;

}

class SjaarciesModel extends GroepenModel {

	const orm = 'Sjaarcie';

	protected static $instance;

}

class OnderverenigingenModel extends GroepenModel {

	const orm = 'Ondervereniging';

	protected static $instance;

}

class WerkgroepenModel extends GroepenModel {

	const orm = 'Werkgroep';

	protected static $instance;

}

class WoonoordenModel extends GroepenModel {

	const orm = 'Woonoord';

	protected static $instance;

}

class ActiviteitenModel extends GroepenModel {

	const orm = 'Activiteit';

	protected static $instance;

}

class ConferentiesModel extends GroepenModel {

	const orm = 'Conferentie';

	protected static $instance;

}

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

	protected static $instance;

}

class KetzerSelectorsModel extends GroepenModel {

	const orm = 'KetzerSelector';

	protected static $instance;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->find('ketzer_id = ?', array($ketzer->id));
	}

}

class KetzerOptiesModel extends GroepenModel {

	const orm = 'KetzerOptie';

	protected static $instance;

	public function getOptiesVoorSelect(KetzerSelector $select) {
		return $this->find('ketzer_id = ? AND select_id = ?', array($select->ketzer_id, $select->select_id));
	}

}

class KetzerKeuzesModel extends GroepenModel {

	const orm = 'KetzerKeuze';

	protected static $instance;

	public function getKeuzesVoorOptie(KetzerOptie $optie) {
		return $this->find('ketzer_id = ? AND select_id = ? AND optie_id = ?', array($optie->ketzer_id, $optie->select_id, $optie->optie_id));
	}

	public function getKeuzeVanLid(KetzerSelector $select, $lid_id) {
		return $this->find('ketzer_id = ? AND select_id = ? AND lid_id = ?', array($select->ketzer_id, $select->select_id, $lid_id));
	}

	public function getKetzerKeuzesVanLid(Ketzer $ketzer, $lid_id) {
		return $this->find('ketzer_id = ? AND lid_id = ?', array($ketzer->id, $lid_id));
	}

}
