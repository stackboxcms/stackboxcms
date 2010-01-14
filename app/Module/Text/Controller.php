<?php
/**
 * Text Module
 */
class Module_Text_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * GET
	 */
	public function indexAction($request, Module_Page_Entity $page)
	{
		$view = new Cx_View_Generic_Form($this->mapper());
		return $view;
		//return $this->view(__FUNCTION__);
	}
	
	public function editAction($request, Module_Page_Entity $page) {
		
	}
	public function deleteAction($request, Module_Page_Entity $page) {}
}