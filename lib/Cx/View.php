<?php
/**
 * View Class
 * $Id$
 *
 * View class that will display and handle all templates
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_View extends Cx_View_Abstract
{
	// Template specific stuff
	protected $_module;
	protected $_template;
	protected $_templateFormat;
	protected $_vars;
	protected $_path;
	
	// Template internals
	protected $_messages = array();
	protected $_html = array('head' => array(), 'body' => array());
	
	// Saves reference to instances of view helpers
	protected static $helpers = array();
	
	// Extension type
	protected $_default_format = 'html';
	protected $_default_extenstion = 'php';
	
	
	/**
	 *	Constructor function
	 *
	 * @param $template string	Template file name to use
	 * @param $module string	Module template file resides in
	 */
	public function __construct($template, $module)
	{
		$this->setTemplate($template, $this->_default_format);
		$this->_module = $module;
	}
	
	
	/**
	 * Gets a view variable
	 *
	 * Surpress notice errors for variables not found to
	 * help make template syntax for variables simpler
	 *
	 * @param  string  key
	 * @return mixed   value if the key is found
	 * @return null    if key is not found
	 */
	public function __get($var)
	{
		if(isset($this->_vars[$var])) {
			return $this->_vars[$var];
		} else {
			return null;
		}
	}
	
	
	/**
	 * Sets a view variable.
	 *
	 * @param   string   key
	 * @param   string   value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}
	
	
	/**
	 * Gets a view variable
	 */
	public function get($var)
	{
		return $this->__get($var);
	}
	
	
	/**
	 *	Assign template variables
	 *
	 *	@param string key
	 *	@param mixed value
	 */
	public function set($key, $value='')
	{
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				if(!empty($k)) {
					$this->_vars[$k] = $v;
				}
			}
		} else {
			if(!empty($key)) {
				$this->_vars[$key] = $value;
			}
		}
	}
	
	
	/**
	 * Get template variables
	 * 
	 * @return array
	 */
	public function getVars()
	{
		return $this->_vars;
	}
	
	
	/**
	 * Converts view object to string on the fly
	 *
	 * @return  string
	 */
	public function __toString()
	{
		// Exceptions cannot be thrown in __toString method (results in fatal error)
		// We have to catch any that may be thrown and return a string
		try {
			$content = $this->render(self::OUTPUT_CAPTURE);
		} catch(Cx_Exception_View $e) {
			$content = $e->getError();
		} catch(Exception $e) {
			$content = "<strong>TEMPLATE RENDERING ERROR:</strong><br />" . $e->getMessage();
		}
		return $content;
	}
	
	
	/**
	 * Set path to look in for templates
	 */
	public function setPath($path)
	{
		$this->_path = $path;
	}
	
	
	/**
	 * Get template path
	 */
	public function getPath()
	{
		return $this->_path;
	}
	
	
	// Set template to use (changes template from what was set in constructor)
	public function setTemplate($view, $format = null)
	{
		$this->_template = $view;
		$this->_templateFormat = ($format) ? $format : $this->_default_extenstion;
	}
	
	/**
	 * Get template name that was set
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->_template;
	}
	
	
	/**
	 * Get module name that was set
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}
	
	
	/**
	 * Returns full template filename with format and extension
	 *
	 * @param OPTIONAL $template string (Name of the template to return full file format)
	 * @return string
	 */
	public function getTemplateFilename($template = NULL)
	{
		if(is_null($template)) {
			$template = $this->getTemplate();
		}
		return $template . '.' . $this->getFormat() . '.' . $this->_default_extenstion;
	}
	
	
	/**
	 * Set layout format to use
	 * Templates will use: <template>.<format>.<extension>
	 * Example: index.html.php
	 *
	 * @param $format string (html|xml)
	 */
	public function setFormat($format)
	{
		$this->_templateFormat = $format;
	}
	
	
	/**
	 * Get layout format used
	 */
	public function getFormat()
	{
		return $this->_templateFormat;
	}
	
	
	/**
	 * Display template file (read and echo contents)
	 */
	public function render()
	{
		echo $this->getContent();
	}
	
	
	/**
	 * Read template file
	 *
	 * @return string
	 */
	public function read($template, $parsePHP = true)
	{
		$vpath = $this->getPath();
		$vfile = $vpath . $template;
		
		// Empty template name
		if(empty($vpath)) {
			throw new Cx_Exception_View("Base template path is not set!  Use '\$view->setPath('/path/to/template')' to set root path to template files!");
		}
		
		// Ensure template file exists
		if(!file_exists($vfile)) {
			throw new Cx_Exception_View("The template file '" . $template . "' does not exist.<br />Path: " . $vpath);
		}
		
		// Include() and parse PHP code
		if($parsePHP) {
			// Extract allowed variables for template scope
			if(is_array($this->_vars)) {
				extract($this->_vars);
			}
			
			ob_start();
			include($vfile);
			$templateContent = ob_get_contents();
			ob_end_clean();
		} else {
			// Just get raw file contents
			$templateContent = file_get_contents($vfile);
		}
			
		return $templateContent;
	}
	
	
	/**
	 * Get stored content or read template content
	 * 
	 * @return string
	 */
	public function getContent()
	{
		$templateFile = $this->getTemplateFilename();
		$output = $this->read($templateFile);
		
		return $output;
	}
}