<?php
// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) { exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run this application!"); }

// Configuration File
set_include_path('.');
$cfg = require 'config.php';
require 'functions.php';
require $cfg['cx']['path_lib'] . '/Cx/Locator.php';

// Get CX Locator root object
$cx = cx();
// Add path for library files
$cx->addPath($cx->config('cx.path_lib'));
// Add include paths for modules
$cx->addPath($cx->config('cx.root') . $cx->config('cx.dir_core') . $cx->config('cx.dir_modules')); // core modules
$cx->addPath($cx->config('cx.path_modules')); // cms modules
	
// PHP Error Level
if($cx->config('cx.error_reporting')) {
	// Show all errors
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', '1');
} else {
	// Show NO errors
	error_reporting(0);
	ini_set('display_errors', '0');
}

// Set default timezone based on config
date_default_timezone_set($cx->config('cx.i18n.timezone', 'GMT'));

// Set include path to current base directory
set_include_path($cx->config('cx.root'));

// Set session max lifetime
ini_set("session.gc_maxlifetime", "28800");


/**
 * Register the service locator in the SPL autoload stack
 */
// AUTOLOAD Function - Automatically includes required classes as needed (AKA lazy loading)
spl_autoload_register(array($cx, 'load'));