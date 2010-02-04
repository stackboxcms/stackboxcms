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
	public function indexAction($request, $page, $module)
	{
		$text = $this->mapper()->first(array('module_id' => $module->id));
		if(!$text) {
			return false;
		}
		
		// Return only content for HTML
		if($request->format == 'html') {
			return $text->content;
		}
		
		return $this->cx->resource($text);
		//return $this->view(__FUNCTION__);
	}
	
	public function editAction($request, $page, $module) {
		
	}
	public function deleteAction($request, $page, $module) {
		
	}
}