<?php

namespace CsrDelft\entity\commissievoorkeuren;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurOpmerking
 * @package CsrDelft\entity\commissievoorkeuren
 * @ORM\Entity(repositoryClass="CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository")
 * @ORM\Table("voorkeurOpmerking")
 */
class VoorkeurOpmerking {
	/**
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true, name="lidOpmerking")
	 */
	public $lidOpmerking;

	/**
	 * @var string
	 * @ORM\Column(type="text", nullable=true, name="praesesOpmerking")
	 */
	public $praesesOpmerking;

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
}
