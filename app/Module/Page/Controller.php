<?php
/**
 * Page controller - sets up whole page for display
 */
class Module_Page_Controller extends Cx_Module_Controller
{
	/**
	 * GET
	 */
	public function indexAction($request)
	{
		return $this->viewUrl($request->url);
	}
	
	
	/**
	 * View page by URL
	 */
	public function viewUrl($url)
	{
		$cx = $this->cx;
		
		// Ensure page exists
		$page = $this->mapper()->getPageByUrl($url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($url) . "'");
		}
		
		// Load page template
		$activeTheme = ($page->theme) ? $page->theme : $cx->config('cx.default.theme');
		$activeTemplate = (($page->template) ? $page->template : $cx->config('cx.default.theme_template')) . ".tpl.html";
		$template = new Module_Page_Template($cx->config('cx.path_themes') . $activeTheme . '/' . $activeTemplate);
		$template->parse();
		//$templateRegions = $template->regions();
		//$templateTags = $template->tags();
		
		// Modules
		$regionModules = array();
		foreach($page->modules as $module) {
			// Loop over modules, building content for each region
			$regionModules[$module->region][] = $cx->dispatch($module->name, 'indexAction', array($cx->request(), $page));
		}
		
		// Replace region content
		$cx->trigger('module_page_regions', array(&$regionModules));
		foreach($regionModules as $region => $modules) {
			$template->replaceRegion($region, implode("\n", $modules));
		}
		
		// Replace template tags
		$tags = $page->toArray();
		$cx->trigger('module_page_tags', array(&$tags));
		foreach($tags as $tagName => $tagValue) {
			$template->replaceTag($tagName, $tagValue);
		}
		
		return $template;
	}
}