<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\renderer\TemplateView;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class LidInstellingenController extends AbstractController {
	/** @var LidInstellingenRepository  */
	private $lidInstellingenRepository;

	public function __construct(LidInstellingenRepository $lidInstellingenRepository) {
		$this->lidInstellingenRepository = $lidInstellingenRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/instellingen", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheer() {
		return view('instellingen.lidinstellingen', [
			'defaultInstellingen' => $this->lidInstellingenRepository->getAll(),
			'instellingen' => $this->lidInstellingenRepository->getAllForLid($this->getUid())
		]);
	}

	/**
	 * @param Request $request
	 * @param $module
	 * @param $instelling
	 * @param null $waarde
	 * @return JsonResponse
	 * @Route("/instellingen/update/{module}/{instelling}/{waarde}", methods={"POST"}, defaults={"waarde": null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function update(Request $request, $module, $instelling, $waarde = null) {
		if ($waarde === null) {
			$waarde = $request->request->get('waarde');
		}

		if ($this->lidInstellingenRepository->isValidValue($module, $instelling, urldecode($waarde))) {
			$this->lidInstellingenRepository->wijzigInstelling($module, $instelling, urldecode($waarde));
			return new JsonResponse(['success' => true]);
		} else {
			return new JsonResponse(['success' => false], 400);
		}
	}

	/**
	 * @throws Exception
	 * @Route("/instellingen/opslaan", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function opslaan() {
		$this->lidInstellingenRepository->saveAll(); // fetches $_POST values itself
		setMelding('Instellingen opgeslagen', 1);
		return $this->redirectToRoute('csrdelft_lidinstellingen_beheer');
	}

	/**
	 * @param string $module
	 * @param string $key
	 * @return JsonResponse
	 * @Route("/instellingen/reset/{module}/{key}", methods={"POST"})
	 * @Auth(P_ADMIN)
	 */
	public function reset($module, $key) {
		$this->lidInstellingenRepository->resetForAll($module, $key);
		setMelding('Voor iedereen de instelling ge-reset naar de standaard waarde', 1);
		return new JsonResponse(true);
	}

}
