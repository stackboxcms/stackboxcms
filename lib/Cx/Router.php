<?php
/**
 * Router
 * $Id$
 *
 * Maps URL to named parameters for use in application
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Router
{
	// Store specified routes
	protected $_routes = array();
	protected $_defaultRoute = array();
	
	// Default regex match syntax to replace named keys with
	protected $_paramRegex = "([a-zA-Z0-9\_\-]+)";
	protected $_paramRegexNumeric = "([0-9]+)";
	protected $_paramRegexWildcard = "(.*)";
	
	
	/**
	 * Connect route
	 */
	public function addRoute($route, array $defaults = array(), $name = null)
	{
		if($name === null) {
			// Use numeric index if name is not given
			$name = count($this->getRoutes());
		}
		
		$routeRegex = null;
		$routeParams = array();
		preg_match_all("@\([\:|\*|\#]([^\)]+)\)@", $route, $regexMatches, PREG_PATTERN_ORDER);
		if(isset($regexMatches[1])) {
			$routeParams = $regexMatches[1];
			// Replace all keys with regex
			$routeRegex = $route;
			foreach($routeParams as $paramName) {
				// Does parameter have marker for custom regex rule?
				if(strpos($paramName, '|') !== false) {
					// Custom regex rule
					$paramParts = explode('|', $paramName);
					$routeRegex = str_replace("(:" . $paramName . ")", "(" . $paramParts[1] . ")", $routeRegex);
				} else {
					// Standard regex rule
					$routeRegex = str_replace("(:" . $paramName . ")", $this->_paramRegex, $routeRegex);
					$routeRegex = str_replace("(#" . $paramName . ")", $this->_paramRegexNumeric, $routeRegex);
					$routeRegex = str_replace("(*" . $paramName . ")", $this->_paramRegexWildcard, $routeRegex);
				}
			}
		}
		
		// Save route info
		$routeArray = array(
			'route' => $route,
			'regex' => $routeRegex,
			'params' => $routeParams,
			'defaults' => $defaults
		);
	
		// The first route defined is the default one
		if(count($this->getRoutes()) == 0) {
			$this->_defaultRoute = $routeArray;
		}
		
		// Save route info
		$this->_routes[$name] = $routeArray;
	}
	
	
	/**
	 * Get set routes
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}
	
	
	/**
	 * Match given URL string
	 *
	 * @param string $url
	 * @return array $params Parameters with values that were matched
	 */
	public function match($url)
	{
		if(count($this->getRoutes()) == 0) {
			throw new OutOfBoundsException("There must be at least one route defined to match for.");
		}
		
		// Clean up URL for matching
		$url = trim(urldecode($url), '/');
		$params = array();
		
		// Loop over set routes to find a match
		// Order matters - Looping will stop when first suitable match is found
		$routes = $this->getRoutes();
		foreach($routes as $routeName => $route) {
			if($params = $this->routeMatch($route, $url)) {
				break;
			}
        }
		
		// Use default route params if no match
		if(count($params) == 0) {
			$params = $this->_defaultRoute['defaults'];
		}
		
		return $params;
	}
	
	
	/**
	 * Match URL against a specific given route
	 */
	protected function routeMatch($route, $url)
	{
		$params = array();
		// Strings are padded at beginning and end, resulting in a more exact (strict) match
		$result = preg_match("@##" . $route['regex'] . "##@", "##" . $url . "##", $matches);
		if($result) {
			array_shift($matches); // Shift off first "match" result - full URL input string
			$params = array_combine($route['params'], $matches);
			$params = array_merge($route['defaults'], $params);
		}
		return $params;
	}
	
	
	/**
	 * Get string URL from given params
	 */
	public function urlTo($params)
	{
		
	}
}