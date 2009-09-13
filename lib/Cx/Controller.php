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
	 * Executes immediately BEFORE each action call
	 */
	public function beforeDispatch()
	{
		
	}
	
	
	/**
	 * Executes immediately AFTER each action call
	 *
	 * @param mixed $result Result from dispatch action call - can be controller object or returned result
	 * @return mixed Can be a string or an object with __toString() method defined
	 */
	public function afterDispatch($result)
	{
		if($this->autoRender) {
			$this->render();
		}
		return $result;
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
		// Turn off auto-rendering - we are already rendering right now
		$this->autoRender = false;
		// Return template contents
		return $this->view()->getContent();
	}
	
	
	/**
	 * Get model class to use for module
	 */
	public function model($name = null)
	{
		// Get model for current module
		if($name === null) {
			// Model name
			$modelName = 'Module_' . $this->name() . '_Model';
			// Instantiate model - first time used
			if($this->model === null) {
				$this->model = $this->cx->get($modelName);
				if(is_object($this->model) && $model instanceof phpDataMapper_Model) {
					$this->model->setLoader($this->cx, 'load');
				}
			}
			$model = $this->model;
		
		// Get model by given name
		} else {
			// Model name 'Page'
			$modelName = 'Module_' . $name . '_Model';
			$model = $this->cx->get($modelName);
			if(is_object($model) && $model instanceof phpDataMapper_Model) {
				$model->setLoader($this->cx, 'load');
			}
		}
		
		return $model;
	}
	
	
	/**
	 * Get view object
	 *
	 * @return object Cx_View
	 */
	public function view()
	{
		if($this->view === null) {
			$this->view = new $this->viewClass($this->action(), $this->name());
			
			// Set view template path
			$this->view->setPath($this->path() . $this->namePath() . '/views/');
			
			// Give the view access to session and request vars
			$this->view->set('request', $this->getRequest());
		}
		$view = $this->view;
		
		return $view;
	}
	
	
	/**
	 * Get request object
	 *
	 * @return object Cx_Request
	 */
	public function request($key = null, $value = null)
	{
		$object = $this->cx->request();
		if($key === null) {
			return $object;
		} else {
			return $object->get($key, $value);
		}
	}
	
	
	/**
	 * Get response object
	 *
	 * @return object Cx_Response
	 */
	public function response()
	{
		return $this->cx->response();
	}
	
	
	/**
	 * Get session object
	 *
	 * @return object Cx_Session
	 */
	public function session()
	{
		return $this->cx->session();
	}
	
	
	/**
	 * Getter/setter
	 * Current action that was run
	 *
	 * @return string or null
	 */
	public function action($action = null)
	{
		if($action === null) {
			return $this->action;
		} else {
			$this->action = $action;
		}
	}
	
	
	/**
	 * Returns current module name
	 * Assumes standard module naming convention
	 * Will return full class name if name cannot be determined by conventions
	 * 
	 * @return string
	 */
	public function name()
	{
		if(!$this->name) {
			$className = get_class($this);
			$nameParts = explode('_', $className);
			if(isset($nameParts[1])) {
				$this->name = $nameParts[1];
			} else {
				$this->name = $className;
			}
		}
		return $this->name;
	}
	
	
	/**
	 * Returns module name transformed into a directory
	 * Relies on naming conventions - replaces underscores with directory separators
	 */
	public function namePath()
	{
		return str_replace('_', DIRECTORY_SEPARATOR, $this->name());
	}
	
	
	/**
	 * Returns guess at current module path
	 */
	public function path()
	{
		$path = $this->cx->config('cx.path_core') . $this->cx->config('cx.dir_modules') . $this->getNamePath();
		return $path;
	}
	
	
	/**
	 * Return template contents as string
	 */
	public function __toString()
	{
		$content = '';
		if($this->autoRender) {
			// Exceptions cannot be thrown in __toString method (results in fatal error)
			// We have to catch any that may be thrown and return a string
			try {
				$content = $this->render();
			} catch(Cx_Exception $e) {
				$content = $e->getError();
			} catch(Exception $e) {
				$content = "<strong>TEMPLATE RENDERING ERROR:</strong><hr />" . $e->getMessage();
			}
		}
		return $content;
	}
}