<?php

require_once 'configuratie.include.php';

require_once('courant/courant.class.php');
$courant = new Courant();
if (!$courant->magToevoegen()) {
	header('location: ' . CSR_ROOT);
	exit;
}

require_once('courant/courantbeheercontent.class.php');
$body = new CourantBeheerContent($courant);

//url waarheen standaard gerefreshed wordt
$courant_url = CSR_ROOT . '/actueel/courant';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($courant->valideerBerichtInvoer() === true) {
		$iBerichtID = (int) $_GET['ID'];
		if ($iBerichtID == 0) {
			//nieuw bericht invoeren
			if ($courant->addBericht($_POST['titel'], $_POST['categorie'], $_POST['bericht'])) {
				setMelding('<h3>Dank u</h3>Uw bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);
				if (isset($_SESSION['compose_snapshot'])) {
					$_SESSION['compose_snapshot'] = null;
				}
			} else {
				setMelding('<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.', -1);
				$courant_url .= '/?ID=0';
			}
		} else {
			//bericht bewerken.
			if ($courant->bewerkBericht($iBerichtID, $_POST['titel'], $_POST['categorie'], $_POST['bericht'])) {
				setMelding('<h3>Dank u</h3>Uw bewerkte bericht is opgenomen in ons databeest, en het zal in de komende C.S.R.-courant verschijnen.', 1);
				if (isset($_SESSION['compose_snapshot'])) {
					$_SESSION['compose_snapshot'] = null;
				}
			} else {
				setMelding('<h1>Fout</h1>Er ging iets mis met het invoeren van uw bericht. Probeer opnieuw, of stuur uw bericht in een mail naar <a href="mailto:pubcie@csrdelft.nl">pubcie@csrdelft.nl</a>.', -1);
				$courant_url .= '/bewerken/' . $iBerichtID;
			}
		}
		invokeRefresh($courant_url, $courant->getError());
	} else {
		if (isset($_GET['ID']) AND $_GET['ID'] == 0) {
			//nieuw bericht
			setMelding($courant->getError(), -1);
		} else {
			//bewerken
			setMelding($courant->getError(), -1);
			$body->edit((int) $_GET['ID']);
		}
	}
} else {
	if (isset($_GET['ID'])) {
		$iBerichtID = (int) $_GET['ID'];
		if (isset($_GET['verwijder'])) {
			if ($courant->verwijderBericht($iBerichtID)) {
				invokeRefresh($courant_url, '<h3>Uw bericht is verwijderd.</h3>', 1);
			} else {
				invokeRefresh($courant_url, '<h3>Er ging iets mis!</h3>Uw bericht is niet verwijderd. Probeer het a.u.b. nog eens.');
			}
		}
		if (isset($_GET['bewerken'])) {
			//bericht bewerken.
			$body->edit($iBerichtID);
		}
	}
}
$pagina = new CsrLayoutPage($body);
$pagina->view();
?>
