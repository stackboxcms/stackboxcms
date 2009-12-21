<?php
require dirname(__FILE__) . '/AppKernel/Main.php';
/**
 * Cx Kernel
 *
 * Cont-xt Application kernel
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 */
class Cx extends AppKernel_Main
{
	protected $loadPaths = array();
	protected $modulePaths = array();
	protected $pluginPaths = array();
	
	protected static $database;
	protected static $session;
	protected static $mappers = array();
	
	
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
	 * Add path for class loader to look in
	 */
	public function addLoadPath($path)
	{
		$this->loadPaths[] = $path;
	}
	 
	 
	/**
	 * Return array of set class paths
	 */
	public function loadPaths()
	{
		return $this->loadPaths;
	}
	
	/**
	 * Add path for module loader to look in
	 */
	public function addModulePath($path)
	{
		$this->modulePaths[] = $path;
	}
	 
	 
	/**
	 * Return array of set module paths
	 */
	public function modulePaths()
	{
		return $this->modulePaths;
	}
	
	/**
	 * Add path for plugin loader to look in
	 */
	public function addPluginPath($path)
	{
		$this->pluginPaths[] = $path;
	}
	 
	 
	/**
	 * Return array of set plugin paths
	 */
	public function pluginPaths()
	{
		return $this->pluginPaths;
	}
	
	
	/**
	 * Load (include) class file
	 * Overridden to enable custom 'addLoadPath' function to add multiple module load paths
	 *
	 * @param $className string Name of the class
	 * @param $path string Path to begin looking in for class file
	 * @return boolean
	 */
	public function load($className, $paths = null)
	{
		// Module
		if(strpos($className, 'Module_') === 0) {
			$paths = (array) $paths + $this->modulePaths();
		
		// Plugin
		} elseif(strpos($className, 'Plugin_') === 0) {
			$paths = (array) $paths + $this->pluginPaths();
		
		// Library
		} else {
			$paths = (array) $paths + $this->loadPaths();
		}
		
		return parent::load($className, $paths);
	}
	
	
	/**
	 * Load and return instantiated module class
	 *
	 * @param $className string Name of the class
	 * @return object
	 * @throws Cx_Exception_FileNotFound
	 */
	public function module($module)
	{
		// Clean module name to prevent possible security vulnerabilities
		$sModule = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
		$sModuleClass = 'Module_' . $sModule . '_Controller';
		
		// Replace underscores with folder slashes
		$sModule = str_replace('_', '/', $sModule);
		
		// Load module file, call function on it
		$loaded = $this->load($sModuleClass, $this->modulePaths());
		if(!$loaded) {
			$this->dump($this->modulePaths());
			throw new Cx_Exception_FileNotFound("Module '" . $sModule . "' not found (class " . $sModuleClass . ")");
		}
		
		// Instantiate class and call method
		$sModuleObject = new $sModuleClass($this);
		
		// Run init() setup only if supported
		if(is_callable(array($sModuleObject, 'init'))) {
			$sModuleObject->init();
		}
		
		return $sModuleObject;
	}
	
	
	/**
	 * Get mapper object to work with
	 * Ensures only one instance of a mapper gets loaded
	 */
	public function mapper($mapperName = null)
	{
		// Append given name, if any
		if(null === $mapperName) {
			$mapperName = $this->name();
		}
		
		// Append 'Mapper' to the end, as per convention
		$mapperName .=  "_Mapper";
		
		if(!isset(self::$mappers[$mapperName])) {
			// Ensure file can be loaded
			if(!$this->load($mapperName)) {
				throw new Exception("Unable to load class '" . $mapperName . "' - requested class not found");
			}
			
			// Create new mapper, passing in adapter connection
			$mapper = new $mapperName($this->database());
			
			// Auto-Migrations when in debug mode
			if($this->config('cx.mode.development')) {
				$mapper->migrate();
			}
			
			self::$mappers[$mapperName] = $mapper;
		}
		return self::$mappers[$mapperName];
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
	public function dispatch($module, $action = 'indexAction', array $params = array())
	{
		// Get module
		$sModuleObject = $this->module($module);
		
		// Run module action
		if(!is_callable(array($sModuleObject, $action))) {
			throw new Cx_Exception_FileNotFound("Module '" . $sModule ."' does not have a callable method '" . $action . "'");
		}
		
		// Handle result
		$paramCount = count($params);
		if($paramCount == 0) {
			$result = $sModuleObject->$action();
		} elseif($paramCount == 1) {
			$result = $sModuleObject->$action(current($params));
		} else {
			$result = call_user_func_array(array($sModuleObject, $action), $params);
		}
		return $result;
	}
	
	
	/**
	 * Return a session object to work with
	 */
	public function session()
	{
		if(null === self::$session) {
			self::$session = new Cx_Session();
		}
		return self::$session;
	}
	
	
	/**
	 * Get instance of database connection
	 */
	public function database($name = 'master')
	{
		if(null === self::$database) {
			$cfg = $this->config('cx.database.' . $name);
			if($this->load('phpDataMapper_Adapter_Mysql')) {
				self::$database = new phpDataMapper_Adapter_Mysql($cfg['host'], $cfg['dbname'], $cfg['username'], $cfg['password']);
			} else {
				throw new Exception("Unable to load database connection - Check to ensure the username and password are correct");
			}
		}
		return self::$database;
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
	public function formatClassname($word) {
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
	 * Print out an array or object contents in preformatted text
	 * Useful for debugging and quickly determining contents of variables
	 */
	public function dump()
	{
		$objects = func_get_args();
		foreach($objects as $object) {
			echo "<h1>Dumping " . gettype($object) . "</h1><br />\n";
			echo "\n<pre>\n";
			print_r($object);
			echo "\n</pre>\n";
		}
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
function cx(array $cfg = array()) {
	$cx = Cx::getInstance($cfg);
	return $cx;
}