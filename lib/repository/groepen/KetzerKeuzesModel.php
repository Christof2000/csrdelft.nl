<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\KetzerKeuze;
use CsrDelft\entity\groepen\KetzerOptie;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzerKeuzesModel extends AbstractGroepenRepository {

	public function __construct(AccessModel $accessModel, ManagerRegistry $managerRegistry) {
		parent::__construct($accessModel, $managerRegistry, KetzerKeuze::class);
	}

	const ORM = KetzerKeuze::class;

	public function getKeuzesVoorOptie(KetzerOptie $optie) {
		return $this->prefetch('optie_id = ?', array($optie->optie_id));
	}

}
