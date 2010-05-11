<?php
/**
 * Navigation Module
 */
class Module_Navigation_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		$pages = $this->mapper('Module_Page')->pageTree();
		
		return $this->view(__FUNCTION__)
			->set(array('pages' => $pages));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		return "There are currently no editable options for navigation display.";
	}
}