<?php
class Module_Code_Mapper extends Cx_Module_Mapper
{
	// Table
	protected $_datasource = "module_code";
	
	// Fields
	public $content = array('type' => 'text', 'required' => true);
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
	
	
	/**
	 * Get current text entity
	 */
	public function currentEntity(Module_Page_Module_Entity $module)
	{
		$item = $this->all(array('module_id' => $module->id))->order(array('id' => 'DESC'))->first();
		if(!$item) {
			$item = $this->get();
		}
		return $item;
	}
}