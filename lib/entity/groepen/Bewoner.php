<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bewoner.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een bewoner van een woonoord / huis.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\BewonersRepository")
 * @ORM\Table("bewoners")
 */
class Bewoner extends AbstractGroepLid {

	protected static $table_name = 'bewoners';

	/**
	 * @var Woonoord
	 * @ORM\ManyToOne(targetEntity="Woonoord", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
