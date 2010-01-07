<?php
/**
 * $Id$
 */
class Module_Page_Mapper extends Cx_Mapper
{
	// Custom row class
	protected $rowClass = 'Module_Page_Entity';
	
	// Setup table and fields
	protected $source = "pages";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true);
	public $title = array('type' => 'string', 'required' => true);
	public $url = array('type' => 'string', 'key' => true, 'required' => true);
	public $meta_keywords = array('type' => 'string');
	public $meta_description = array('type' => 'string');
	public $theme = array('type' => 'string');
	public $template = array('type' => 'string');
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
	
	
	/**
	 * Get current page by given URL
	 */
	public function getPageByUrl($url)
	{
		return $this->first(array('url' => $this->formatPageUrl($url)));
	}
	
	
	/**
	 * Format a page URL by ensuring there is a begining and ending slash
	 *
	 * @param string $url
	 * @return string
	 */
	public function formatPageUrl($url)
	{
		// Prepended slash
		if(strpos($url, '/') !== 0) {
			$url = '/' . $url;
		}
		
		// Appended slash
		if(substr($url, strlen($url)-1, 1) != "/") {
			$url .= '/';
		}
		
		return $url;
	}
}


// Custom entity object
class Module_Page_Entity extends phpDataMapper_Entity
{
	
}