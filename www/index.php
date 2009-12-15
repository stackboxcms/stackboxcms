<?php
// Require common application file
define('APP_WEB_ROOT', dirname(__FILE__));
require('../app/common.php');

// Configuration settings
$cfg = require('../app/config.php');

// Run!
$cx = false;
try {
	$cx = cx($cfg);
	// Enable debug mode if set
	if($cx->config('debug')) {
		$cx->debug(true);
	}
	$request = $cx->request();
	$router = $cx->router();
	spl_autoload_register(array($cx, 'load'));
	 
	// Router - Add routes we want to match
	$router->route('(*url)', array('module' => 'Page', 'action' => 'index', 'format' => 'html'));
	$router->route('(*url).(:format)', array('module' => 'Page', 'action' => 'index'));
	
	// Router - Match HTTP request and return named params
	$requestUrl = isset($_GET['r']) ? $_GET['r'] : '/';
	$params = $router->match($requestUrl);
	$request->setParams($params);
	
	// Required params
	$module = $params['module'];
	$action = $params['action'] . 'Action'; // Append with 'Action' to limit scope of available functions from HTTP request
	
	// Run/execute
	$responseStatus = 200;
	$content = $cx->dispatch($module, $action);
 
// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e->getMessage();
	$cx->response($responseStatus);
	$cx->dispatch('user', 'loginAction');
 
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
/*
if($cx && $content) {
	if($responseStatus != 200) {
		try {
			$content = $cx->dispatch('error', 'index', array($responseStatus, $content));
		} catch(Exception $e) {
			$content = $e->getMessage();
		}
	}
}
*/

// Send proper response
if($cx) {
	// Set content and send response
	$cx->response($responseStatus);
	echo $content;
	
	// Debugging on?
	if($cx->config('debug')) {
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