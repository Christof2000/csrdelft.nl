<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\LDAP;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\groepen\ActiviteitenRepository;
use CsrDelft\repository\LogRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\SavedQueryRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\Roodschopper;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\PlainView;
use CsrDelft\view\renderer\TemplateView;
use CsrDelft\view\roodschopper\RoodschopperForm;
use CsrDelft\view\SavedQueryContent;
use CsrDelft\view\Streeplijstcontent;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deze controller bevat een aantal beheertools die niet direct onder een andere controller geschaard kunnen worden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class ToolsController extends AbstractController {
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var LogRepository
	 */
	private $logRepository;
	/**
	 * @var SavedQueryRepository
	 */
	private $savedQueryRepository;
	/**
	 * @var ProfielService
	 */
	private $profielService;

	public function __construct(AccountRepository $accountRepository, ProfielRepository $profielRepository, ProfielService $profielService, SuService $suService, LogRepository $logRepository, SavedQueryRepository $savedQueryRepository) {
		$this->savedQueryRepository = $savedQueryRepository;
		$this->accountRepository = $accountRepository;
		$this->profielRepository = $profielRepository;
		$this->suService = $suService;
		$this->logRepository = $logRepository;
		$this->profielService = $profielService;
	}

	/**
	 * @return PlainView|TemplateView
	 * @Route("/tools/streeplijst", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function streeplijst() {
		$body = new Streeplijstcontent();

		# yuck
		if (isset($_GET['iframe'])) {
			return new PlainView($body->getHtml());
		} else {
			return view('default', ['content' => $body]);
		}
	}

	/**
	 * @param Request $request
	 * @return TemplateView
	 * @Route("/tools/stats", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function stats(Request $request) {
		if ($request->query->has('uid')) {
			$by = ['uid' => $request->query->get('uid')];
		} elseif ($request->query->has('ip')) {
			$by = ['ip' => $request->query->get('ip')];
		} else {
			$by = [];
		}

		$log = $this->logRepository->findBy($by, ['ID' => 'desc'], 30);

		return view('stats.stats', ['log' => $log]);
	}

	/**
	 * @return TemplateView
	 * @Route("/tools/verticalelijsten", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function verticalelijsten() {
		return view('tools.verticalelijst', [
			'verticalen' => array_reduce(
				['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'],
				function ($carry, $letter) {
					$carry[$letter] = $this->profielRepository->createQueryBuilder('p')
						->where('p.verticale = :verticale and p.status in (:lidstatus)')
						->setParameter('verticale', $letter)
						->setParameter('lidstatus', LidStatus::getFiscaalLidLike())
						->getQuery()->getResult();
					return $carry;
				},
				[]
			)
		]);
	}

	/**
	 * @param Request $request
	 * @return TemplateView|RedirectResponse
	 * @Route("/tools/roodschopper", methods={"GET", "POST"})
	 * @Auth(P_FISCAAT_MOD)
	 */
	public function roodschopper(Request $request) {
		if ($request->query->has('verzenden')) {
			return view('roodschopper.roodschopper', [
				'verzenden' => true,
				'aantal' => $request->query->get('aantal'),
			]);
		}

		$roodschopper = Roodschopper::getDefaults();
		$roodschopperForm = new RoodschopperForm($roodschopper);

		if ($roodschopperForm->isPosted() && $roodschopperForm->validate() && $roodschopper->verzenden) {
			$roodschopper->sendMails();
			// Voorkom dubbele submit
			return $this->csrRedirect('/tools/roodschopper?verzenden=true&aantal=' . count($roodschopper->getSaldi()));
		} else {
			$roodschopper->generateMails();
		}

		return view('roodschopper.roodschopper', [
			'verzenden' => false,
			'form' => $roodschopperForm,
			'saldi' => $roodschopper->getSaldi(),
		]);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/syncldap", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function syncldap() {
		if (DEBUG || LoginService::mag(P_ADMIN) || $this->suService->isSued()) {
			$ldap = new LDAP();
			foreach ($this->profielRepository->findAll() as $profiel) {
				$this->profielRepository->save_ldap($profiel, $ldap);
			}

			$ldap->disconnect();

			return new PlainView('done');
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @return PlainView
	 * @Route("/tools/phpinfo", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function phpinfo() {
		ob_start();
		phpinfo();
		return new PlainView(ob_get_clean());
	}

	/**
	 * @return TemplateView
	 * @Route("/tools/admins", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function admins() {
		return view('tools.admins', [
			'accounts' => $this->accountRepository->findAdmins(),
		]);
	}

	/**
	 * Voor de NovCie, zorgt ervoor dat novieten bekeken kunnen worden als dat afgeschermd is op de rest van de stek.
	 *
	 * @return View
	 * @Route("/tools/novieten", methods={"GET"})
	 * @Auth({P_ADMIN,"commissie:NovCie"})
	 */
	public function novieten() {
		return view('tools.novieten', [
			'novieten' => $this->profielRepository->findBy(['status' => LidStatus::Noviet, 'lidjaar' => date('Y')])
		]);
	}

	/**
	 * @return JsonResponse
	 * @Route("/tools/dragobject", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function dragobject() {
		$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
		$coords = filter_input(INPUT_POST, 'coords', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

		$_SESSION['dragobject'][$id] = $coords;

		return new JsonResponse(null);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/naamlink", methods={"GET", "POST"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamlink() {
//is er een uid gegeven?
		$given = 'uid';
		if (isset($_GET['uid'])) {
			$string = urldecode($_GET['uid']);
		} elseif (isset($_POST['uid'])) {
			$string = $_POST['uid'];

//is er een naam gegeven?
		} elseif (isset($_GET['naam'])) {
			$string = urldecode($_GET['naam']);
			$given = 'naam';
		} elseif (isset($_POST['naam'])) {
			$string = $_POST['naam'];
			$given = 'naam';
		} else { //geen input
			throw new CsrGebruikerException('Geen naam invoer in naamlink');
		}

//welke subset van leden?
		$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		$toegestanezoekfilters = ['leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies'];
		if (isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}

		function uid2naam($uid) {
			$naam = ProfielRepository::getLink($uid, 'civitas');
			if ($naam) {
				return $naam;
			} else {
				return 'Lid[' . htmlspecialchars($uid) . '] &notin; db.';
			}
		}

		if ($given == 'uid') {
			if ($this->accountRepository->isValidUid($string)) {
				return new PlainView(uid2naam($string));
			} else {
				$uids = explode(',', $string);
				foreach ($uids as $uid) {
					return new PlainView(uid2naam($uid));
				}
			}
		} elseif ($given == 'naam') {
			$namen = $this->profielService->zoekLeden($string, 'naam', 'alle', 'achternaam', $zoekin);
			if (!empty($namen)) {
				if (count($namen) === 1) {
					return new PlainView($namen[0]->getLink('civitas'));
				} else {
					return new PlainView('Meerdere leden mogelijk');
				}
			}
			return new PlainView('Geen lid gevonden');
		}

		throw new NotFoundHttpException();
	}

	/**
	 * @param null $zoekin
	 * @param string $query
	 * @return JsonResponse
	 * @Route("/tools/naamsuggesties", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 */
	public function naamsuggesties($zoekin = null, $query = '') {
		//welke subset van leden?
		if (empty($zoekin)) {
			$zoekin = array_merge(LidStatus::getLidLike(), LidStatus::getOudlidLike());
		}
		$toegestanezoekfilters = array('leden', 'oudleden', 'novieten', 'alleleden', 'allepersonen', 'nobodies');
		if (empty($zoekin) && isset($_GET['zoekin']) && in_array($_GET['zoekin'], $toegestanezoekfilters)) {
			$zoekin = $_GET['zoekin'];
		}
		if (empty($zoekin) && isset($_GET['zoekin']) && $_GET['zoekin'] === 'voorkeur') {
			$zoekin = lid_instelling('forum', 'lidSuggesties');
		}

		if (empty($query) && isset($_GET['q'])) {
			$query = $_GET['q'];
		}
		$limiet = 20;
		if (isset($_GET['limit'])) {
			$limiet = (int)$_GET['limit'];
		}

		$toegestaneNaamVormen = ['user', 'volledig', 'streeplijst', 'voorletters', 'bijnaam', 'Duckstad', 'civitas', 'aaidrom'];
		$vorm = 'volledig';
		if (isset($_GET['vorm']) && in_array($_GET['vorm'], $toegestaneNaamVormen)) {
			$vorm = $_GET['vorm'];
		}

		$profielen = $this->profielService->zoekLeden($query, 'naam', 'alle', 'achternaam', $zoekin, $limiet);

		$scoredProfielen = [];
		foreach ($profielen as $profiel) {
			$score = 0;

			// Beste match start met de zoekterm
			if (startsWith(strtolower($profiel->getNaam('volledig')), strtolower($query))) {
				$score += 100;
			}

			// Zoek meest lijkende match
			$score -= levenshtein($query, $profiel->getNaam());

			$scoredProfielen[] = [
				'profiel' => $profiel,
				'score' => $score,
			];
		}

		usort($scoredProfielen, function ($a, $b) {
			return $b['score'] - $a['score'];
		});

		$scoredProfielen = array_slice($scoredProfielen, 0, 5);

		$result = array();
		foreach ($scoredProfielen as $scoredProfiel) {
			/** @var Profiel $profiel */
			$profiel = $scoredProfiel['profiel'];

			$result[] = array(
				'icon' => Icon::getTag('profiel', null, 'Profiel', 'mr-2'),
				'url' => '/profiel/' . $profiel->uid,
				'label' => $profiel->uid,
				'value' => $profiel->getNaam($vorm),
				'uid' => $profiel->uid,
			);
		}

		return new JsonResponse($result);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/memcachestats", methods={"GET"})
	 * @Auth(P_ADMIN)
	 */
	public function memcachestats() {
		if (DEBUG || LoginService::mag(P_ADMIN) || $this->suService->isSued()) {
			ob_start();

			echo getMelding();
			echo '<h1>MemCache statistieken</h1>';
			debugprint($this->get('stek.cache.memcache')->getStats());

			return new PlainView(ob_get_clean());
		}

		throw $this->createAccessDeniedException();
	}

	/**
	 * @param Request $request
	 * @return TemplateView
	 * @Route("/tools/query", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function query(Request $request) {
		if ($request->query->has('id')) {
			$id = $request->query->getInt('id');
			$result = $this->savedQueryRepository->loadQuery($id);
		} else {
			$result = null;
		}

		return view('default', [
			'content' => new SavedQueryContent($result),
		]);
	}

	/**
	 * @return PlainView
	 * @Route("/tools/bbcode", methods={"GET", "POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function bbcode() {
		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);

		if (isset($_POST['data'])) {
			$string = urldecode($_POST['data']);
		} elseif (isset($_GET['data'])) {
			$string = $_GET['data'];
		} elseif (isset($input['data'])) {
			$string = urldecode($input['data']);
		} else {
			$string = 'b0rkb0rkb0rk: geen invoer in htdocs/tools/bbcode';
		}

		$string = trim($string);

		if (isset($_POST['mail']) || isset($input['mail'])) {
			return new PlainView(CsrBB::parseMail($string));
		} else {
			return new PlainView(CsrBB::parse($string));
		}
	}

	/**
	 * Voor patronaat 2019 kan september 2019 verwijderd worden.
	 *
	 * @param ActiviteitenRepository $activiteitenRepository
	 * @return View
	 * @Route("/tools/patronaat", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function patronaat(ActiviteitenRepository $activiteitenRepository) {
		return view('patronaat', ['groep' => $activiteitenRepository->get(1754)]);
	}
}
