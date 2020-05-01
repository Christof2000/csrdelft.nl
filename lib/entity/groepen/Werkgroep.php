<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\leden\WerkgroepDeelnemersRepository;
use CsrDelft\model\security\LoginModel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;


/**
 * Werkgroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\WerkgroepenRepository")
 * @ORM\Table("werkgroepen")
 */
class Werkgroep extends AbstractGroep {
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $aanmelden_tot;
	/**
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $bewerken_tot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $afmelden_tot;

	/**
	 * @var WerkgroepDeelnemer[]
	 * @ORM\OneToMany(targetEntity="WerkgroepDeelnemer", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return WerkgroepDeelnemer::class;
	}

	const LEDEN = WerkgroepDeelnemersRepository::class;

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'werkgroepen';

	public function getUrl() {
		return '/groepen/werkgroepen/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		if ($action === AccessAction::Aanmaken AND !LoginModel::mag(P_LEDEN_MOD)) {
			return false;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods);
	}

}
