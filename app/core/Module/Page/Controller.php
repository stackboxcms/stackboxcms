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
		// Ensure page exists
		$page = $this->mapper()->getPageByUrl($request->url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $request->url . "'");
		}
		
		// Load page template
		$template = new Module_Page_Template($page->template);
		$regions = $template->getRegions();
		
		return "Awesome!<br />" . __FILE__;
	}
}