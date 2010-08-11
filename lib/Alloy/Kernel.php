<?php
require dirname(dirname(__FILE__)) . '/AppKernel/Main.php';
require dirname(__FILE__) . '/Exception/FileNotFound.php';
/**
 * Alloy Kernel
 * 
 * @package Alloy Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://alloyframework.com/
 */
class Alloy_Kernel extends AppKernel_Main
{
	protected $session;
	protected $binds = array();
	protected $_user = false;
	
	protected $_spotConfig;
	protected $_spotMapper = array();
	
	
	/**
 	 * Returns an instance of class itself
	 * 
	 * @param array $config Array of configuration settings to load
	 */
	public static function getInstance(array $config = array())
	{
		if(!is_object(self::$self)) {
			$className = __CLASS__;
			self::$self = new $className($config);
		} else {
			// Add new config settings if given
			if(is_array($config) && count($config) > 0) {
				self::$self->config($config);
			}
		}
		return self::$self;
	}
	
	
	/**
	 * Get/return user
	 *
	 * @param $className string Name of the class
	 * @return object
	 */
	public function user($user = null)
	{
		if(null !== $user) {
			$this->_user = $user;
		}
		return $this->_user;
	}
	
	
	/**
	 * Load and return instantiated module class
	 *
	 * @param string $className Name of the class
	 * @return object
	 * @throws Alloy_Exception_FileNotFound
	 */
	public function module($module)
	{
		// Clean module name to prevent possible security vulnerabilities
		$sModule = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
		
		// Upper-case beginning of each word
		$sModule = str_replace(' ', '_', ucwords(str_replace('_', ' ', $sModule)));
		$sModuleClass = 'Module_' . $sModule . '_Controller';
		
		// Replace underscores with folder slashes
		$sModule = str_replace('_', '/', $sModule);
		
		// Load module file
		$loaded = $this->load($sModuleClass);
		if(!$loaded) {
			throw new Alloy_Exception_FileNotFound("Module '" . $sModule . "' not found (class " . $sModuleClass . ")");
		}
		
		// Instantiate module class
		$sModuleObject = new $sModuleClass($this);
		
		// Run init() setup only if supported
		if(method_exists($sModuleObject, 'init')) {
			$sModuleObject->init();
		}
		
		return $sModuleObject;
	}
	
	
	/**
	 * Get mapper object to work with
	 * Ensures only one instance of a mapper gets loaded
	 *
	 * @param string $mapperName (Optional) Custom mapper class to load in case of custom requirements or queries
	 */
	public function mapper($mapperName = 'Spot_Mapper')
	{
		if(!isset($this->_spotMapper[$mapperName])) {
			// Create new mapper, passing in config
			$this->_spotMapper[$mapperName] = new $mapperName($this->spotConfig());
		}
		return $this->_spotMapper[$mapperName];
	}
	
	
	/**
	 * Get instance of database connection
	 */
	public function spotConfig()
	{
		if(!$this->_spotConfig) {
			$dbCfg = $this->config('database');
			if($dbCfg) {
				// New config
				$this->_spotConfig = new Spot_Config();
				foreach($dbCfg as $name => $options) {
					$this->_spotConfig->addConnection($name, $options);
				}
			} else {
				throw new Exception("Unable to load configuration for Spot - Database configuration settings do not exist.");
			}
		}
		return $this->_spotConfig;
	}
	

	/**
	 * Dispatch module action
	 *
	 * @param string $moduleName Name of module to be called
	 * @param optional string $action function name to call on module
	 * @param optional array $params parameters to pass to module function
	 *
	 * @return mixed String or object that has __toString method
	 */
	public function dispatch($module, $action = 'index', array $params = array())
	{
		if($module instanceof Alloy_Module_Controller) {
			// Use current module instance
			$sModuleObject = $module;
		} else {
			// Get module instance
			$sModuleObject = $this->module($module);
		}
		
		// Module action callable (includes __call magic function if method missing)?
		if(!is_callable(array($sModuleObject, $action))) {
			throw new Alloy_Exception_FileNotFound("Module '" . $module ."' does not have a callable method '" . $action . "'");
		}
		
		try {
			// Handle result
			$paramCount = count($params);
			if($paramCount == 0) {
				$result = $sModuleObject->$action();
			} elseif($paramCount == 1) {
				$result = $sModuleObject->$action(current($params));
			} elseif($paramCount == 2) {
				$result = $sModuleObject->$action($params[0], $params[1]);
			} elseif($paramCount == 3) {
				$result = $sModuleObject->$action($params[0], $params[1], $params[2]);
			} else {
				$result = call_user_func_array(array($sModuleObject, $action), $params);
			}
		
		// Database table/datasource missing - attempt to autoinstall
		// @todo Move this elsewhere - it probably does not belong here 
		} catch(Spot_Exception_Datasource_Missing $e) {
			try {
				$result = $this->dispatch($module, 'install', array($action, $params));
			} catch(Exception $e) {
				$result = $e;
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Dispatch module action from HTTP request
	 * Automatically limits call scope by appending 'Action' or 'Method' to module actions
	 *
	 * @param AppKernel_Request $request
	 * @param string $moduleName Name of module to be called
	 * @param optional string $action function name to call on module
	 * @param optional array $params parameters to pass to module function
	 *
	 * @return mixed String or object that has __toString method
	 */
	public function dispatchRequest($request, $module, $action = 'indexAction', array $params = array())
	{
		$requestMethod = $request->method();
		// Emulate REST for browsers
		if($request->isPost() && $request->post('_method')) {
			$requestMethod = $request->post('_method');
		}
		
		// Append 'Action' or 'Method'
		if(strtolower($requestMethod) == strtolower($action)) {
			$action = $action . 'Method'; // Append with 'Method' to limit scope to REST access only
		} else {
			$action = $action . 'Action'; // Append with 'Action' to limit scope of available functions from HTTP request
		}
		
		
		return $this->dispatch($module, $action, $params);
	}
	
	
	/**
	 * Return a resource object to work with
	 */
	public function resource($data)
	{
		return new Alloy_Resource($data);
	}
	
	
	/**
	 * Return a session object to work with
	 */
	public function session()
	{
		if(null === $this->session) {
			$this->session = new Alloy_Session();
		}
		return $this->session;
	}
	
	
	/**
	 * Event: Trigger a named event and execute callbacks that have been hooked onto it
	 * 
	 * @param string $name Name of the event
	 * @param array $params Parameters to pass to bound event callbacks
	 */
	public function trigger($name, array $params = array())
	{
		if(isset($this->binds[$name])) {
			foreach($this->binds[$name] as $hookName => $callback) {
				call_user_func_array($callback, $params);
			}
		}
		
		$this->trace('[Event] Triggered: ' . $name);
	}
	
	
	/**
	 * Event: Add callback to be triggered on event name
	 * 
	 * @param string $name Name of the event
	 * @param string $hookName Name of the bound callback that is being added (custom for each callback)
	 * @param callback $callback Callback to execute when named event is triggered
	 */
	public function bind($eventName, $hookName, $callback)
	{
		$this->binds[$eventName][$hookName] = $callback;
		$this->trace('[Event] Hook callback added: ' . $hookName . ' on event ' . $eventName);
	}
	
	
	/**
	 * Event: Remove callback by name
	 */
	public function unbind($eventName, $hookName)
	{
		if(isset($this->binds[$eventName][$hookName])) {
			unset($this->binds[$eventName][$hookName]);
		}
		$this->trace('[Event] Hook callback removed: ' . $hookName);
	}
	
	
	/**
	 * Generate URL from given params
	 *
	 * @param string $url
	 * @return string
	 */
	public function url($routeName, array $params = array(), $queryParams = array()) {
		$urlBase = $this->config('url.root');
		// Use query string if URL rewriting is not enabled
		if(!$this->config('url.rewrite')) {
			$urlBase .= "?r=";
		}
		// Query params
		$queryString = "";
		if(count($queryParams) > 0) {
			$queryString = "?" . http_build_query($queryParams);
		}
		$fullUrl = $urlBase . strtolower(ltrim($this->router()->url($routeName, $params), '/')) . $queryString;
		return $fullUrl;
	}
	
	
	/**
	 * Truncates a string to a certian length & adds a "..." to the end
	 *
	 * @param string $string
	 * @return string
	 */
	public function truncate($string, $endlength="30", $end="...") {
	    $strlen = strlen($string);
	    if($strlen > $endlength) {
	        $trim = $endlength-$strlen;
	        $string = substr($string, 0, $trim); 
	        $string .= $end;
	    }
	    return $string;
	}
	
	
	/**
	 * Converts underscores to spaces and capitalizes first letter of each word
	 *
	 * @param string $word
	 * @return string
	 */
	public function formatUnderscoreWord($word) {
		return ucwords(str_replace('_', ' ', $word));
	}
	
	
	/**
	 * Format given string to valid URL string
	 *
	 * @param string $url
	 * @return string URL-safe string
	 */
	public function formatUrl($string)
	{
		// Allow only alphanumerics, underscores and dashes
		$string = preg_replace('/([^a-zA-Z0-9_\-]+)/', '-', strtolower($string));
		// Replace extra spaces and dashes with single dash
		$string = preg_replace('/\s+/', '-', $string);
		$string = preg_replace('|-+|', '-', $string);
		// Trim extra dashes from beginning and end
		$string = trim($string, '-');
	
		return $string;
	}

	
	/**
	 * Filesize Calculating function
	 * Retuns the size of a file in a "human" format
	 * 
	 * @param int $size Filesize in bytes
	 * @return string Calculated filesize with units (ex. "4.58 MB")
	 */
	public function formatFilesize($size) {
	    $kb=1024;
	    $mb=1048576;
	    $gb=1073741824;
	    $tb=1099511627776;
	
	    if($size < $kb) {
	        return $size." B";
	    } else if($size < $mb) {
	        return round($size/$kb,2)." KB";
	    } else if($size < $gb) {
	        return round($size/$mb,2)." MB";
	    } else if($size < $tb) {
	        return round($size/$gb,2)." GB";
	    } else {
	        return round($size/$tb,2)." TB";
	    }
	}
	
	
	/**
	 * Convert to useful array style from HTML form input style
	 * Useful for matching up input arrays without having to increment a number in field names
	 * 
	 * Input an array like this:
	 * [name]	=>	[0] => "Google"
	 *				[1] => "Yahoo!"
	 * [url]	=>	[0] => "http://www.google.com"
	 *				[1] => "http://www.yahoo.com"
	 *
	 * And you will get this:
	 * [0]	=>	[name] => "Google"
	 *			[title] => "http://www.google.com"
	 * [1]	=>	[name] => "Yahoo!"
	 *			[title] => "http://www.yahoo.com"
	 *
	 * @param array $input
	 * @return array
	 */
	public function arrayFlipConvert(array $input) {
		$output = array();
		foreach($input as $key => $val) {
			foreach($val as $key2 => $val2) {
				$output[$key2][$key] = $val2;
			}
		}
		return $output;
	}
	
	
	/**
	 * Custom error reporting
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline) {
		$errorMsg = $errstr . " (Line: " . $errline . ")";
		if($errno != E_WARNING && $errno != E_NOTICE && $errno != E_STRICT) {
			throw new Exception($errorMsg, $errno);
		} else {
			return false; // Let PHP handle it
		}
	}
}

/**
 * Custom function to ensure only one kernel instance
 */
function Alloy(array $cfg = array()) {
	$kernel = Alloy_Kernel::getInstance($cfg);
	return $kernel;
}