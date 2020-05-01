<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * VerticaleLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een verticale.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\VerticaleLedenRepository")
 * @ORM\Table("verticale_leden")
 */
class VerticaleLid extends AbstractGroepLid {

	protected static $table_name = 'verticale_leden';

	/**
	 * @var Verticale
	 * @ORM\ManyToOne(targetEntity="Verticale", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
