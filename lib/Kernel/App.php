<?php
/**
 * AppKernel
 *
 * Application Kernel for PHP projects
 * Functions:
 * 	- Service Locator/Registry for object storage and common access from anywhere in your applicaton
 * 	- Configuration storage and retrieval application-wide
 *	- RESTful Request routing
 * 	- Easily extendable to create new functionality
 *
 * @package AppKernel
 * @author Vance Lucas <vance@vancelucas.com>
 */
class AppKernel
{
	protected static $self;
	
	protected static $cfg = array();
	protected static $trace = array();
	protected static $traceMemoryStart = 0;
	protected static $traceMemoryLast = 0;
	protected static $traceTimeStart = 0;
	protected static $traceTimeLast = 0;
	protected static $debug = false;
	
	protected $instances = array();
	protected $loaded = array();
	protected $callbacks = array();
	
	
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
	 * Constructor
	 *
	 * @param array $config Array of configuration settings to store. Passed array will be stored as a static variable and automatically merged with previous stored settings
	 */
	public function __construct(array $config = array())
	{
		$this->config($config);
		
		// Save memory starting point
		self::$traceMemoryStart = memory_get_usage();
		self::$traceTimeStart = microtime(true);
		// Set last as current starting for good zero-base
		self::$traceMemoryLast = self::$traceMemoryStart;
		self::$traceTimeLast = self::$traceTimeStart;
	}
	
	
	/**
	 * Return configuration value
	 * 
	 * @param mixed $value If string: Value key to search for, If array: Merge given array over current config settings
	 * @param string $default Default value to return if $value not found
	 * @return string
	 */
	public function config($value = null, $default = false) {
		// Setter
		if(is_array($value)) {
			if(count($value) > 0) {
				// Merge given config settings over any previous ones (if $value is array)
				self::$cfg = $this->array_merge_recursive_replace(self::$cfg, $value);
			}
		// Getter
		} else {
			// Config array is static to persist across multiple instances
			$cfg = self::$cfg;
			
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
	}
	
	
	/**
	 * Factory method for loading and instantiating new objects
	 *
	 * @param string $className Name of the class to attempt to load
	 * @return object Instance of the class requested
	 * @throws InvalidArgumentException
	 */
	public function factory($className, array $args = array())
	{
		// @todo Make this more versitile...
		$paths = array(dirname(__FILE__) . '/AppKernel/');
		
		if(isset($this->instances[$className])) {
			return $this->instances[$className];
		}
		
		// Ensure it was loaded
		if(!$this->load($className, $paths)) {
			throw new InvalidArgumentException("Unable to load requested class '" . $className . "'");
		}
		
		// Return new class instance
		if(count($args) == 0) {
			$instance = new $className();
		} else {
			$class = new ReflectionClass($className);
			$instance = $class->newInstanceArgs($args);
		}
		
		return $this->setInstance($className, $instance);
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
	public function setInstance($class, $instance)
	{
		$this->instances[$class] = $instance;
		return $instance;
	}
	
	
	/**
	 * Load (include) class file
	 *
	 * @param $className string	Name of the class
	 * @param $path mixed(string|array)	Path(s) to look in to load class file
	 * @return boolean
	 */
	public function load($className, $paths = array())
	{
		// Ensure class is not already loaded
		if(class_exists($className, false)) {
			return true;
		}
		
		// Make path array if it's a string
		if(is_string($paths)) {
			$paths = (array) $paths;	
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
		// Assumes Zend/PEAR class naming conventions
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
	 * Fetch a URL with given parameters
	 */
	public function fetch($url, array $params = array(), $method = 'GET')
	{
		$this->traceInternal("Fetching External URL: [" . strtoupper($method) . "] " . $url, $params);
		
		$urlParts = parse_url($url);
		$queryString = http_build_query($params);
		
		// Append params to URL as query string if not a POST
		if(strtoupper($method) != 'POST') {
			$url = $url . "?" . $queryString;
		}
		
		// Use cURL
		if(function_exists('curl_init')) {
			$ch = curl_init($urlParts['host']);
			curl_setopt($ch, CURLOPT_URL, $url) or die("Invalid cURL Handle Resouce");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the data
	        curl_setopt($ch, CURLOPT_HEADER, false); // Get headers
	        
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	        
	        if(strtoupper($method) == 'POST') {
	        	curl_setopt($ch, CURLOPT_POST, true);
            	curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
	        }
	        
	        // HTTP digest authentication
			if(isset($urlParts['user']) && isset($urlParts['pass'])) {
				$authHeaders = array("Authorization: Basic ".base64_encode($urlParts['user'].':'.$urlParts['pass']));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
			}
			
			$response = curl_exec($ch);
			$responseInfo = curl_getinfo($ch);
			curl_close($ch);
			
		// Use sockets... (eventually)
		} else {
			throw new Exception(__METHOD__ . " Requres the cURL library to work.");
		}
		
		// Only return false on 404 or 500 errors for now
		if($responseInfo['http_code'] == 404 || $responseInfo['http_code'] == 500) {
			$response = false;
		}
		
		return $response;
	}
	
	
	/**
	 * Custom error-handling function for failed file include error surpression
	 */
	protected function throwError($errno, $errstr, $errfile, $errline)
	{
		$msg = "";
		switch ($errno) {
			case E_USER_ERROR:
			case E_USER_WARNING:
				$msg .= "<b>ERROR</b> [$errno] $errstr<br />\n";
				$msg .= "  Fatal error on line $errline in file $errfile";
				$msg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				$msg .= "Aborting...<br />\n";
				throw new Exception($msg);
				//exit(1);
			break;
				
			case E_USER_NOTICE:
			default:
		}
		
	    // Don't execute PHP internal error handler
	    return true;
	}
	
	
	/**
	 * Get router object
	 */
	public function router()
	{
		return $this->factory('AppKernel_Router');
	}
	
	
	/**
	 * Get request object
	 */
	public function request()
	{
		return $this->factory('AppKernel_Request');
	}
	
	
	/**
	 * Send HTTP response header
	 */
	public function response($statusCode = 200)
	{
		$responses = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',
			
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended'
		);
		
		$statusText = "";
		if(isset($responses[$statusCode])) {
			$statusText = $responses[$statusCode];
		}
		
		// Send HTTP Header
		header($_SERVER['SERVER_PROTOCOL'] . " " . $statusCode . " " . $statusText);
	}
	
	
	/**
	 * Send HTTP 302 redirect
	 */
	public function redirect($url)
	{
		header("Location: " . $url);
		exit();
	}
	
	
	/**
	 * Trace messages and return message stack
	 * Used for debugging and performance monitoring
	 *
	 * @param string $msg Log Message
	 * @param array $data Arroy of any data to log that is related to the message
	 * @param string $function Function or Class::Method call
	 * @param string $file File path where message originated from
	 * @param int $line Line of the file where message originated
	 * @return array Message stack
	 */
	public function trace($msg = null, $data = array(), $function = null, $file = null, $line = null, $internal = false)
	{
		// Don't incur the overhead if not in debug mode
		if(!self::$debug) {
			return false;
		}
		
		// Build log entry
		if(null !== $msg) {
			$entry = array(
				'message' => $msg,
				'data' => $data,
				'function' => $function,
				'file' => $file,
				'line' => $line,
				'internal' => (int) $internal,
				);
			// Only log time & memory for non-internal method calls
			if(!$internal) {
				$currentTime = microtime(true);
				$currentMemory = memory_get_usage();
				$entry += array(
					'time' => ($currentTime - self::$traceTimeLast),
					'time_total' => $currentTime - self::$traceTimeStart,
					'memory' => $currentMemory - self::$traceMemoryLast,
					'memory_total' => $currentMemory - self::$traceMemoryStart
					);
				// Store as last run
				self::$traceTimeLast = $currentTime;
				self::$traceMemoryLast = $currentMemory;
			}
			self::$trace[] = $entry;
		}
		
		return self::$trace;
	}
	
	
	/**
	 * Internal message/action trace - to separate the internal function calls from the stack
	 */
	protected function traceInternal($msg = null, $data = array(), $function = null, $file = null, $line = null)
	{
		// Don't incur the overhead if not in debug mode
		if(!self::$debug) {
			return false;
		}
		
		// Log with last parameter as 'true' - last param will always be internal marker
		return $this->trace($msg, $data, $function, $file, $line, true);
	}
	
	
	/**
	 * Debug mode switch
	 */
	public function debug($switch = true)
	{
		self::$debug = $switch;
		return $this->trace();
	}
	
	
	/**
	 * Add a custom user method via PHP5.3 closure or PHP callback
	 */
	public function addMethod($method, $callback)
	{
		$this->traceInternal("Added '" . $method . "' to " . __METHOD__ . "()");
		$this->callbacks[$method] = $callback;
	}
	
	
	/**
	 * Run user-added callback
	 * @throws BadMethodCallException
	 */
	public function __call($method, $args)
	{
		if(isset($this->callbacks[$method]) && is_callable($this->callbacks[$method])) {
			$this->trace("Calling custom method '" . $method . "' added with " . __CLASS__ . "::addMethod()", $args);
			$callback = $this->callbacks[$method];
			return call_user_func_array($callback, $args);
		} else {
			throw new BadMethodCallException(__CLASS__ . " doesn't know the command '" . $method . "' or the command is not a valid callback type.");	
		}
	}
	
	
	/**
	 * Merges any number of arrays of any dimensions, the later overwriting
	 * previous keys, unless the key is numeric, in whitch case, duplicated
	 * values will not be added.
	 *
	 * The arrays to be merged are passed as arguments to the function.
	 *
	 * @access public
	 * @return array Resulting array, once all have been merged
	 */
	public function array_merge_recursive_replace() {
		// Holds all the arrays passed
		$params =  func_get_args();
	   
		// First array is used as the base, everything else overwrites on it
		$return = array_shift($params);
	   
		// Merge all arrays on the first array
		foreach ($params as $array) {
			foreach($array as $key => $value) {
				// Numeric keyed values are added (unless already there)
				if(is_numeric($key) && (!in_array($value, $return))) {
					if(is_array($value)) {
						$return[] = $this->array_merge_recursive_replace($return[$key], $value);
					} else {
						$return[] = $value;
					}
					  
				// String keyed values are replaced
				} else {
					if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
						$return[$key] = $this->array_merge_recursive_replace($return[$key], $value);
					} else {
						$return[$key] = $value;
					}
				}
			}
		}
	   
		return $return;
	}
}


/**
 * Get and return instance of AppKernel
 * Checks if 'Kernel' function already exists so it can be overridden/customized
 */
if(!function_exists('AppKernel')) {
	function AppKernel(array $config = array()) {
		$kernel = AppKernel::getInstance($config);
		return $kernel;
	}
}

/**
 * Custom 'memory_get_usage' function if one is not defined by PHP
 * Should not be a problem in most systems or for PHP >= 5.3.0
 */
if(!function_exists('memory_get_usage')) {
	function memory_get_usage() {
		$return = 0;
		//If its Windows
		//Tested on Win XP Pro SP2. Should work on Win 2003 Server too
		//Doesn't work for 2000
		//If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
		if (substr(PHP_OS,0,3) == 'WIN') {
			if (substr(PHP_OS, 0, 3) == 'WIN') {
				$output = array();
				exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
				
			$return = preg_replace( '/[\D]/', '', $output[5] ) * 1024;
			}
		} else {
			//We now assume the OS is UNIX
			//Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
			//This should work on most UNIX systems
			$pid = getmypid();
			exec("ps -eo%mem,rss,pid | grep $pid", $output);
			$output = explode("  ", $output[0]);
			//rss is given in 1024 byte units
			$return = $output[1] * 1024;
		}
		return $return;
	}
} 