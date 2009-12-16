<?php
/**
 * $Id$
 */
class Page_Module extends Cx_Module
{
	/**
	 * Display current page
	 */
	public function indexAction($request)
	{
		// Load page template for parsing
		//$this->model()->getPageByUrl($request->uri());
		
		return "Awesome!<br />" . __FILE__;
	}
}