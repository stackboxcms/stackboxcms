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
			var_dump($page);
			$this->mapper()->debug();
			throw new Cx_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($cx->request()->url) . "'");
		}
		
		// Load page template
		$activeTheme = ($page->theme) ? $page->theme : $cx->config('cx.default.theme');
		$activeTemplate = (($page->template) ? $page->template : $cx->config('cx.default.theme_template')) . ".tpl.html";
		$template = new Module_Page_Template($cx->config('cx.path_themes') . $activeTheme . '/' . $activeTemplate);
		$regions = $template->regions();
		$tags = $template->tags();
		
		return "Here: " . __FILE__;
	}
}