<?php
/**
 * $Id$
 */
class Module_Page_Mapper extends phpDataMapper_Base
{
	// Custom row class
	protected $rowClass = 'Module_Page_Entity';
	
	// Setup table and fields
	protected $source = "pages";
	protected $fields = array(
		'id' => array('type' => 'int', 'primary' => true),
		'title' => array('type' => 'string', 'required' => true),
		'url' => array('type' => 'string', 'key' => true, 'required' => true),
		'meta_keywords' => array('type' => 'string'),
		'meta_description' => array('type' => 'string'),
		'theme' => array('type' => 'string'),
		'template' => array('type' => 'string'),
		'date_created' => array('type' => 'datetime'),
		'date_modified' => array('type' => 'datetime')
		);
	
	
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