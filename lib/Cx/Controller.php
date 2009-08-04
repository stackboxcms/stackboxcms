<?php
/*
 * Base Controller Class
 * $Id$
 * 
 * Base controller class for other component controllers to extend
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Controller
{
	// Automatically render the page after action call
	protected $autoRender = true;

	// Model Settings
	protected $model = null;
	
	// View Settings
	protected $viewClass = "Cx_View";
	protected $view = null;
	
	// Other required variables
	protected $name = null;
	protected $action = null;
	protected $module = null;
	
	// Context object (Front Controller)
	protected $context;
	protected $cx;
	
	
	/**
	 *	Constructor Method
	 */
	public function __construct($cx)
	{
		// Save loader
		$this->cx = $cx;
	}
	
	
	/**
	 *	Initialization function
	 */
	public function init()
	{
		// Initialize me!
	}
	
	
	/**
	 * Pre-Dispatch
	 * Executes immediately BEFORE each action call
	 */
	public function preDispatch()
	{
		
	}
	
	
	/**
	 * Post-Dispatch
	 * Executes immediately AFTER each action call
	 */
	public function postDispatch()
	{
		if($this->autoRender) {
			$this->render();
		}
	}
	
	
	/**
	 *	Forward processing to a different action
	 */
	public function forward($component, $action, array $params = array())
	{
		$controller = false;
		
		// Use passed controller object or get instance from given controller name
		if(!is_subclass_of($component, 'Cx_Controller')) {
			$controller = $this->cx->getController($component);
		}

		if($controller) {
			// Update $action to new one
			$controller->setAction($action);
			
			// Call function if we can
			if(is_callable(array($controller, $action))) {
				return call_user_func_array(array($controller, $action), $params);
			} else {
				throw new Cx_Exception('Attempting to forward execution to method that does not exist!<br /> Attempted: ' . $controller . '::' . $action);
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Begin output to browser window
	 */
	public function render()
	{
		$request = $this->getRequest();
		return $this->getView()->getContent();
	}
	
	
	/**
	 * Get model class to use for module
	 */
	public function getModel($name = null)
	{
		// Get model for current module
		if($this->useModel && $name === null) {
			// Instantiate model - first time used
			if($this->model === null) {
				$this->model = $this->cx->getModel($this->getName());
				if(is_object($this->model)) {
					$this->model->setLoader($this->cx, 'load');
				}
			}
			$model = $this->model;
		
		// Get model by given name
		} elseif($name !== null) {
			$model = $this->cx->getModel($name);
			if(is_object($model)) {
				$model->setLoader($this->cx, 'load');
			}
		
		// No model
		} else {
			$model = false;
		}
		
		return $model;
	}
	
	
	/**
	 * Get view object
	 *
	 * @return object Cx_View
	 */
	public function getView()
	{
		if($this->view === null) {
			$this->view = new $this->viewClass($this->getAction(), $this->getName());
			
			// Set view template path
			$this->view->setPath($this->getPath() . '/views/');
			
			// Give the view access to session and request vars
			$this->view->set('request', $this->getRequest());
		}
		$view = $this->view;
		
		return $view;
	}
	
	
	/**
	 * Is current user authenticated to execute command module?
	 * 
	 * @todo This function will be the way to check user permissions, etc.
	 * @return boolean
	 */
	public function canRun($action)
	{
		return true;
	}
	
	
	/**
	 * Get context object
	 *
	 * @return object Cx_Controller_Front
	 */
	public function getContext()
	{
		return $this->context;
	}
	
	
	/**
	 * Set context object
	 *
	 * @param object Cx_Controller_Front
	 */
	public function setContext(Cx_Controller_Front $context)
	{
		$this->context = $context;
	}
	
	
	/**
	 * Get request object
	 *
	 * @return object Cx_Http_Request
	 */
	public function getRequest()
	{
		return $this->getContext()->getRequest();
	}
	
	
	/**
	 * Get response object
	 *
	 * @return object Cx_Http_Response
	 */
	public function getResponse()
	{
		return $this->getContext()->getResponse();
	}
	
	
	/**
	 * Get session object
	 *
	 * @return object Cx_Session
	 */
	public function getSession()
	{
		return $this->getContext()->getSession();
	}
	
	
	/**
	 * Returns current action
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}
	
	
	/**
	 * Sets current action
	 *
	 * @param string Name of the action being executed on the current module
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}
	
	
	/**
	 * Returns current module name
	 * relies on module naming conventions
	 * 
	 * @return string
	 */
	public function getName()
	{
		if(!$this->name) {
			// Removes last 10 characters from class name - "Controller"
			$this->name = substr(get_class($this), 0, -10);
		}
		return $this->name;
	}
	
	
	/**
	 * Returns module name transformed into a directory
	 * Relies on naming conventions - replaces underscores with directory separators
	 */
	public function getNamePath()
	{
		return str_replace('_', DIRECTORY_SEPARATOR, strtolower($this->getName()));
	}
	
	
	/**
	 * Returns guess at current module path
	 */
	public function getPath()
	{
		$path = $this->cx->config('cx.path_core') . $this->cx->config('cx.dir_modules') . '/' . $this->getNamePath();
		return $path;
	}
	
	
	/**
	 * Return template contents as string
	 */
	public function __toString()
	{
		// Exceptions cannot be thrown in __toString method (results in fatal error)
		// We have to catch any that may be thrown and return a string
		try {
			$content = $this->render();
		} catch(Cx_Exception $e) {
			$content = $e->getError();
		} catch(Exception $e) {
			$content = "<strong>TEMPLATE RENDERING ERROR:</strong><hr />" . $e->getMessage();
		}
		return $content;
	}
}