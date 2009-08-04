<?php
// Require common application file
define('CX_ROOT_WEB', dirname(__FILE__));
require('app/common.php');

$app = false;
$response = false;

try {
	$cx = cx();
	$cx->addPath($cx->config('cx.path_lib'));
	$cx->addPath($cx->config('cx.path_core'));
	$cx->event('startup');

	// Setup Request / Response objects
	$request = $cx->get('Cx_Request');
	$response = $cx->get('Cx_Response');

	// Router to match HTTP requests
	$router = $cx->get('Cx_Router');
	$router->addRoute('(*page)', array('page' => '/', 'module' => 'page', 'action' => 'index', 'format' => 'html'), 'page');
	//$router->addRoute('(*page)m.(:module)/(:action)', array('page' => '/', 'module' => 'page', 'action' => 'index', 'format' => 'html'), 'page');
	//$router->addRoute('(:module)/(:action)', array('module' => 'page', 'action' => 'index', 'format' => 'html'), 'module_action');
	//$router->addRoute('(:module)/(:action).(:format)', array('module' => 'page', 'action' => 'index', 'format' => 'html'), 'module_action_format');
	//$router->addRoute('(:module)/(:action)/(:id)', array('module' => 'page', 'action' => 'index', 'id' => null, 'format' => 'html'), 'module_item');
	//$router->addRoute('(:module)/(:action)/(:id).(:format)', array('module' => 'page', 'action' => 'index', 'id' => null, 'format' => 'html'), 'module_item_format');
	$requestUrl = isset($_GET['r']) ? $_GET['r'] : '/';
	$requestParams = $router->match($requestUrl);
	
	//cx_dump($requestParams);
	//cx_dump($router->getRoutes());
	
	// Set request parameters parsed from router
	$request->setParams($requestParams);

	// Load front controller
	$app = $cx->get('Cx_Controller_Front', array($cx));

	// Set data parsed from router
	$app->setModule($request->getParam('module', $cx->config('cx.default.module')));
	$app->setAction($request->getParam('action', $cx->config('cx.default.action')));

	// Run/execute
	$responseStatus = 200;
	$content = $app->run($request, $response);

// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e->getMessage();
	$response->redirect(cx_url(array('module' => 'user', 'action' => 'login')));
	
// 404 Errors
} catch(Cx_Exception_FileNotFound $e) {
	$responseStatus = 404;
	$content = $e->getMessage();

// Module Errors (Mostly originating from the Front Controller)
} catch(Cx_Exception_Module $e) {
	$responseStatus = 404;
	$content = "Oops! The file, module, or method you were looking for is missing or could not be not found. Please check the URL and try again.";
	$content = $e->getMessage();

// User Error
} catch(Cx_Exception_User $e) {
	$responseStatus = 405; // 405 - Method Not Allowed
	$content = $e->getMessage();
	
// Module/Action Error
} catch(Cx_Exception $e) {
	$responseStatus = 500;
	$content = $e->getMessage();

// Generic Error
} catch(Exception $e) {
	$responseStatus = 500;
	$content = $e->getMessage();
}

// Error handling through core error module
if($app && $response) {
	if($responseStatus != 200) {
		try {
			$content = $app->dispatch('error', 'indexAction', array($responseStatus, $content));
		} catch(Exception $e) {
			$content = $e->getMessage();
		}
	}

	// Set content and send response
	$response->setStatus($responseStatus);
	$response->setContent($content);
	$response->send();
	
	if($cx) {
		$cx->event('shutdown');
	}
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}