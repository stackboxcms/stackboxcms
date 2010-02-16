<?php
// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run in Cont-xt.");
}

// Configuration settings
$cfg = require('../app/config.php');

// Cont-xt Kernel
require $cfg['cx']['path_lib'] . '/Cx.php';

// Run!
$cx = false;
try {
	$cx = cx($cfg);
	spl_autoload_register(array($cx, 'load'));
	set_error_handler(array($cx, 'errorHandler'));
	
	// Debug?
	if($cx->config('cx.debug')) {
		// Enable debug mode
		$cx->debug(true);
		
		// Show all errors
		error_reporting(E_ALL | E_STRICT);
		ini_set('display_errors', '1');
	} else {
		// Show NO errors
		//error_reporting(0);
		//ini_set('display_errors', '0');
	}
	
	$cx->trigger('cx_boot');
	
	// Router - Add routes we want to match
	$router = $cx->router();
	$cx->trigger('cx_boot_router_before', array($router));
	
	$router->route('module_item', '<*url>/<:module_name>_<#module_id>/<#module_item>(/<:module_action>)(.<:format>)')
		->defaults(array('url' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'view', 'format' => 'html'))
		->get(array('module_action' => 'view'))
		->post(array('module_action' => 'post'))
		->put(array('module_action' => 'put'))
		->delete(array('module_action' => 'delete'));
	$router->route('module_action', '<*url>/<:module_name>_<#module_id>/<:module_action>(.<:format>)')
		->defaults(array('url' => '/', 'module' => 'Page', 'action' => 'index', 'format' => 'html'));
	$router->route('index_action', '<:action>\.<:format>')
		->defaults(array('module' => 'Page', 'format' => 'html', 'url' => '/'));
	$router->route('page_action', '<*url>/<:action>(.<:format>)')
		->defaults(array('module' => 'Page', 'format' => 'html'));
	$router->route('page', '<*url>')
		->defaults(array('module' => 'Page', 'action' => 'index', 'format' => 'html'))
		->post(array('action' => 'post'))
		->put(array('action' => 'put'))
		->delete(array('action' => 'delete'));
		
	$cx->trigger('cx_boot_router_after', array($router));
	
	// Router - Match HTTP request and return named params
	$request = $cx->request();
	$requestUrl = isset($_GET['r']) ? $_GET['r'] : '/';
	$requestMethod = $request->method();
	// Emulate REST for browsers
	if($request->isPost() && $request->post('_method')) {
		$requestMethod = $request->post('_method');
	}
	$params = $router->match($requestMethod, $requestUrl);
	// Set matched params back on request object
	$request->setParams($params);
	$request->route = $router->matchedRoute()->name();
	
	// Required params
	$module = $params['module'];
	if(strtolower($requestMethod) == strtolower($params['action'])) {
		$action = $params['action'] . 'Method'; // Append with 'Method' to limit scope to REST access only
	} else {
		$action = $params['action'] . 'Action'; // Append with 'Action' to limit scope of available functions from HTTP request
	}
	
	// Set class load paths - works for all classes using the PEAR/Zend naming convention placed in 'lib'
	$cx->addLoadPath($cx->config('cx.path_lib'));
	// Module paths
	$cx->addLoadPath($cx->config('cx.path_modules'), 'Module_');
	
	// Run/execute
	$responseStatus = 200;
	$content = "";
	
	$cx->trigger('cx_boot_dispatch_before', array(&$responseStatus, &$content));
	
	$content .= $cx->dispatch($module, $action, array($request));
	
	$cx->trigger('cx_boot_dispatch_after', array(&$responseStatus, &$content));
 
// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e->getMessage();
	$cx->response($responseStatus);
	$cx->dispatch('user', 'loginAction', array($request));
 
// 404 Errors
} catch(Cx_Exception_FileNotFound $e) {
	$responseStatus = 404;
	$content = $e->getMessage();

// Method Not Allowed
} catch(Cx_Exception_Method $e) {
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
if($cx && $content && $responseStatus >= 400) {
	try {
		$content = $cx->dispatch('Error', 'display', array($responseStatus, $content));
	} catch(Exception $e) {
		$content = $e->getMessage();
	}
}

// Send proper response
if($cx) {
	$cx->trigger('cx_response_before', array(&$responseStatus, &$content));
	
	// Set content and send response
	$cx->response($responseStatus);
	echo $content;
	
	$cx->trigger('cx_response_after', array(&$responseStatus));
	$cx->trigger('cx_shutdown');
	
	// Debugging on?
	if($cx->config('cx.debug')) {
		echo "<hr />";
		echo "<pre>";
		print_r($cx->trace());
		echo "</pre>";	
	}
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}