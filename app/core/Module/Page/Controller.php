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
		return $this->view($request->url);
	}
	
	
	/**
	 * View page by URL
	 */
	public function view($url)
	{
		$cx = $this->cx;
		
		// Ensure page exists
		$page = $this->mapper()->getPageByUrl($url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($request->url) . "'");
		}
		
		// Load page template
		$activeTheme = ($page->theme) ? $page->theme : $cx->config('cx.default.theme');
		$activeTemplate = ($page->template) ? $page->template : $cx->config('cx.default.theme_template');
		$template = new Module_Page_Template($cx->config('cx.path_themes') . $activeTheme . $activeTemplate);
		$regions = $template->getRegions();
	}
}