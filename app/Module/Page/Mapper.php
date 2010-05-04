<?php
class Module_Page_Mapper extends Alloy_Mapper
{
	// Table
	protected $_datasource = "pages";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
	public $parent_id = array('type' => 'int', 'key' => true, 'default' => 0);
	public $title = array('type' => 'string', 'required' => true);
	public $url = array('type' => 'string', 'required' => true, 'unique' => true);
	public $meta_keywords = array('type' => 'string');
	public $meta_description = array('type' => 'string');
	public $theme = array('type' => 'string');
	public $template = array('type' => 'string');
	public $ordering = array('type' => 'int', 'length' => 3, 'default' => 0);
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
	
	// Subpages / hierarchy
	public $children = array(
		'type' => 'relation',
		'relation' => 'HasMany',
		'mapper' => 'Module_Page_Mapper',
		'where' => array('parent_id' => 'entity.id'),
		'order' => array('ordering' => 'ASC')
		);
	
	// Modules in regions on page
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
	 * Return full tree of pages with all children nested properly
	 *
	 * @param string $url
	 */
	public function pageTree($startPage = null)
	{
		if(null === $startPage) {
			$rootPages = $this->all(array('parent_id <' => 1))->order(array('ordering' => 'ASC'));
		} else {
			if($startPage instanceof Module_Page_Entity) {
				$rootPages = $startPage->children;
			} else {
				throw new Exception("Provided start page must be an instance of Module_Page_Entity");
			}
		}
		
		return $rootPages;
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