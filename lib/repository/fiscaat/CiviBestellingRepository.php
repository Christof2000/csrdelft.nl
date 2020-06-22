<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviBestelling|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviBestelling|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviBestelling[]    findAll()
 * @method CiviBestelling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviBestellingRepository extends AbstractRepository {
	/**
	 * @var CiviBestellingInhoudRepository
	 */
	private $civiBestellingInhoudRepository;
	/**
	 * @var CiviProductRepository
	 */
	private $civiProductRepository;
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;

	/**
	 * @param ManagerRegistry $registry
	 * @param CiviBestellingInhoudRepository $civiBestellingInhoudRepository
	 * @param CiviProductRepository $civiProductRepository
	 * @param CiviSaldoRepository $civiSaldoRepository
	 */
	public function __construct(
		ManagerRegistry $registry,
		CiviBestellingInhoudRepository $civiBestellingInhoudRepository,
		CiviProductRepository $civiProductRepository,
		CiviSaldoRepository $civiSaldoRepository
	) {
		parent::__construct($registry, CiviBestelling::class);

		$this->civiBestellingInhoudRepository = $civiBestellingInhoudRepository;
		$this->civiProductRepository = $civiProductRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
	}

	/**
	 * @param int $id
	 * @return CiviBestelling
	 */
	public function get($id) {
		return $this->find($id);
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @return CiviBestelling[]
	 */
	public function getPinBestellingInMoment($from, $to) {
		/** @var CiviBestelling[] $bestellingen */
		$bestellingen = $this->createQueryBuilder('b')
			->where('b.moment > :van and b.moment < :tot and b.deleted = false')
			->setParameter('van', $from)
			->setParameter('tot', $to)
			->orderBy('b.moment', 'ASC')
			->getQuery()->getResult();
		$pinBestellingen = [];

		foreach ($bestellingen as $bestelling) {
			foreach ($bestelling->inhoud as $item) {
				if ($item->product_id == CiviProductTypeEnum::PINTRANSACTIE) {
					$pinBestellingen[] = $bestelling;
				}
			}
		}

		return $pinBestellingen;
	}

	/**
	 * @param CiviBestelling $bestelling
	 */
	public function revert(CiviBestelling $bestelling) {
		return $this->_em->transactional(function () use ($bestelling) {
			if ($bestelling->deleted) {
				throw new Exception("Bestelling kan niet worden teruggedraaid.");
			}
			$this->civiSaldoRepository->ophogen($bestelling->uid, $bestelling->totaal);
			$bestelling->deleted = true;
			// TODO LOG?
			$this->_em->persist($bestelling);
			$this->_em->flush();
		});
	}

	/**
	 * @param string $uid
	 * @param int $limit
	 *
	 * @return CiviBestelling[]
	 */
	public function getBestellingenVoorLid($uid, $limit = null) {
		return $this->findBy(['uid' => $uid, 'deleted' => false], ['moment' => 'DESC'], $limit);
	}

	/**
	 * @param DateTime $date
	 * @param bool $profielOnly
	 *
	 * @return integer
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function getSomBestellingenVanaf(DateTime $date, $profielOnly = false) {
		$qb = $this->createQueryBuilder('cb')
			->select('SUM(cb.totaal)')
			->where('cb.deleted = false and cb.moment > :moment')
			->setParameter('moment', $date);

		if ($profielOnly) {
			$qb->andWhere('cb.uid NOT LIKE \'c%\'');
		}

		return (int)$qb->getQuery()->getSingleScalarResult();
	}

	public function vanBedragInCenten($bedrag, $uid) {
		$bestelling = new CiviBestelling();
		$bestelling->cie = 'anders';
		$bestelling->uid = $uid;
		$bestelling->profiel = ProfielRepository::get($uid);
		$bestelling->deleted = false;
		$bestelling->moment = date_create_immutable();

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = -$bedrag;
		$inhoud->product_id = CiviProductTypeEnum::OVERGEMAAKT;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = $this->civiProductRepository->getProduct($inhoud->product_id)->tmpPrijs * -$bedrag;

		return $bestelling;
	}

	/**
	 * @param CiviBestelling $entity
	 * @return string
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function create(CiviBestelling $entity) {
		// Persist bestelling eerst zonder inhoud
		$inhoud = $entity->inhoud;
		$entity->inhoud = [];
		$this->_em->persist($entity);
		$this->_em->flush();

		// Voeg inhoud toe
		$entity->inhoud = $inhoud;
		foreach ($entity->inhoud as $bestellingInhoud) {
			$bestellingInhoud->setBestelling($entity);
			$this->_em->persist($bestellingInhoud);
		}

		$this->_em->flush();

		return $entity->id;
	}
}
