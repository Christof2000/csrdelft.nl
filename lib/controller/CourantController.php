<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\courant\Courant;
use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\repository\CourantBerichtRepository;
use CsrDelft\repository\CourantRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\courant\CourantBerichtFormulier;
use CsrDelft\view\courant\CourantView;
use CsrDelft\view\PlainView;
use CsrDelft\view\renderer\TemplateView;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de courant.
 */
class CourantController extends AbstractController {
	/**
	 * @var CourantRepository
	 */
	private $courantRepository;
	/**
	 * @var CourantBerichtRepository
	 */
	private $courantBerichtRepository;

	public function __construct(CourantRepository $courantRepository, CourantBerichtRepository $courantBerichtRepository) {
		$this->courantRepository = $courantRepository;
		$this->courantBerichtRepository = $courantBerichtRepository;
	}

	/**
	 * @return TemplateView
	 * @Route("/courant/archief", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function archief() {
		return view('courant.archief', ['couranten' => $this->courantRepository->findAll()]);
	}

	/**
	 * @param Courant $courant
	 * @return Response
	 * @Route("/courant/bekijken/{id}", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function bekijken(Courant $courant) {
		return new Response($courant->inhoud);
	}

	/**
	 * @return CourantView
	 * @Route("/courant/voorbeeld", methods={"GET"})
	 * @Auth(P_LEDEN_READ)
	 */
	public function voorbeeld() {
		return new CourantView($this->courantRepository->nieuwCourant(), $this->courantBerichtRepository->findAll());
	}

	/**
	 * @return TemplateView|RedirectResponse
	 * @Route("/courant", methods={"GET", "POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function toevoegen() {
		$bericht = new CourantBericht();
		$bericht->datumTijd = new DateTime();
		$bericht->uid = $this->getUid();
		$bericht->schrijver = $this->getProfiel();

		$form = new CourantBerichtFormulier($bericht, '/courant');

		if ($form->isPosted() && $form->validate()) {
			$bericht->setVolgorde();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($bericht);
			$manager->flush();
			setMelding('Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);

			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return view('courant.beheer', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	/**
	 * @param CourantBericht $bericht
	 * @return TemplateView|RedirectResponse
	 * @Route("/courant/bewerken/{id}", methods={"GET", "POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function bewerken(CourantBericht $bericht) {
		$form = new CourantBerichtFormulier($bericht, '/courant/bewerken/' . $bericht->id);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()->getManager()->flush();
			setMelding('Bericht is bewerkt', 1);
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		return view('courant.beheer', [
			'magVerzenden' => $this->courantRepository->magVerzenden(),
			'magBeheren' => $this->courantRepository->magBeheren(),
			'berichten' => $this->courantBerichtRepository->getBerichtenVoorGebruiker(),
			'form' => $form,
		]);
	}

	/**
	 * @param CourantBericht $bericht
	 * @return RedirectResponse
	 * @Route("/courant/verwijderen/{id}", methods={"POST"})
	 * @Auth(P_MAIL_POST)
	 */
	public function verwijderen(CourantBericht $bericht) {
		if (!$bericht->magBeheren()) {
			throw $this->createAccessDeniedException();
		}

		try {
			$manager = $this->getDoctrine()->getManager();
			$manager->remove($bericht);
			$manager->flush();

			setMelding('Uw bericht is verwijderd.', 1);
		} catch (Exception $exception) {
			setMelding('Uw bericht is niet verwijderd.', -1);
		}
		return $this->redirectToRoute('csrdelft_courant_toevoegen');
	}

	/**
	 * @param null $iedereen
	 * @return PlainView|RedirectResponse
	 * @throws ConnectionException
	 * @Route("/courant/verzenden/{iedereen}", methods={"POST"}, defaults={"iedereen": null})
	 * @Auth(P_MAIL_SEND)
	 */
	public function verzenden($iedereen = null) {
		if (count($this->courantBerichtRepository->findAll()) < 1) {
			setMelding('Lege courant kan niet worden verzonden', 0);
			return $this->redirectToRoute('csrdelft_courant_toevoegen');
		}

		$courant = $this->courantRepository->nieuwCourant();

		$courantView = new CourantView($courant, $this->courantBerichtRepository->findAll());
		$courant->inhoud = $courantView->getHtml(false);
		if ($iedereen === 'iedereen') {
			$this->courantRepository->verzenden($_ENV['EMAIL_LEDEN'], $courantView);
			/** @var Connection $conn */
			$conn = $this->getDoctrine()->getConnection();
			$conn->beginTransaction();

			try {
				$manager = $this->getDoctrine()->getManager();
				$manager->persist($courant);

				$berichten = $this->courantBerichtRepository->findAll();

				foreach ($berichten as $bericht) {
					$manager->remove($bericht);
				}

				$manager->flush();
				$conn->commit();

				setMelding('De courant is verzonden naar iedereen', 1);
			} catch (Exception $exception) {
				$conn->rollBack();
				setMelding('Courant niet verzonden', -1);
			}

			return new PlainView('<div id="courantKnoppenContainer">' . getMelding() . '<strong>Aan iedereen verzonden</strong></div>');
		} else {
			$this->courantRepository->verzenden($_ENV['EMAIL_PUBCIE'], $courantView);
			setMelding('Verzonden naar de PubCie', 1);
			return new PlainView('<div id="courantKnoppenContainer">' . getMelding() . '<a class="btn btn-primary post confirm" title="Courant aan iedereen verzenden" href="/courant/verzenden/iedereen">Aan iedereen verzenden</a></div>');
		}
	}
}
