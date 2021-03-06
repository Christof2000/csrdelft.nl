<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\renderer\TemplateView;
use CsrDelft\view\toestemming\ToestemmingLijstResponse;
use CsrDelft\view\toestemming\ToestemmingLijstTable;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingController extends AbstractController {
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(LidToestemmingRepository $lidToestemmingRepository, CmsPaginaRepository $cmsPaginaRepository) {
		$this->lidToestemmingRepository = $lidToestemmingRepository;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
	}

	/**
	 * @throws Exception
	 * @Route("/toestemming", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function POST_overzicht() {
		$form = new ToestemmingModalForm($this->lidToestemmingRepository);

		if ($form->isPosted() && $form->validate()) {

			$this->lidToestemmingRepository->saveForLid();
			setMelding('Toestemming opgeslagen', 1);
			return new CmsPaginaView($this->cmsPaginaRepository->find('thuis'));
		} else {
			return $form;
		}
	}

	/**
	 * @return TemplateView
	 * @throws Exception
	 * @Route("/toestemming", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function GET_overzicht() {
		return view('default', ['content' => new CmsPaginaView($this->cmsPaginaRepository->find('thuis')), 'modal' => new ToestemmingModalForm($this->lidToestemmingRepository)]);
	}

	/**
	 * @return CmsPaginaView
	 * @Route("/toestemming/annuleren", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function POST_annuleren() {
		$_SESSION['stop_nag'] = time();

		return new CmsPaginaView($this->cmsPaginaRepository->find('thuis'));
	}

	/**
	 * @return RedirectResponse
	 * @Route("/toestemming/annuleren", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function GET_annuleren() {
		$_SESSION['stop_nag'] = time();

		return $this->redirectToRoute('default');
	}

	/**
	 * @param Request $request
	 * @return TemplateView|ToestemmingLijstResponse
	 * @Route("/toestemming/lijst", methods={"GET","POST"})
	 * @Auth({P_LEDEN_MOD,P_ALBUM_MOD,"commissie:promocie:ht"})
	 */
	public function lijst(Request $request) {
		if (LoginService::mag(P_LEDEN_MOD)) {
			$ids = ['foto_intern', 'foto_extern', 'vereniging', 'bijzonder'];
		} else if (LoginService::mag(P_ALBUM_MOD)) {
			$ids = ['foto_intern', 'foto_extern'];
		} else if (LoginService::mag('commissie:promocie:ht')) {
			$ids = ['foto_intern', 'foto_extern'];
		} else {
			throw $this->createAccessDeniedException('Geen toegang');
		}

		if ($request->getMethod() === 'POST') {
			$filter = $request->query->get('filter', 'leden');

			$filterStatus = [
				'leden' => LidStatus::getLidLike(),
				'oudleden' => LidStatus::getOudlidLike(),
				'ledenoudleden' => array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike()),
				'iedereen' => LidStatus::getEnumValues(),
			];

			$toestemming = group_by('uid', $this->lidToestemmingRepository->getToestemmingForIds($ids));

			$toestemmingFiltered = [];
			foreach ($toestemming as $uid => $toestemmingen) {
				$profiel = ProfielRepository::get($uid);

				if (in_array($profiel->status, $filterStatus[$filter])) {
					$toestemmingFiltered[] = $toestemmingen;
				}
			}

			return new ToestemmingLijstResponse($toestemmingFiltered, $ids);
		} else {
			return view('pagina', [
				'titel' => 'Lid toestemming',
				'breadcrumbs' => 'Lid toestemmingen',
				'body' => new ToestemmingLijstTable($ids)
			]);
		}
	}
}
