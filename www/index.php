<?php
// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run this application!");
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
	$cx->trigger('cx_router_before', array($router));
	$router->route('(*url)', array('module' => 'Page', 'action' => 'index', 'format' => 'html'));
	$router->route('(*url).(:format)', array('module' => 'Page', 'action' => 'index'));
	$cx->trigger('cx_router_after', array($router));
	
	// Router - Match HTTP request and return named params
	$requestUrl = isset($_GET['r']) ? $_GET['r'] : '/';
	$params = $router->match($requestUrl);
	// Set matched params back on request object
	$request = $cx->request();
	$request->setParams($params);
	
	// Required params
	$module = $params['module'];
	$action = $params['action'] . 'Action'; // Append with 'Action' to limit scope of available functions from HTTP request
	
	// Set class load paths
	$cx->addLoadPath($cx->config('cx.path_lib'));
	// Module paths
	$cx->addModulePath($cx->config('cx.path_core_modules'));
	$cx->addModulePath($cx->config('cx.path_modules'));
	
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
/*
if($cx && $content) {
	if($responseStatus != 200) {
		try {
			$content = $cx->dispatch('error', 'indexAction', array($responseStatus, $content));
		} catch(Exception $e) {
			$content = $e->getMessage();
		}
	}
}
*/

// Send proper response
if($cx) {
	$cx->trigger('cx_boot_render_before', array(&$responseStatus, &$content));
	
	// Set content and send response
	$cx->response($responseStatus);
	echo $content;
	
	$cx->trigger('cx_boot_render_after', array(&$responseStatus));
	
	// Debugging on?
	if($cx->config('cx.debug')) {
		echo "<hr />";
		echo "<pre>";
		print_r($cx->trace());
		echo "</pre>";	
	}
	
	$cx->trigger('cx_shutdown');
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}