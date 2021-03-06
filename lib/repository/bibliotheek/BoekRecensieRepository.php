<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoekRecensie|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoekRecensie|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoekRecensie[]    findAll()
 * @method BoekRecensie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoekRecensieRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, BoekRecensie::class);
	}

	public function get(int $boek_id, string $uid): BoekRecensie {
		$recensie = $this->findOneBy(["boek_id" => $boek_id, "schrijver_uid" => $uid]);

		if (!$recensie) {
			$recensie = new BoekRecensie();
			$recensie->boek_id = $boek_id;
			$recensie->schrijver_uid = $uid;
			$recensie->schrijver = ProfielRepository::get($uid);
			$recensie->toegevoegd = getDateTime();
		}

		return $recensie;
	}

	/**
	 * @param $uid
	 * @return BoekRecensie[]
	 */
	public function getVoorLid($uid) {
		return $this->findBy(["schrijver_uid" => $uid]);
	}
}
