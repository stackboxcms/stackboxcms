<?php
/*
 * Front Controller Class
 * $Id$
 * 
 * Main front controller for handling and dispatching requests
 * 
 * @package Cx Framework
 * @link http://cont-xt.com/
 */
class Cx_Controller_Front
{
	protected $module;
	protected $action;
	protected $params = array();
	
	protected $cx;
	protected $request;
	protected $response;
	
	
	/**
	 *	Constructor fucntion
	 */
	public function __construct($cx)
	{
		$this->cx = $cx;
	}
	
	
	/**
	 *	Getters/Setters
	 */
	// module
	public function setModule($str)
	{
		$this->module = $str;
	}
	public function getModule()
	{
		return $this->module;
	}
	
	// action
	public function setAction($str)
	{
		$this->action = $str;
	}
	public function getAction()
	{
		return $this->action;
	}
	
	// parameters for the function call
	public function setParam($name, $value)
	{
		$this->params[$name] = $value;
	}
	public function setParams(array $params = array())
	{
		$this->params = array_merge($this->params, $params);
	}
	public function getParams()
	{
		return $this->params;
	}
	
	
	/**
	 *	Invoke application
	 */
	public function run(Cx_Request $request, Cx_Response $response)
	{
		// Store request & response
		$this->request = $request;
		$this->response = $response;
		
		// Get common variables
		$module = $this->getModule();
		$action = $this->getAction();
		
		// Append 'Action' to URL
		$action .= "Action";
		
		// Dispatch module/action call
		return $this->dispatch($module, $action);
	}
	
	
	/**
	 * Dispatch module action
	 * 
	 * @param string $moduleName Name of module to be called
	 * @param optional string $action function name to call on module
	 * @param optional array $params parameters to pass to module function
	 *
	 * @return object Cx_Controller
	 */
	public function dispatch($moduleName, $action = 'index', array $params = array())
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		// Determine module name and load class file
		$moduleClassName = 'Module_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $moduleName))) . '_Controller';
	
		$return = true;
		$moduleClass = null;
		
		// Load the module file (Locator will prevent double-loading)
		$loaded = $this->cx->loadInModule($moduleClassName, $moduleName);
		if($loaded) {
			// Ensure class actually exists
			if(class_exists($moduleClassName, false)) {
				// New module controller instance with dependencies
				$moduleClass = new $moduleClassName($this->cx);
			} else {
				throw new Cx_Exception_Module("Unable to load module '" . $moduleName . "': Class '" . $moduleClassName . "' has not been defined.");
			}
		} else {
			throw new Cx_Exception_Module("Unable to load module '" . $moduleName . "': Module file '" . $moduleClassName . "' not found.");
		}
		
		// Ensure module was found
		if($moduleClass) {
			$moduleClass->setAction($action);
			$moduleClass->setContext($this);
			
			// Save instance in locator
			$this->cx->set($moduleClassName, $moduleClass);
			
			// Initialize function
			$moduleClass->init();
			
			// Are we able to call module function?
			if(is_callable(array($moduleClass, $action))) {
				$callable = true;
				$filterAction = $action;
				
				// If this is callable with __call instead of action name
				if(!method_exists($moduleClass, $action)) {
					// Add action name as the first parameter for expected behavior
					//$params = array($action) + $params;
					
					// Filter action will be '__call', the actual function name
					$filterAction = '__call';
				}
			} else {
				$callable = false;
			}
			
			// Call actual module function
			if($callable) {
				// If module is authenticated to run
				// @todo Add check for user authentication - Is user allowed to execute this function?
				if(true) {
					$moduleClass->beforeDispatch();
					// Call requested module action
					if(count($params) > 0) {
						$return = call_user_func_array(array($moduleClass, $action), $params);
					} else {
						$return = $moduleClass->$action();
					}
					$return = $moduleClass->afterDispatch($return);
				} else {
					// Unauthorized user
					throw new Cx_Exception_Auth("Please log in to access this page");
				}
			} else {
				throw new Cx_Exception_Module($moduleClassName . "::" . $action . " failed:<br />\nModule '" . $moduleName . "' does not have a callable method named '" . $action . "'.");
			}
		} else {
			throw new Cx_Exception_Module("Unable to load module '" . $moduleName . "' - Module not found.");
		}
		
		// Return class instance if there is no other content returned by the function itself
		if(null === $return) {
			$return = $moduleClass;
		}
		return $return;
	}
	
	
	/**
	 * Get request object
	 *
	 * @return object Cx_Request
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	
	/**
	 * Get response object
	 *
	 * @return object Cx_Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}