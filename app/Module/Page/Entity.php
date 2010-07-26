<?php
class Module_Page_Entity extends Cx_Module_Entity_Abstract
{
	// Table
	protected $_datasource = "pages";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
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
		'entity' => ':self',
		'where' => array('parent_id' => ':entity.id'),
		'order' => array('ordering' => 'ASC')
		);
	
	// Modules in regions on page
	public $modules = array(
		'type' => 'relation',
		'relation' => 'HasMany',
		'entity' => 'Module_Page_Module_Entity',
		'where' => array('page_id' => ':entity.id'),
		'order' => array('ordering' => 'ASC')
		);
	
	
	/**
	 * Formats URL on save
	 */
	public function beforeSave(Spot_Mapper $mapper)
	{
		$this->url = self::formatPageUrl($this->url);
	}
	
	
	/**
	 * Format a page URL by ensuring there is a begining and ending slash
	 *
	 * @param string $url
	 * @return string
	 */
	public static function formatPageUrl($url)
	{
		if(empty($url)) {
			$url = '/';
		} elseif($url != '/') {
			$url = '/' . trim($url, '/') . '/';
		}
		return $url;
	}
}