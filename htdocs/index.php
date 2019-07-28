<?php
/**
 * index.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Entry point voor stek modules.
 */

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\controller\AgendaController;
use CsrDelft\controller\CmsPaginaController;
use CsrDelft\controller\ContactFormulierController;
use CsrDelft\controller\ForumController;
use CsrDelft\controller\FotoAlbumController;
use CsrDelft\controller\framework\Controller;
use CsrDelft\controller\LoginController;
use CsrDelft\controller\MededelingenController;
use CsrDelft\controller\ToolsController;
use CsrDelft\controller\WachtwoordController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\TimerModel;
use CsrDelft\Orm\Persistence\DatabaseAdmin;
use CsrDelft\service\CsrfService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutPage;
use Invoker\Invoker;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

require_once 'configuratie.include.php';

try {
	if (isset($_GET['c'])) {
		$legacy = true;
		// We hebben een oude controller te pakken
		// start MVC
		$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);
		$method = 'performAction';
		$parameters = [];

		if (empty($class)) {
			$class = 'CmsPagina';
		}

		$class = 'CsrDelft\\controller\\' . $class . 'Controller';
	} else {
		$legacy = false;
		// Laat Symfony routen
		$requestContext = new RequestContext();
		$requestContext->fromRequest(Request::createFromGlobals());
		$router = new Router(
			new YamlFileLoader(new FileLocator([LIB_PATH])),
			'config/routes.yaml',
			['cache_dir' => ROUTES_CACHE_PATH, 'debug' => DEBUG],
			$requestContext
		);

		$parameters = $router->match(strtok(REQUEST_URI, '?'));

		$allowCsrf = $router->getRouteCollection()->get($parameters['_route'])->getOption('allow_csrf') ?? false;
		if (!$allowCsrf && $_SERVER['REQUEST_METHOD'] != 'GET') {
			CsrfService::preventCsrf();
		}

		$acl = $router->getRouteCollection()->get($parameters['_route'])->getOption('mag');

		if ($acl == null) {
			throw new CsrException(sprintf('Route "%s" moet een "mag" optie hebben.', $parameters['_route']));
		}
		if (!LoginModel::mag($acl)) {
			throw new CsrToegangException('Geen toegang');
		}

		list($class, $method) = explode('::', $parameters['_controller']);
	}

	// toegang tot leden website dicht-timmeren:
	switch ($class) {
		// toegestaan voor iedereen:
		case LoginController::class:
		case WachtwoordController::class:
		case CmsPaginaController::class:
		case ForumController::class:
		case FotoAlbumController::class:
		case AgendaController::class:
		case MededelingenController::class:
		case ContactFormulierController::class:
		case ToolsController::class:
			break;

		// de rest alleen voor ingelogde gebruikers:
		default:
			if (!LoginModel::mag(P_LOGGED_IN)) {
				redirect_via_login(REQUEST_URI);
			}
	}

	if (class_exists($class)) {
		/** @var Controller $controller */
		$controller = new $class(REQUEST_URI);
	} else {
		http_response_code(404);
		exit;
	}

	if ($legacy) {
		$controller->performAction();
		$view = $controller->getView();
	} else {
		$view = (new Invoker())->call([$controller, $method], $parameters);
	}
} catch (ResourceNotFoundException $exception) {
	http_response_code(404);
	$view = view('fout.404');
} catch (MethodNotAllowedException $exception) {
	http_response_code(404);
	$view = view('fout.404');
} catch (CsrGebruikerException $exception) {
	http_response_code(400);
	$view = view('fout.400', ['bericht' => $exception->getMessage()]);
} catch (CsrToegangException $exception) {
	http_response_code($exception->getCode());
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		die($exception->getMessage());
	} // Redirect to login form
	elseif (LoginModel::getUid() === 'x999') {
		redirect_via_login(REQUEST_URI);
	}
	switch ($exception->getCode()) {
		case 404:
			$view = view('fout.404');
			break;
		case 403:
			$view = view('fout.403');
			break;
		case 400:
			$view = view('fout.400', ['bericht' => $e->getMessage()]);
			break;
		default:
			$view = view('fout.500');
			break;
	}
}


if (DB_CHECK AND LoginModel::mag(P_ADMIN)) {

    $queries = DatabaseAdmin::instance()->getQueries();
    if (!empty($queries)) {
        if (DB_MODIFY) {
            header('Content-Type: text/x-sql');
            header('Content-Disposition: attachment;filename=DB_modify_' . time() . '.sql');
            foreach ($queries as $query) {
                echo $query . ";\n";
            }
            exit;
        } else {
            debugprint($queries);
        }
    }
}

if (TIME_MEASURE) {
    TimerModel::instance()->time();
}

$view->view();
// einde MVC
