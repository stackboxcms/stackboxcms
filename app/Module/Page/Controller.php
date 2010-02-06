<?php
/**
 * Page controller - sets up whole page for display
 */
class Module_Page_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
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
		$request = $cx->request();
		
		// Ensure page exists
		$mapper = $this->mapper();
		$page = $mapper->getPageByUrl($url);
		if(!$page) {
			$pageUrl = $mapper->formatPageUrl($url);
			if($pageUrl == '/') {
				// Create new page for the homepage automatically if it does not exist
				$page = $mapper->get();
				$page->title = "Home";
				$page->url = $pageUrl;
				$page->date_created = date($mapper->adapter()->dateTimeFormat());
				$page->date_modified = $page->date_created;
				if(!$mapper->save($page)) {
					throw new Cx_Exception_FileNotFound("Unable to automatically create homepage at '" . $pageUrl . "' - Please check data source permissions");
				}
			} else {
				throw new Cx_Exception_FileNotFound("Page not found: '" . $pageUrl . "'");
			}
		}
		
		// Load page template
		$activeTheme = ($page->theme) ? $page->theme : $cx->config('cx.default.theme');
		$activeTemplate = ($page->template) ? $page->template : $cx->config('cx.default.theme_template');
		$template = new Module_Page_Template($activeTemplate);
		$template->format($request->format);
		$template->path($cx->config('cx.path_themes') . $activeTheme . '/');
		$template->parse();
		
		// Modules
		$regionModules = array();
		foreach($page->modules as $module) {
			// Loop over modules, building content for each region
			$moduleResponse = $cx->dispatch($module->name, 'indexAction', array($request, $page, $module));
			$regionModules[$module->region][] = $this->regionModuleFormat($request, $module, $moduleResponse);
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
		
		// Template string content
		$templateContent = $template->content();
		
		// Add admin stuff to the page
		// Admin toolbar, javascript, styles, etc.
		if($template->format() == 'html') {
			$templateHeadContent = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.0/jquery.min.js"></script>';
			$templateHeadContent = '<link type="text/css" href="' . $this->cx->config('cx.url_assets_admin') . 'styles/cx_admin.css" rel="stylesheet" />';
			$templateContent = str_replace("</head>", $templateHeadContent . "</head>", $templateContent);
			$templateBodyContent = $this->view('_adminBar');
			$templateContent = str_replace("</body>", $templateBodyContent . "\n</body>", $templateContent);
		}
		return $templateContent;
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
	
	
	/**
	 * Format module return content for display on page response
	 */
	protected function regionModuleFormat($request, $module, $moduleResponse)
	{
		$content = "";
		if(false !== $moduleResponse) {
			if($request->format == 'html') {
				$content = '<div id="cx_module_' . $module->id . '" class="cx_module cx_module_' . $module->name . '">' . $moduleResponse . '</div>';
			}
		}
		return $content;
	}
}