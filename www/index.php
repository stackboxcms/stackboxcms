<?php
// Show all errors by default
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run in Cont-xt.");
}

// Configuration settings
$cfg = require(dirname(dirname(__FILE__)) . '/app/config/app.php');

// Cont-xt Kernel
require $cfg['path']['lib'] . '/Alloy/Kernel.php';

// Run!
$kernel = false;
try {
	$kernel = Alloy($cfg);
	spl_autoload_register(array($kernel, 'load'));
	set_error_handler(array($kernel, 'errorHandler'));
	ini_set("session.cookie_httponly", true); // Mitigate XSS javascript cookie attacks for browers that support it
	ini_set("session.use_only_cookies", true); // Don't allow session_id in URLs
	session_start();
	
	// Host-based config file for overriding default settings in different environments
	$cfgHostFile = dirname(dirname(__FILE__)) . '/app/config.' . strtolower(php_uname('n')) . '.php';
	if(file_exists($cfgHostFile)) {
		$cfgHost = require($cfgHostFile);
		$cfg = $kernel->config($cfgHost);
	}
	
	// Debug?
	if($kernel->config('debug')) {
		// Enable debug mode
		$kernel->debug(true);
		
		// Show all errors
		error_reporting(-1);
		ini_set('display_errors', 'On');
	} else {
		// Show NO errors
		//error_reporting(0);
		//ini_set('display_errors', 'Off');
	}
	
	// Host-based config file for overriding default settings in different environments
	$cfgHostFile = dirname(dirname(__FILE__)) . '/app/config.' . strtolower(php_uname('n')) . '.php';
	if(file_exists($cfgHostFile)) {
		$cfgHost = require($cfgHostFile);
		$kernel->config($cfgHost);
	}
	
	// Global setup based on config settings
	date_default_timezone_set($kernel->config('i18n.timezone'));
	ini_set("session.gc_maxlifetime", $kernel->config('session.lifetime'));
	
	$kernel->trigger('cx_boot');
	
	// Initial response code (not sent to browser yet)
	$responseStatus = 200;
	$response = $kernel->response($responseStatus);
	
	// Router - Add routes we want to match
	$router = $kernel->router();
	$kernel->trigger('cx_boot_routes_before', array($router));
	require $kernel->config('path.config') . '/routes.php';
	$kernel->trigger('cx_boot_routes_after', array($router));
	
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
	$kernel->addLoadPath($kernel->config('path.lib'));
	// Module paths
	$kernel->addLoadPath($kernel->config('path.modules'), 'Module_');
	$kernel->addLoadPath($kernel->config('path.cx_modules'), 'Module_');
	
	// User - Custom user code for authentication
	// @todo Move this code to plugin or event hook instead of bootstrap file
	// ==================================
	$sessionKey = null;
	if(isset($_SESSION['user']['session'])) {
		$sessionKey = $_SESSION['user']['session'];
	}
	$user = $kernel->dispatch('User_Session', 'authenticate', array($sessionKey));
	if(!$user->isLoggedin() && isset($_SESSION['user']['session'])) {
		// Unset invalid user key
		unset($_SESSION['user']['session']);
	}
	// Set user object on Kernel so it is available everywhere
	$kernel->user($user);
	// ==================================
	
	//var_dump($kernel->user());
	
	// Run/execute
	$content = "";
	
	$kernel->trigger('cx_boot_dispatch_before', array(&$content));
	
	$content .= $kernel->dispatchRequest($request, $module, $action, array($request));
	
	$kernel->trigger('cx_boot_dispatch_after', array(&$content));
 
// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e;
	$kernel->dispatch('user', 'loginAction', array($request));
 
// 404 Errors
} catch(Alloy_Exception_FileNotFound $e) {
	$responseStatus = 404;
	$content = $e;

// Method Not Allowed
} catch(Alloy_Exception_Method $e) {
	$responseStatus = 405; // 405 - Method Not Allowed
	$content = $e;

// HTTP Exception
} catch(Alloy_Exception_Http $e) {
	$responseStatus = $e->getCode(); 
	$content = $e;

// Module/Action Error
} catch(Alloy_Exception $e) {
	$responseStatus = 500;
	$content = $e;
 
// Generic Error
} catch(Exception $e) {
	$responseStatus = 500;
	$content = $e;
}

/*
// Error handling through core error module
if($kernel && $content && $responseStatus >= 400 && !$request->isAjax()) {
	try {
		$content = $kernel->dispatch('Error', 'displayAction', array($request, $responseStatus, $content));
	} catch(Exception $e) {
		// Let original error go through and be displayed
	}
}
//*/

// Exception detail depending on mode
if($content instanceof Exception) {
	$content = "[ERROR] " . $e->getMessage();
	// Show debugging info?
	if($kernel->config('debug')) {
		$content .= "<p>File: " . $e->getFile() . " (" . $e->getLine() . ")</p>";
		$content .= "<pre>" . $e->getTraceAsString() . "</pre>";
	}
}

// Send proper response
if($kernel) {
	$kernel->trigger('cx_response_before', array(&$content));
	
	// Set content and send response
	if($responseStatus != 200) {
		$response->status($responseStatus);
	}
	$response->content($content);
	$response->send();
	
	$kernel->trigger('cx_response_after');
	$kernel->trigger('cx_shutdown');
	
	// Debugging on?
	if($kernel->config('debug')) {
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
