<?php
/**
 * Page controller - sets up whole page for display
 */
class Module_Page_Controller extends Cx_Module_Controller
{
	/**
	 * @method GET
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
	
	
	/**
	 * @method GET
	 */
	public function newAction($request)
	{
		$postUrl = $this->cx->router()->url('page', array('url' => '/'));
		return $this->formView()->method('post')->action($postUrl);
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request)
	{
		return $this->formView();
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request)
	{
		$mapper = $this->mapper();
		$entity = $mapper->get()->data($request->post());
		if($mapper->save($entity)) {
			return $this->cx->resource($entity)->status(201);
		} else {
			$cx->response(400);
			return $this->formView()->errors($mapper->getErrors());
		}
	}
	
	
	/**
	 * @method DELETE
	 */
	public function deleteMethod($request)
	{
		// Ensure page exists
		$page = $this->mapper()->getPageByUrl($request->url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($url) . "'");
		}
		
		$this->mapper()->delete($page);
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$view = new Cx_View_Generic_Form($this->cx);
		$view->action("")
			->fields($this->mapper()->fields())
			->removeFields(array('id', 'date_created', 'date_modified'));
		return $view;
	}
}