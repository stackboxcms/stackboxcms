<?php
/**
 * Cx Kernel
 *
 * Application kernel with custom methods added
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 */
class Cx extends Kernel_App
{
	public function dispatch($module, $action = 'indexAction', array $params = array())
	{
		// Clean module name to prevent possible security vulnerabilities
		$sModule = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
		$sModuleClass = $sModule . '_Module';
		// Replace underscores with folder slashes
		$sModule = str_replace('_', '/', $sModule);
		
		// Load module file, call function on it
		$path = dirname(dirname(__FILE__)) . '/modules/';
		$loaded = $this->load($sModuleClass, $path);
		if(!$loaded) {
			throw new Cx_Exception_FileNotFound("Requested module '" . $sModule . "' not found");	
		}
		
		// Instantiate class and call method
		$sModuleObject = new $sModuleClass($this);
		
		// Run init() setup only if supported
		if(is_callable(array($sModuleObject, 'init'))) {
			$sModuleObject->init();
		}
		
		// Run module action
		if(!is_callable(array($sModuleObject, $action))) {
			throw new Cx_Exception_FileNotFound("Module '" . $sModule ."' does not have a callable method '" . $action . "'");
		}
		
		// Handle result
		$result = $sModuleObject->$action();
		return $result;
	}
	
}

/**
 * Custom function to ensure only one kernel instance
 */
function cx(array $cfg = array()) {
	$cx = Cx::getInstance($config);
	return $cx;
}