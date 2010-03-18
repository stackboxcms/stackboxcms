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
		throw new Cx_Exception_Http("Navigation module is not editable", 500);
	}
}