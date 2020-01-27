<?php


namespace CsrDelft\entity\courant;


use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Courant
 * @package CsrDelft\entity\courant
 * @ORM\Entity(repositoryClass="CsrDelft\repository\CourantRepository")
 * @ORM\Table("courant")
 */
class Courant {
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $verzendMoment;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $inhoud;
	/**
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 */
	public $verzender;

	public function getJaar() {
		return date('Y', strtotime($this->verzendMoment));
	}
}
