<?php
class Module_Page_Mapper extends Cx_Mapper
{
	// Table
	protected $source = "pages";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $title = array('type' => 'string', 'required' => true);
	public $url = array('type' => 'string', 'required' => true, 'unique' => true);
	public $meta_keywords = array('type' => 'string');
	public $meta_description = array('type' => 'string');
	public $theme = array('type' => 'string');
	public $template = array('type' => 'string');
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
	
	// Relations
	public $modules = array(
		'type' => 'relation',
		'relation' => 'HasMany',
		'mapper' => 'Module_Page_Module_Mapper',
		'where' => array('page_id' => 'entity.id'),
		'order' => array('ordering' => 'ASC')
		);
	
	// Custom entity class
	protected $_entityClass = 'Module_Page_Entity';

	
	/**
	 * Get current page by given URL
	 *
	 * @param string $url
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
		if(empty($url)) {
			$url = '/';
		} elseif($url != '/') {
			$url = '/' . trim($url, '/') . '/';
		}
		return $url;
	}
}