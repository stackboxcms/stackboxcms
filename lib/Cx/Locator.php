<?php
/**
 * Cx_Locator Class
 * $Id$
 *
 * Registry/Factory and class manager
 * Used as framework root for loading new objects
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Locator
{
	protected static $me;
	protected $instances = array();
	protected $loaded = array();
	protected $paths = array();
	
	protected $classMappings = array();
	protected $defaultPrefix = 'Cx_';
	//protected $prefixes = array('Cx_' => 'Cx_');
	
	
	/**
 	 *	Returns an instance of class itself
	 */
	public static function getInstance()
	{
		if(!is_object(self::$me)) {
			$className = __CLASS__;
			self::$me = new $className();
		}
		return self::$me;
	}
	
	
	/**
	 * Add path for loader to look in
	 */
	public function addPath($path)
	{
		$this->paths[] = $path;
	}
	
	
	/**
	 * Return array of set module paths
	 */
	public function getPaths()
	{
		return $this->paths;
	}
	
	/**
	 * Return configuration value
	 * 
	 * @param string $value Value key to search for
	 * @param string $default Default value to return if $value not found
	 */
	public function config($value = null, $default = false) {
		global $cfg; // Ew. I know. I know. Fear not, it's only temporary. :)
		
		// No value passed - return entire config array
		if($value === null) { return $cfg; }
		
		// Find value to return
		if(strpos($value, '.') !== false) {
			$cfgValue = $cfg;
			$valueParts = explode('.', $value);
			foreach($valueParts as $valuePart) {
				if(isset($cfgValue[$valuePart])) {
					$cfgValue = $cfgValue[$valuePart];
				} else {
					$cfgValue = $default;
				}
			}
		} else {
			$cfgValue = $cfg;
			if(isset($cfgValue[$value])) {
				$cfgValue = $cfgValue[$value];
			} else {
				$cfgValue = $default;
			}
		}
		return $cfgValue;
	}
	
	
	/**
	 * Factory method for loading and instantiating new objects
	 */
	public function get($className, array $args = array())
	{
		$paths = $this->paths;
		
		if(isset($this->instances[$className])) {
			return $this->instances[$className];
		}
		
		// Check custom class mappings for override
		if(isset($this->classMappings[$className])) {
			$className = $this->classMappings[$className]['class'];
			$paths[] = $this->classMappings[$className]['path'];
		} else {
			$className = $className;
		}
		
		// Ensure it was loaded
		if(!$this->load($className, $paths)) {
			throw new Cx_Exception("Unable to load requested class '" . $className . "'");
		}
		
		// Return new class instance
		if(count($args) == 0) {
			$instance = new $className();
		} else {
			$class = new ReflectionClass($className);
			$instance = $class->newInstanceArgs($args);
		}
		
		return $this->set($className, $instance);
	}
	
	
	/**
	 *	Sets instance of specified class
	 *	Note: This function does not check if $class is currently in use or already instantiated.
	 *		This will override any previous instances of $class within the $instances array.
	 *
	 *	@param $class string	Name of the class you wish to locate
	 *	@param $instance object	Instance of class you wish to set in locator
	 *	@return object
	 */
	public function set($class, $instance)
	{
		$this->instances[$class] = $instance;
		return $instance;
	}
	
	
	/**
	 * Load (include) class file
	 *
	 * @param $className string	Name of the class
	 * @param $path string	Path to begin looking in for class file
	 * @return boolean
	 */
	public function load($className, $paths = null)
	{
		// Ensure class is not already loaded
		if(class_exists($className, false)) {
			return true;
		}
		
		// Is $paths null?
		if($paths === null) {
			$paths = $this->getPaths();
			//$className = str_replace('_', '/', $className);
			//$paths[] = $className;
		}
		
		// MODULE loading
		if(strpos($className, 'Module_') !== false) {
			// Explode class name to get parts
			$classNameParts = explode("_", $className);
			
			// Get module name (2nd part)
			$moduleName = array_shift($classNameParts);
			
			// Put class name back together without prefix or module name
			$className = implode("_", $classNameParts);
			
			// Add current module name to each module base path
			/*
			$modulePaths = array();
			if(is_array($paths) && count($paths) > 0) {
				foreach($paths as $path) {
					$modulePaths[] = $path . $moduleName . DIRECTORY_SEPARATOR;
				}
				$paths = $modulePaths;
			}
			*/
		}
		
		// Add custom set include paths
		$incPath = false;
		if(is_array($paths) && count($paths) > 0) {
			// Filter out duplicate paths
			$paths = array_unique($paths);
			// Change include path to have all possible paths in it
			$dirs = implode(PATH_SEPARATOR, array_reverse($paths));
			$incPath = get_include_path();
			set_include_path($dirs . PATH_SEPARATOR . $incPath);
		}
		
		// Put together the full class location (include path)
		$className = str_replace('_', '/', $className);
		$classLocation = $className . '.php';
		
		// Include class file if it exists
		// Set cutom error handler to surpress inclusion failure warnings
		set_error_handler(array($this, 'throwError'));
		try {
			// Warning: The '@' before the include will surpress parse errors
			$included = include_once($classLocation);
		} catch(Exception $e) {
			$included = false;
		}
		// Restore previous error handler
		restore_error_handler();
		
		// Reset include path with original
		if($incPath) {
			set_include_path($incPath);
		}
		
		// Return result
		if($included !== false) {
			$this->loaded[] = $className;
			return true;
		} else {
			return false;
		}
	}
	

	/**
	 * Run hooks for specified event name
	 */
	public function event($name)
	{
		// @todo Run hooks for event name
	}
	
	
	/**
	 * Log message
	 */
	public function log($message)
	{
		// @todo Record log message
	}
	
	
	/**
	 * Custom error-handling function for failed file include error surpression
	 */
	public function throwError($errno, $errstr, $errfile, $errline)
	{
		$msg = "";
		switch ($errno) {
			case E_USER_ERROR:
			case E_USER_WARNING:
				$msg .= "<b>ERROR</b> [$errno] $errstr<br />\n";
				$msg .= "  Fatal error on line $errline in file $errfile";
				$msg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				$msg .= "Aborting...<br />\n";
				throw new Cx_Exception($msg);
				//exit(1);
			break;
				
			case E_USER_NOTICE:
			default:
		}
		
	    // Don't execute PHP internal error handler
	    return true;
	}
	
	
	/**
	 * Get request object
	 */
	public function request()
	{
		return $this->get('Cx_Request');
	}
	
	
	/**
	 * Get response object
	 */
	public function response()
	{
		return $this->get('Cx_Response');
	}
	
	
	/**
	 * Get session object
	 */
	public function session()
	{
		return $this->get('Cx_Session');
	}
}