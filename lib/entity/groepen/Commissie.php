<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use CsrDelft\repository\groepen\leden\CommissieLedenRepository;
use Doctrine\ORM\Mapping as ORM;


/**
 * Commissie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een commissie is een groep waarvan de groepsleden een specifieke functie (kunnen) hebben.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\CommissiesRepository")
 * @ORM\Table("commissies")
 */
class Commissie extends AbstractGroep {
	/**
	 * @var CommissieLid[]
	 * @ORM\OneToMany(targetEntity="CommissieLid", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return CommissieLid::class;
	}

	const LEDEN = CommissieLedenRepository::class;

	/**
	 * (Bestuurs-)Commissie / SjaarCie
	 * @var CommissieSoort
	 * @ORM\Column(type="string")
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'soort' => [T::Enumeration, false, CommissieSoort::class],
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'commissies';

	public function getUrl() {
		return '/groepen/commissies/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods=null, $soort = null) {
		switch ($soort) {

			case CommissieSoort::SjaarCie:
				if (LoginModel::mag('commissie:NovCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
