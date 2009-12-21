<?php
/**
 * $Id$
 */
class Module_Page_Controller extends Cx_Module_Controller
{
	/**
	 * Display current page
	 */
	public function indexAction($request)
	{
		// Load page template for parsing
		$this->mapper()->getPageByUrl($request->url);
		
		return "Awesome!<br />" . __FILE__;
	}
}