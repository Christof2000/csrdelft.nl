<?php

require_once 'MVC/model/taken/KwalificatiesModel.class.php';

/**
 * FunctiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FunctiesModel extends PersistenceModel {

	const orm = 'CorveeFunctie';

	protected static $instance;

	/**
	 * Optional eager loading of kwalificaties.
	 * 
	 * @param boolean $load_kwalificaties
	 * @return CorveeFunctie[]
	 */
	public function getAlleFuncties($load_kwalificaties = false) {
		$functies = $this->find();
		if ($load_kwalificaties) {
			$kwalificaties = KwalificatiesModel::instance()->getAlleKwalificaties();
		}
		$result = array();
		foreach ($functies as $functie) {
			if ($load_kwalificaties) {
				if (array_key_exists($functie->functie_id, $kwalificaties)) {
					$functie->setKwalificaties($kwalificaties[$functie->functie_id]);
					unset($kwalificaties[$functie->functie_id]);
				} else {
					$functie->setKwalificaties(array());
				}
			}
			$result[$functie->functie_id] = $functie;
		}
		return $result;
	}

	/**
	 * Lazy loading of kwalificaties.
	 * 
	 * @param int $fid
	 * @return CorveeFunctie[]
	 */
	public function getFunctie($fid) {
		return $this->retrieveByPrimaryKeys(array($fid));
	}

	public function newFunctie() {
		$functie = new CorveeFunctie();
		$functie->kwalificatie_benodigd = (bool) Instellingen::get('corvee', 'standaard_kwalificatie');
		return $functie;
	}

	public function removeFunctie(CorveeFunctie $functie) {
		if (TakenModel::existFunctieTaken($functie->functie_id)) {
			throw new Exception('Verwijder eerst de bijbehorende corveetaken!');
		}
		if (CorveeRepetitiesModel::existFunctieRepetities($functie->functie_id)) {
			throw new Exception('Verwijder eerst de bijbehorende corveerepetities!');
		}
		if ($functie->hasKwalificaties()) {
			throw new Exception('Verwijder eerst de bijbehorende kwalificaties!');
		}
		return $this->delete($functie);
	}

}
