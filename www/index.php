<?php
// Show all errors by default
error_reporting(-1);
ini_set('display_errors', '1');

// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run in Cont-xt.");
}

// Configuration settings
$cfg = require('../app/config.php');

// Cont-xt Kernel
require $cfg['cx']['path_lib'] . '/Cx/Kernel.php';

// Run!
$kernel = false;
try {
	$kernel = cx($cfg);
	spl_autoload_register(array($kernel, 'load'));
	set_error_handler(array($kernel, 'errorHandler'));
	
	// Debug?
	if($kernel->config('cx.debug')) {
		// Enable debug mode
		$kernel->debug(true);
		
		// Show all errors
		error_reporting(-1);
		ini_set('display_errors', '1');
	} else {
		// Show NO errors
		//error_reporting(0);
		//ini_set('display_errors', '0');
	}
	
	$kernel->trigger('cx_boot');
	
	// Router - Add routes we want to match
	$router = $kernel->router();
	$kernel->trigger('cx_boot_router_before', array($router));
	
	// HTTP Errors
	$router->route('http_error', 'error/<#errorCode>(.<:format>)')
		->defaults(array('module' => 'Error', 'action' => 'display', 'format' => 'html', 'url' => '/'));
	
	// User reserved route
	$router->route('user', 'user/<:action>')
		->defaults(array('module' => 'User', 'action' => 'index', 'format' => 'html'));
	
	// Normal Routes
	$router->route('module', '<*url>/<:module_name>_<#module_id>.<:format>')
		->defaults(array('url' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'index', 'format' => 'html'))
		->get(array('module_action' => 'view'))
		->post(array('module_action' => 'post'))
		->put(array('module_action' => 'put'))
		->delete(array('module_action' => 'delete'));
		
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
		
	$kernel->trigger('cx_boot_router_after', array($router));
	
	// Router - Match HTTP request and return named params
	$request = $kernel->request();
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
	$action = $params['action'];
	
	// Set class load paths - works for all classes using the PEAR/Zend naming convention placed in 'lib'
	$kernel->addLoadPath($kernel->config('cx.path_lib'));
	// Module paths
	$kernel->addLoadPath($kernel->config('cx.path_modules'), 'Module_');
	
	// Run/execute
	$responseStatus = 200;
	$response = $kernel->response($responseStatus);
	$content = "";
	
	$kernel->trigger('cx_boot_dispatch_before', array(&$content));
	
	$content .= $kernel->dispatchRequest($request, $module, $action, array($request));
	
	$kernel->trigger('cx_boot_dispatch_after', array(&$content));
 
// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e->getMessage();
	$kernel->response($responseStatus);
	$kernel->dispatch('user', 'loginAction', array($request));
 
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
if($kernel && $content && $responseStatus >= 400) {
	try {
		$content = $kernel->dispatch('Error', 'displayAction', array($request, $responseStatus, $content));
	} catch(Exception $e) {
		$content = $e->getMessage();
	}
}

// Send proper response
if($kernel) {
	$kernel->trigger('cx_response_before', array(&$content));
	
	// Set content and send response
	$response->content($content);
	$response->send();
	
	$kernel->trigger('cx_response_after');
	$kernel->trigger('cx_shutdown');
	
	// Debugging on?
	if($kernel->config('cx.debug')) {
		echo "<hr />";
		echo "<pre>";
		print_r($kernel->trace());
		echo "</pre>";	
	}
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}