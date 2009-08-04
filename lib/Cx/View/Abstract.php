<?php	
/**
 * Absrtact View Class
 * $Id$
 *
 * View abstract class with some global helper functions like linking relationships
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */
abstract class Cx_View_Abstract
{	
	// Message constants
	const MSG_FLASH = 'flash';
	const MSG_ERROR = 'error';
	
	// Stores instances of already loaded helpers
	protected static $helpers = array();
	
	protected $messages = array();
	protected $headContent = array();
	
	public $content;
	
	
	/**
	 * Loads a helper functions file for use in current view template
	 */
	public function loadHelper($helper)
	{
		// Check if helper has been loaded already
		if(array_key_exists($helper, self::$helpers)) {
			return self::$helpers[$helper];
		} else {
			// Load helper
			$helperName = ucfirst($helper);
			$file = cx_config('cx.path_lib') . 'Cx/View/Helper/' . $helperName . '.php';
			if(file_exists($file)) {
				require($file);
				$className = 'Cx_View_Helper_' . $helperName;
				if(class_exists($className)) {
					$helperInstance = new $className($this);
					self::$helpers[$helper] = $helperInstance;
					return $helperInstance;
				} else {
					return false;
				}
			} else {
				throw new Cx_View_Exception($helper . ' helper does not exist in ' . cx_config('cx.path_lib') . '<br />Looking for ' . $file);
				return false;
			}
		}
	}
	
	

	/**
	 * Full URL to different component/action
	 */
	public function urlTo($params, $route = null, array $qsData = array(), $qsAppend = false)
	{
		// HTTPS Secure URL?
		$isSecure = false;
		if(isset($params['secure']) && true === $params['secure']) {
			$isSecure = true;
			unset($params['secure']);
		}
		
		// Assemble with router
		if(is_array($params)) {
			$url = $this->router->urlTo($params, $route);
		} else {
			$url = (string) $params;
		}
		
		// Is there query string data?
		$request = $this->router->getRequest();
		if($qsAppend && $request->getQuery()) {
			$qsData = array_merge($request->getQuery(), $qsData);
		}
		if(count($qsData) != 0) {
			// Build query string from array $qsData
			$queryString = http_build_query($qsData, '', '&amp;');
		} else {
			$queryString = false;
		}
		
		// Base URL
		if(is_array($params)) {
			$baseUrl = $this->router->getBasePath();
		} else {
			$baseUrl = "";
		}
		
		// HTTPS Secure URL?
		if($isSecure) {
			$baseUrl = str_replace('http:', 'https:', $baseUrl);
		}
		
		// Determine URL
		if(cx_config('cx.mod_rewrite')) {
			$url = $baseUrl . $url . (($queryString !== false) ? '?' . $queryString : '');
		} else {
			$url = $baseUrl . '?url=' . $url . (($queryString !== false) ? '&amp;' . $queryString : '');
		}
		
		return $url;
	}


	/**
	 * HTML link tag to another component/action
	 */
	public function linkTo($title, $params, $route = null, array $extra = array(), $qsAppend = false)
	{
		$qsData = isset($params['querystring']) && is_array($params['querystring']) ? $params['querystring'] : array();
		$extra['title'] = isset($extra['title']) ? $extra['title'] : trim(strip_tags($title));
		$tag = '<a href="' . $this->urlTo($params, $route, $qsData, $qsAppend) . '" ' . $this->listExtra($extra) . '>' . $title . '</a>';
		return $tag;
	}
	
	
	/**
	 * Returns URL path to public folder for current component
	 */
	public function urlToResource($module)
	{
		// Use current component if none specified
		if(is_null($module)) {
			$module = cx_config('cx.dir_public') . $this->module . '/';
		} elseif($module == 'public') {
			$module = '';
		} else {
			$module = cx_config('cx.dir_public') . $module . '/';
		}
		$src = cx_config('cx.url') . '/' . $module . '';
		return $src;
	}
	
	
	/**
	 * Date/time string to date format
	 */
	public function toDateFormat($input, $format = 'M d, Y')
	{
		return $input ? date($format, strtotime($input)) : date($format);
	}
	
	
	/**
	 * List array values as HTML tag attributes
	 */
	protected function listExtra(array $extra)
	{
		$str = '';
		if(count($extra) > 0)
		{
			foreach($extra as $key => $value)
			{
				// Javascript "confirm" box
				if($key == "confirm") {
					$msg = (empty($value) || $value == "true") ? "Are you sure?" : $value;
					$str .= ' onClick="if(!confirm(\'' . $msg . '\')){ return false; }"';
				} else {
					$str .= ' ' . $key . '="' . $value . '"';
				}
			}
		}
		// Return string
		return $str;
	}
	

	/**
	 * Produces an HTML image tag
	 */
	public function imageTag($image, array $extra = array(), $component = NULL)
	{
		$src = $this->urlToResource($component) . 'images/' . $image;
		$tag = '<img src="' . $src . '"' . $this->listExtra($extra) . ' />' . "\n";
		return $tag;
	}
	
	
	/**
	 * Produces an HTML stylesheet link tag
	 */
	public function stylesheetTag($str, $component = NULL, array $extra = array())
	{
		$href = $this->urlToResource($component) . 'styles/' . $str;
		$tag = '<link rel="stylesheet" type="text/css" href="' . $href . '"' . $this->listExtra($extra) . ' />' . "\n";
		return $tag;
	}
	
	
	/**
	 * Produces an HTML stylesheet link tag
	 */
	public function scriptTag($str, $component = NULL, array $extra = array())
	{
		$src = $this->urlToResource($component) . 'scripts/' . $str;
		$tag = '<script type="text/javascript" src="' . $src . '"' . $this->listExtra($extra) . '></script>' . "\n";
		return $tag;
	}
	
	
	/**
	 * Adds a content string to the <head> element on a HTML page
	 * Takes a $name parameter so that previously assigned content can be overwritten, avoiding duplicate content
	 * 
	 * @param string $name Name of the content you wish to add
	 * @param string $content The actual raw content that will be added
	 */
	public function addHeadContent($name, $content)
	{
		$this->headContent[$name] = $content;
		return true;
	}
	
	
	/**
	 * Gets all content to add in the <head> element on a HTML page
	 */
	public function getHeadContent()
	{
		return $this->headContent;
	}
	

	/**
	 * Escapes HTML entities
	 * Use to prevent XSS attacks
	 *
	 * @link http://ha.ckers.org/xss.html
	 */
	public function escapeHtml($str)
	{
		return htmlentities($str, ENT_NOQUOTES, "UTF-8");
	}


	/**
	 * Check if any errors exist
	 */
	public function hasErrors()
	{
		return count($this->getMessages(self::MSG_ERROR));
	}
	
	
	/**
	 * Check if any errors exist
	 */
	public function hasMessages()
	{
		$flashMsgs = isset($_SESSION['cx_messages']) ? $_SESSION['cx_messages'] : array();
		$this->messages = array_merge_recursive($this->messages, $flashMsgs);
		
		return count($this->messages);
	}
	
	
	/**
	 * Get array of error messages
	 */
	public function getMessages($class = null)
	{
		if($class) {
			$msgs = isset($this->messages[$class]) ? $this->messages[$class] : null;
		} else {
			$msgs = $this->messages;
		}
		
		return $msgs;
	}
	
	
	/**
	 * Add an error to error messages array
	 */
	public function addMessage($msg, $class = self::MSG_FLASH)
	{
		// Flash messages are stored in session until they are displayed
		if($class == self::MSG_FLASH) {
			$_SESSION['cx_messages'][$class][] = $msg;
			$_SESSION['cx_messages'][$class] = array_unique($_SESSION['cx_messages'][$class]);
		} else {
			// Add to error array
			$this->messages[$class][] = $msg;
		}
	}


	/**
	 * Add an array of errors all at once
	 */
	public function addMessages(array $msgs, $class = self::MSG_FLASH)
	{
		foreach($msgs as $msg)
		{
			$this->addMessage($msg, $class);
		}
	}
	
	
	/**
	 * Convenience methods
	 */
	public function addFlash($msg)
	{
		$this->addMessage($msg, self::MSG_FLASH);
	}
	public function addError($msg)
	{
		$this->addMessage($msg, self::MSG_ERROR);
	}
	public function addErrors(array $msgs)
	{
		$this->addMessages($msgs, self::MSG_ERROR);
	}


	/**
	 * Display any errors on the page
	 */
	public function showMessages()
	{
		$output = "";
		$lastClass = "";
		
		if($this->hasMessages())
		{
			$messages = $this->getMessages();
			
			foreach($messages as $class => $msgs)
			{
				$output .= "<div class=\"box_msg box_msg_" . $class . "\">\n";
				$output .= "  <ul>\n";
				
				foreach($msgs as $msg)
				{
					$output .= "<li>" . $msg . "</li>\n";
				}
				
				$output .="  </ul>\n";
				$output .= "</div>";
			}
		}
		
		// Unset/clear stored messages
		if(isset($_SESSION['im_messages'])) {
			unset($_SESSION['im_messages']);
		}
		
		return $output;
	}
	
	
	/**
	 * Set router class - Router will be used to build URLs
	 * 
	 * @param Cx_Http_Router $router
	 */
	public function setRouter(Cx_Http_Router $router)
	{
		$this->router = $router;
	}
	
	
	/**
	 * Set template contents
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	
	/**
	 * Get template contents
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	
	/**
	 * Does template have contents?
	 */
	public function hasContent()
	{
		return !empty($this->content);
	}
}