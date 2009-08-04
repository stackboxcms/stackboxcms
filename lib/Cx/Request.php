<?php
/**
 * HTTP Request Class
 * $Id$
 *
 * Handles incoming HTTP request
 *
 * Lots of ideas and code borrowed from Zend Framework 1.5
 * Zend_Controller_Request_Http
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Request
{
	// Request parameters
	protected $params = array();
	
	
	/**
	 *	Constructor Function
	 */
	public function __construct($url = null)
	{
		// Clean up magic quotes mess
		$this->fixMagicQuotes();
		
		// Apply the 'urldecode' function to all request variables
		//$this->map('urldecode');
	}
	
	
	/**
     * Access values contained in the superglobals as public members
     * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
     *
     * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
     * @param string $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        switch (true) {
			
            case isset($_GET[$key]):
                $value = $_GET[$key];
            break;
			
			case isset($_POST[$key]):
                $value = $_POST[$key];
            break;
			//*
			case isset($this->params[$key]):
                $value = $this->params[$key];
			break;
			//*/
			case isset($_COOKIE[$key]):
                $value = $_COOKIE[$key];
            break;
			
			case isset($_SERVER[$key]):
                $value = $_SERVER[$key];
            break;
			
			case isset($_ENV[$key]):
                $value = $_ENV[$key];
            break;
			
			default:
                return $default;
        }

		return $value;
    }
	// Automagic companion function
	public function __get($key)
	{
		return $this->get($key);
	}


	/**
	 *	Override request parameter value
	 *
	 *	@string $key
	 *	@string $value
	 */
	public function set($key, $value)
	{
		$this->params[$key] = $value;
	}
	// Automagic companion function
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}
	
	
	/**
     * Check to see if a property is set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        switch (true) {
			//*
            case isset($this->params[$key]):
                return true;
			//*/
            case isset($_GET[$key]):
                return true;
            case isset($_POST[$key]):
                return true;
            case isset($_COOKIE[$key]):
                return true;
            case isset($_SERVER[$key]):
                return true;
            case isset($_ENV[$key]):
                return true;
            default:
                return false;
        }
    }
	
	
	/**
     * Retrieve request parameters
     *
     * @return array Returns array of all GET, POST, and set params
     */
    public function getParams()
    {
		$params = array_merge($_GET, $_POST, $this->params);
        return $params;
    }
	
	
	/**
     * Set additional request parameters
     */
    public function setParams($params)
    {
		if($params && is_array($params)) {
			foreach($params as $pKey => $pValue) {
				$this->set($pKey, $pValue);
			}
		}
    }
	
	
	/**
     * Retrieve a member of the $params set variables
     *
     * If no $key is passed, returns the entire $params array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getParam($key = null, $default = null)
    {
        if (null === $key) {
            return $this->params;
        }

        return (isset($this->params[$key])) ? $this->params[$key] : $default;
    }
	
	
	/**
     * Retrieve a member of the $_GET superglobal
     *
     * If no $key is passed, returns the entire $_GET array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getQuery($key = null, $default = null)
    {
        if (null === $key) {
            return $_GET;
        }

        return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }
	
	
    /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }
	
	
    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @todo How to retrieve from nested arrays
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getCookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }
	
	
    /**
     * Retrieve a member of the $_SERVER superglobal
     *
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
	
	
    /**
     * Retrieve a member of the $_ENV superglobal
     *
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getEnv($key = null, $default = null)
    {
        if (null === $key) {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }
	
	
	/**
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     */
    public function getHeader($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
		
        // This seems to be the only way to get the Authorization header on Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }

        return false;
    }
	
	
	/**
     * Return the method by which the request was made
     *
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }
	
	
	/**
	 * Get a user's correct IP address
	 * Retrieves IP's behind firewalls or ISP proxys like AOL
	 *
	 * @return string IP Address
	 */
	public function getIP()
	{
		$ip = FALSE;

		if( !empty( $_SERVER["HTTP_CLIENT_IP"] ) )
			$ip = $_SERVER["HTTP_CLIENT_IP"];

		if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
			// Put the IP's into an array which we shall work with shortly.
			$ips = explode( ", ", $_SERVER['HTTP_X_FORWARDED_FOR'] );
			if( $ip ){
				array_unshift( $ips, $ip );
				$ip = false;
			}

			for( $i = 0; $i < count($ips); $i++ ){
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
	
	
	/**
	 *	Determine is incoming request is POST
	 *
	 *	@return boolean
	 */
	public function isPost()
	{
		return ($this->getMethod() == "POST");
	}
	
	
	/**
	 *	Determine is incoming request is GET
	 *
	 *	@return boolean
	 */
	public function isGet()
	{
		return ($this->getMethod() == "GET");
	}
	
	
	/**
	 *	Determine is incoming request is PUT
	 *
	 *	@return boolean
	 */
	public function isPut()
	{
		return ($this->getMethod() == "PUT");
	}
	
	
	/**
	 *	Determine is incoming request is DELETE
	 *
	 *	@return boolean
	 */
	public function isDelete()
	{
		return ($this->getMethod() == "DELETE");
	}
	
	
	/**
	 *	Determine is incoming request is HEAD
	 *
	 *	@return boolean
	 */
	public function isHead()
	{
		return ($this->getMethod() == "HEAD");
	}

	
	/**
	 *	Determine is incoming request is secure HTTPS
	 *
	 *	@return boolean
	 */
	public function isSecure()
	{
		return  (bool) ((!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true);
	}
	

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Works with Prototype/Script.aculo.us, jQuery, YUI, Dojo, possibly others.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

	
    /**
     * Is this a Flash request?
     * 
     * @return bool
     */
    public function isFlash()
    {
        return ($this->getHeader('USER_AGENT') == 'Shockwave Flash');
    }
	
	
	/**
	 *	Apply a user-defined function to all request parameters
	 *
	 *	@string $function user-defined function
	 */
	public function map($function)
	{
		$in = array(&$_GET, &$_POST, &$_COOKIE);
		while (list($k,$v) = each($in)) {
			if(is_array($v)) {
				foreach ($v as $key => $val) {
					if (!is_array($val)) {
						$in[$k][$key] = $function($val);
					}
					$in[] =& $in[$k][$key];
				}
			}
		}
		unset($in);
		return true;
	}
	
	
	/**
	 * Cleans arrays ruined by MagicQuotes=On.
	 */
	protected function fixMagicQuotes()
	{
		if (get_magic_quotes_gpc()) {
			$this->map('stripslashes');
		}
		return true;
	}
}