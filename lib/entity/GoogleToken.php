<?php

namespace CsrDelft\entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GoogleToken.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @ORM\Entity(repositoryClass="CsrDelft\repository\GoogleTokenRepository")
 * @ORM\Table("GoogleToken")
 */
class GoogleToken {
	/**
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $token;
}
