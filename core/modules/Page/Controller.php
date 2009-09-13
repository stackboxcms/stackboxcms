<?php
/**
 * $Id$
 */
class Module_Page_Controller extends Cx_Controller
{
	/**
	 * Display current page
	 */
	public function indexAction()
	{
		$request = $this->request();
		
		// Load page template for parsing
		$this->model()->getPageByUrl($request->uri());
		
		// Prevent template from rendering
		$this->autoRender = false;
	}
}