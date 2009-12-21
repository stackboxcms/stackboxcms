<?php
/**
 * $Id$
 */
class Module_Page_Mapper extends phpDataMapper_Base
{
	// Custom row class
	protected $rowClass = 'Page_Item';
	
	// Setup table and fields
	protected $source = "pages";
	protected $fields = array(
		'id' => array('type' => 'int', 'primary' => true),
		'title' => array('type' => 'string'),
		'url' => array('type' => 'string', 'key' => true, 'required' => true),
		'meta_keywords' => array('type' => 'string'),
		'meta_description' => array('type' => 'string'),
		'template' => array('type' => 'string'),
		'date_created' => array('type' => 'datetime'),
		'date_modified' => array('type' => 'datetime')
		);
	
	/**
	 * Get current page by given URL
	 */
	public function getPageByUrl($url)
	{
		return $this->first(array('url' => $url));
	}
}


// Custom row object
class Page_Item extends phpDataMapper_Entity
{
	
}