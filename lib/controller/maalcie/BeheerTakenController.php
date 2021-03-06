<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\corvee\CorveeRepetitiesRepository;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\corvee\CorveeHerinneringService;
use CsrDelft\service\corvee\CorveeToewijzenService;
use CsrDelft\view\formulier\invoervelden\LidObjectField;
use CsrDelft\view\maalcie\forms\RepetitieCorveeForm;
use CsrDelft\view\maalcie\forms\TaakForm;
use CsrDelft\view\maalcie\forms\ToewijzenForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class BeheerTakenController extends AbstractController {
	/** @var CorveeTakenRepository */
	private $corveeTakenRepository;
	/** @var MaaltijdenRepository */
	private $maaltijdenRepository;
	/** @var CorveeRepetitiesRepository */
	private $corveeRepetitiesRepository;
	/** @var CorveeToewijzenService */
	private $corveeToewijzenService;
	/** @var CorveeHerinneringService */
	private $corveeHerinneringService;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		CorveeTakenRepository $corveeTakenRepository,
		MaaltijdenRepository $maaltijdenRepository,
		CorveeRepetitiesRepository $corveeRepetitiesRepository,
		CorveeToewijzenService $corveeToewijzenService,
		CorveeHerinneringService $corveeHerinneringService
	) {
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeRepetitiesRepository = $corveeRepetitiesRepository;
		$this->corveeToewijzenService = $corveeToewijzenService;
		$this->corveeHerinneringService = $corveeHerinneringService;
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return TemplateView
	 * @Route("/corvee/beheer/maaltijd/{maaltijd_id}", methods={"GET"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function maaltijd(Maaltijd $maaltijd) {
		return $this->beheer(null, $maaltijd);
	}

	/**
	 * @param CorveeTaak|null $taak
	 * @param Maaltijd|null $maaltijd
	 * @return TemplateView
	 * @Route("/corvee/beheer/{taak_id<\d*>}/{maaltijd_id<\d*>}", methods={"GET"}, defaults={"taak_id"=null,"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function beheer(CorveeTaak $taak = null, Maaltijd $maaltijd = null) {
		$modal = null;
		if ($taak) {
			$modal = $this->bewerk($taak);
		}
		if ($maaltijd) {
			$taken = $this->corveeTakenRepository->getTakenVoorMaaltijd($maaltijd->maaltijd_id, true);
		} else {
			$taken = $this->corveeTakenRepository->getAlleTaken();
			$maaltijd = null;
		}
		$model = [];
		if (isset($taken)) {
			foreach ($taken as $taak) {
				$datum = $taak->datum;
				if (!array_key_exists(date_format_intl($datum, DATE_FORMAT), $model)) {
					$model[date_format_intl($datum, DATE_FORMAT)] = array();
				}
				$model[date_format_intl($datum, DATE_FORMAT)][$taak->corveeFunctie->functie_id][] = $taak;
			}
		}
		return view('maaltijden.corveetaak.beheer_taken', [
			'taken' => $model,
			'maaltijd' => $maaltijd,
			'prullenbak' => false,
			'show' => $maaltijd !== null,
			'repetities' => $this->corveeRepetitiesRepository->getAlleRepetities(),
			'modal' => $modal,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return TaakForm
	 * @Route("/corvee/beheer/bewerk/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function bewerk(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}

		return new TaakForm($taak, 'opslaan/' . $taak->taak_id); // fetches POST values itself
	}

	/**
	 * @return TemplateView
	 * @Route("/corvee/beheer/prullenbak", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function prullenbak() {
		$taken = $this->corveeTakenRepository->getVerwijderdeTaken();
		$model = [];
		foreach ($taken as $taak) {
			$datum = $taak->datum;
			if (!array_key_exists(date_format_intl($datum, DATE_FORMAT), $model)) {
				$model[date_format_intl($datum, DATE_FORMAT)] = array();
			}
			$model[date_format_intl($datum, DATE_FORMAT)][$taak->corveeFunctie->functie_id][] = $taak;
		}
		return view('maaltijden.corveetaak.beheer_taken', [
			'taken' => $model,
			'maaltijd' => null,
			'repetities' => null,
			'prullenbak' => true,
			'show' => false,
		]);
	}

	/**
	 * @return RedirectResponse
	 * @Route("/corvee/beheren/herinneren", methods={"GET"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function herinneren() {
		$verstuurd_errors = $this->corveeHerinneringService->stuurHerinneringen();
		$verstuurd = $verstuurd_errors[0];
		$errors = $verstuurd_errors[1];
		$aantal = sizeof($verstuurd);
		$count = sizeof($errors);
		if ($count > 0) {
			setMelding($count . ' herinnering' . ($count !== 1 ? 'en' : '') . ' niet kunnen versturen!', -1);
			foreach ($errors as $error) {
				setMelding($error->getMessage(), 2); // toon wat fout is gegaan
			}
		}
		if ($aantal > 0) {
			setMelding($aantal . ' herinnering' . ($aantal !== 1 ? 'en' : '') . ' verstuurd!', 1);
			foreach ($verstuurd as $melding) {
				setMelding($melding, 1); // toon wat goed is gegaan
			}
		} else {
			setMelding('Geen herinneringen verstuurd.', 0);
		}
		return $this->redirectToRoute('csrdelft_maalcie_beheertaken_beheer');
	}

	/**
	 * @param CorveeTaak|null $taak
	 * @return RepetitieCorveeForm|TaakForm|TemplateView
	 * @throws Throwable
	 * @Route("/corvee/beheer/opslaan/{taak_id}", methods={"POST"}, defaults={"taak_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function opslaan(CorveeTaak $taak = null) {
		if ($taak) {
			$view = $this->bewerk($taak);
		} else {
			$view = $this->nieuw();
		}
		if ($view->validate()) {
			/** @var CorveeTaak $values */
			$values = $view->getModel();
			$taak = $this->corveeTakenRepository->saveTaak($values);
			$maaltijd = null;
			if (endsWith($_SERVER['HTTP_REFERER'], '/corvee/beheer/maaltijd/' . ($taak->maaltijd ? $taak->maaltijd->maaltijd_id : ''))) { // state of gui
				$maaltijd = $taak->maaltijd;
			}
			return view('maaltijden.corveetaak.beheer_taak_lijst', [
				'taak' => $taak,
				'maaltijd' => $maaltijd,
				'show' => true,
				'prullenbak' => false,
			]);
		}

		$this->entityManager->clear();

		return $view;
	}

	/**
	 * @param Maaltijd|null $maaltijd
	 * @return RepetitieCorveeForm|TaakForm
	 * @Route("/corvee/beheer/nieuw/{maaltijd_id}", methods={"POST"}, defaults={"maaltijd_id"=null})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function nieuw(Maaltijd $maaltijd = null) {
		$beginDatum = null;
		if ($maaltijd) {
			$beginDatum = $maaltijd->datum;
		}
		$crv_repetitie_id = filter_input(INPUT_POST, 'crv_repetitie_id', FILTER_SANITIZE_NUMBER_INT);
		if (!empty($crv_repetitie_id)) {
			$repetitie = $this->corveeRepetitiesRepository->getRepetitie((int)$crv_repetitie_id);
			if (!$maaltijd) {
				$beginDatum = $this->corveeRepetitiesRepository->getFirstOccurrence($repetitie);
				if ($repetitie->periode_in_dagen > 0) {
					return new RepetitieCorveeForm($repetitie, $beginDatum, $beginDatum); // fetches POST values itself
				}
			}
			$taak = $this->corveeTakenRepository->vanRepetitie($repetitie, date_create_immutable($beginDatum), $maaltijd);
			return new TaakForm($taak, 'opslaan'); // fetches POST values itself
		} else {
			$taak = new CorveeTaak();
			if ($beginDatum) {
				$taak->datum = $beginDatum;
			}
			$taak->maaltijd = $maaltijd;
			return new TaakForm($taak, 'opslaan'); // fetches POST values itself
		}
	}

	/**
	 * @param CorveeTaak $taak
	 * @Route("/corvee/beheer/verwijder/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function verwijder(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			$this->entityManager->remove($taak);
		} else {
			$taak->verwijderd = true;
		}
		$this->entityManager->flush();

		echo '<tr id="corveetaak-row-' . $taak->taak_id . '" class="remove"></tr>';
		exit;
	}

	/**
	 * @param CorveeTaak $taak
	 * @Route("/corvee/beheer/herstel/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function herstel(CorveeTaak $taak) {
		if (!$taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is niet verwijderd');
		}
		$taakId = $taak->taak_id;
		$taak->verwijderd = false;
		$this->entityManager->flush();

		echo '<tr id="corveetaak-row-' . $taakId . '" class="remove"></tr>';
		exit;
	}

	/**
	 * @param CorveeTaak $taak
	 * @return ToewijzenForm|TemplateView
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/beheer/toewijzen/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function toewijzen(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is verwijderd');
		}

		$lidField = new LidObjectField('profiel', null, null, 'leden'); // fetches POST values itself
		if ($lidField->validate()) {
			$this->corveeTakenRepository->taakToewijzenAanLid($taak, $taak->profiel, $lidField->getFormattedValue());
			return view('maaltijden.corveetaak.beheer_taak_lijst', [
				'taak' => $taak,
				'maaltijd' => null,
				'show' => true,
				'prullenbak' => false,
			]);
		} else {
			$suggesties = $this->corveeToewijzenService->getSuggesties($taak);
			return new ToewijzenForm($taak, $suggesties); // fetches POST values itself
		}
	}

	/**
	 * @param CorveeTaak $taak
	 * @return TemplateView
	 * @Route("/corvee/beheer/puntentoekennen/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function puntentoekennen(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			throw new CsrGebruikerException("Corveetaak is verwijderd");
		}

		$this->corveeTakenRepository->puntenToekennen($taak, $taak->profiel);

		$this->getDoctrine()->getManager()->flush();

		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return TemplateView
	 * @Route("/corvee/beheer/puntenintrekken/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function puntenintrekken(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			throw new CsrGebruikerException("Corveetaak is verwijderd");
		}

		$this->corveeTakenRepository->puntenIntrekken($taak, $taak->profiel);

		$this->getDoctrine()->getManager()->flush();

		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @param CorveeTaak $taak
	 * @return TemplateView
	 * @Route("/corvee/beheer/email/{taak_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function email(CorveeTaak $taak) {
		if ($taak->verwijderd) {
			throw new CsrGebruikerException("Corveetaak is verwijderd");
		}

		$this->corveeHerinneringService->stuurHerinnering($taak);

		return view('maaltijden.corveetaak.beheer_taak_lijst', [
			'taak' => $taak,
			'maaltijd' => null,
			'show' => true,
			'prullenbak' => false,
		]);
	}

	/**
	 * @return RedirectResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/corvee/beheer/leegmaken", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function leegmaken() {
		$aantal = $this->corveeTakenRepository->prullenbakLeegmaken();
		setMelding($aantal . ($aantal === 1 ? ' taak' : ' taken') . ' definitief verwijderd.', ($aantal === 0 ? 0 : 1));
		return $this->redirectToRoute('csrdelft_maalcie_beheertaken_prullenbak');
	}

	// Repetitie-Taken ############################################################

	/**
	 * @param CorveeRepetitie $corveeRepetitie
	 * @return RepetitieCorveeForm|TemplateView
	 * @throws Throwable
	 * @Route("/corvee/beheer/aanmaken/{crv_repetitie_id}", methods={"POST"})
	 * @Auth(P_CORVEE_MOD)
	 */
	public function aanmaken(CorveeRepetitie $corveeRepetitie) {
		$form = new RepetitieCorveeForm($corveeRepetitie); // fetches POST values itself

		if ($form->validate()) {
			$values = $form->getValues();
			$maaltijd_id = (empty($values['maaltijd_id']) ? null : (int)$values['maaltijd_id']);
			$maaltijd = $maaltijd_id ? $this->maaltijdenRepository->find($maaltijd_id) : null;
			$taken = $this->corveeTakenRepository->maakRepetitieTaken(
				$corveeRepetitie,
				$form->findByName('begindatum')->getFormattedValue(),
				$form->findByName('einddatum')->getFormattedValue(),
				$maaltijd
			);

			if (empty($taken)) {
				throw new CsrGebruikerException('Geen nieuwe taken aangemaakt.');
			}
			return view('maaltijden.corveetaak.beheer_taken_response', ['taken' => $taken]);
		} else {
			return $form;
		}
	}
}
