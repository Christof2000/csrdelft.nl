<?php

/**
 * index.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor stek modules.
 */
try {
	require_once 'configuratie.include.php';

	$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

	if (empty($class)) {
		$class = 'CmsPagina';
	}
	// Toegang tot leden website dicht-timmeren
	switch ($class) {
		case 'Login':
		case 'CmsPagina':
		case 'Forum':
		case 'FotoAlbum':
		case 'Agenda':
			break; // toegestaan voor iedereen
		default: // alleen ingelode gebruikers
			if (!LoginModel::mag('P_LOGGED_IN')) {
				header('location: ' . CSR_ROOT);
				exit;
			}
	}
	$class .= 'Controller';

	$request = Instellingen::get('stek', 'request');

	require_once 'MVC/controller/' . $class . '.class.php';
	$controller = new $class($request);
	$controller->performAction();

	if (defined('DB_MODIFY_ENABLE') AND LoginModel::mag('P_ADMIN')) {

		require_once 'MVC/model/DatabaseAdmin.singleton.php';
		$queries = DatabaseAdmin::getQueries();

		if (empty($queries)) {
			debugprint('DB_MODIFY_ENABLED');
		} else {
			header('Content-Type: text/x-sql');
			header('Content-disposition: attachment;filename=DB_modify_' . time() . '.sql');
			foreach ($queries as $query) {
				echo $query . ";\n";
			}
			exit;
		}
	}
	$controller->getView()->view();
}
catch (Exception $e) {
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	$code = ($e->getCode() >= 100 ? $e->getCode() : 500);
	header($protocol . ' ' . $code . ' ' . $e->getMessage());

	if (defined('DEBUG') && (LoginModel::mag('P_ADMIN') || LoginModel::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace 
	} else {
		DebugLogModel::instance()->log('index.php', 'new ' . $class, array($request), $e);
	}
}
