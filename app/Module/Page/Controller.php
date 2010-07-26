<?php
/**
 * Page controller - sets up whole page for display
 */
class Module_Page_Controller extends Cx_Module_Controller_Abstract
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request)
	{
		return $this->viewUrl($request->page);
	}
	
	
	/**
	 * View page by URL
	 */
	public function viewUrl($pageUrl)
	{
		$kernel = $this->kernel;
		$request = $kernel->request();
		$user = $kernel->user();
		
		// Ensure page exists
		$mapper = $kernel->mapper();
		$pageMapper = $kernel->mapper('Module_Page_Mapper');
		$pageUrl = Module_Page_Entity::formatPageUrl($pageUrl);
		$page = $pageMapper->getPageByUrl($pageUrl);
		if(!$page) {
			if($pageUrl == '/') {
				// Create new page for the homepage automatically if it does not exist
				$page = $pageMapper->get('Module_Page_Entity');
				$page->parent_id = 0;
				$page->title = "Home";
				$page->url = $pageUrl;
				$page->date_created = date($pageMapper->connection('Module_Page_Entity')->dateTime());
				$page->date_modified = $page->date_created;
				if(!$pageMapper->save($page)) {
					throw new Alloy_Exception_FileNotFound("Unable to automatically create homepage at '" . $pageUrl . "' - Please check data source permissions");
				}
			} else {
				throw new Alloy_Exception_FileNotFound("Page not found: '" . $pageUrl . "'");
			}
		}
		
		// Single module call?
		// @todo Check against matched route name instead of general request params (? - may restict query string params from being used)
		if($request->module_name && $request->module_action) {
			$moduleId = (int) $request->module_id;
			$moduleName = $request->module_name;
			$moduleAction = $request->module_action;
			
			if($moduleId == 0) {
				// Get new module entity, no ID supplied
				// @todo Possibly restrict callable action with ID of '0' to 'new', etc. because other functions may depend on saved and valid module record
				$module = $mapper->get('Module_Page_Module_Entity');
				$module->name = $request->name;
			} else {
				// Module belongs to current page
				$module = $page->modules->where(array('id' => $moduleId))->first();
			}
			
			// Setup dummy module object if there is none loaded
			if(!$module) {
				$module = $mapper->get('Module_Page_Module_Entity');
				$module->name = $request->name;
			}
			
			// Load requested module
			$moduleObject = $kernel->module($moduleName);
			
			// Ensure user can execute requested action
			if(!$moduleObject->userCanExecute($user, $moduleAction)) {
				throw new Alloy_Exception_Auth("User does not have sufficient permissions to execute requested action (" . $moduleAction . "). Please login and try again.");
			}
			
			// Dispatch to single module
			$moduleResponse = $kernel->dispatchRequest($request, $moduleObject, $moduleAction, array($request, $page, $module));
			
			// Return content immediately, currently not wrapped in template
			return $this->regionModuleFormat($request, $page, $module, $user, $moduleResponse);
		}
		
		// Load page template
		$activeTheme = ($page->theme) ? $page->theme : $kernel->config('default.theme');
		$activeTemplate = ($page->template) ? $page->template : $kernel->config('default.theme_template');
		$themeUrl = $kernel->config('url.themes') . $activeTheme . '/';
		$template = new Module_Page_Template($activeTemplate);
		$template->format($request->format);
		$template->path($kernel->config('path.themes') . $activeTheme . '/');
		$template->parse();
		
		// Template Region Defaults
		$regionModules = array();
		foreach($template->regions() as $regionName => $regionData) {
			$regionModules[$regionName] = $regionData['content'];
		}
		
		// Modules
		$modules = $page->modules;
        // Also include modules in global template regions if global regions are present
        if($template->regionsType('global')) {
            $modules->orWhere(array('region' => $template->regionsType('global')));
        }
		foreach($modules as $module) {
			// Loop over modules, building content for each region
			$moduleResponse = $kernel->dispatch($module->name, 'indexAction', array($request, $page, $module));
			if(!is_array($regionModules[$module->region])) {
				$regionModules[$module->region] = array();
			}
			$regionModules[$module->region][] = $this->regionModuleFormat($request, $page, $module, $user, $moduleResponse);
		}
		
		// Replace region content
		$kernel->trigger('module_page_regions', array(&$regionModules));
		foreach($regionModules as $region => $modules) {
			if(is_array($modules)) {
				// Array = Region has modules
				$regionContent = implode("\n", $modules);
			} else {
				// Use default content between tags in template (no other content)
				$regionContent = (string) $modules;
			}
			$template->replaceRegion($region, $this->regionFormat($request, $region, $regionContent));
		}
		
		// Replace template tags
		$tags = $mapper->data($page);
		$kernel->trigger('module_page_tags', array(&$tags));
		foreach($tags as $tagName => $tagValue) {
			$template->replaceTag($tagName, $tagValue);
		}
		
		// Template string content
		$template->clean(); // Remove all unmatched tokens
		$templateHead = $template->head();
		
		// Admin stuff for HTML format
		if($template->format() == 'html') {
			// Add user and admin stuff to the page
			if($user->isAdmin()) {
				// Admin toolbar, javascript, styles, etc.
				$templateHead->script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
				$templateHead->script('jquery-ui.min.js');
				
				// Setup javascript variables for use
				$templateHead->prepend('<script type="text/javascript">var cx = {page: {id: ' . $page->id . ', url: "' . $pageUrl . '"}, config: {url: "' . $this->kernel->config('url.root') . '", url_assets: "' . $this->kernel->config('url.assets') . '", url_assets_admin: "' . $this->kernel->config('url.assets_admin') . '"}};</script>' . "\n");
				$templateHead->script($this->kernel->config('url.assets_admin') . 'scripts/cx_admin.js');
				$templateHead->script($this->kernel->config('url.assets_admin') . 'jHtmlArea/scripts/jHtmlArea-0.7.0.min.js');
				$templateHead->stylesheet('jquery-ui/base/jquery.ui.all.css');
				$templateHead->stylesheet($this->kernel->config('url.assets_admin') . 'styles/cx_admin.css');
				$templateHead->stylesheet($this->kernel->config('url.assets_admin') . 'jHtmlArea/styles/jHtmlArea.css');
				
				// Grab template contents
				$template = $template->content();
				
				// Render head
				$template = str_replace("</head>", $templateHead->content() . "\n</head>", $template);
				
				// Admin bar and edit controls
				$templateBodyContent = $this->view('_adminBar')->set('page', $page);
				$template = str_replace("</body>", $templateBodyContent . "\n</body>", $template);
			}
			
			// Prepend asset path to beginning of "href" or "src" attributes that is prefixed with '@'
			$template = preg_replace("/<(.*?)([src|href]+)=\"@([^\"|:]+)\"([^>]*)>/i", "<$1$2=\"".$themeUrl."$3\"$4>", $template);
			// Replace '!' prepend with web URL root
			$template = preg_replace("/<(.*?)([src|href]+)=\"!([^\"|:]*)\"([^>]*)>/i", "<$1$2=\"".$this->kernel->config('url.root')."$3\"$4>", $template);
		} else {
			// Other output formats not supported at this time
			throw new Alloy_Exception_FileNotFound("Page not found");
		}
		
		return $template;
	}
	
	
	/**
	 * @method GET
	 */
	public function newAction($request)
	{
		return $this->formView()
			->method('post')
			->action($this->kernel->url('page', array('page' => '/')));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request)
	{
		$kernel = $this->kernel;
		
		// Ensure page exists
		$mapper = $this->mapper();
		$page = $mapper->getPageByUrl($request->page);
		if(!$page) {
			throw new Alloy_Exception_FileNotFound("Page not found: '" . $request->page . "'");
		}
		
		return $this->formView()->data($page->data());
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request)
	{
		$mapper = $this->mapper();
		$entity = $mapper->get()->data($request->post());
		$entity->parent_id = (int) $request->parent_id;
		$entity->date_created = date($mapper->adapter()->dateTimeFormat());
		$entity->date_modified = $entity->date_created;
		
		// Auto-genereate URL if not filled in
		if(!$request->url) {
			$entity->url = $this->kernel->formatUrl($request->title);
		}
		if($mapper->save($entity)) {
			$pageUrl = $this->kernel->url('page', array('page' => $entity->url));
			if($request->format == 'html') {
				return $this->kernel->redirect($pageUrl);
			} else {
				return $this->kernel->resource($entity)->status(201)->location($pageUrl);
			}
		} else {
			$this->kernel->response(400);
			return $this->newAction($request)
				->data($request->post())
				->errors($mapper->errors());
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
			throw new Alloy_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($url) . "'");
		}
		
		$this->mapper()->delete($page);
	}
	
	
	/**
	 * @method GET
	 */
	public function sitemapAction($request)
	{
		$kernel = $this->kernel;
		
		// Ensure page exists
		$mapper = $this->mapper();
		$page = $mapper->getPageByUrl($request->url);
		if(!$page) {
			throw new Alloy_Exception_FileNotFound("Page not found: '" . $request->url . "'");
		}
		
		$pages = $mapper->pageTree();
		
		
		return $this->view(__FUNCTION__)
			->format($request->format)
			->set(array('pages' => $pages));
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$view = parent::formView();
		$fields = $view->fields();
		
		// Override int 'parent_id' with option select box
		$fields['parent_id']['type'] = 'select';
		$fields['parent_id']['options'] = array(0 => '[None]') + $this->mapper()->all()->order(array('ordering' => 'ASC'))->toArray('id', 'title');
		$fields['parent_id']['title'] = 'Parent Page';
		
		// Prepare view
		$view->action("")
			->fields($fields)
			->removeFields(array('id', 'theme', 'template', 'ordering', 'date_created', 'date_modified'));
		return $view;
	}
	
	
	
	/**
	 * Format region return content for display on page response
	 */
	protected function regionFormat($request, $regionName, $regionContent)
	{
		if('html' == $request->format) {
			$content = '<div id="cx_region_' . $regionName . '" class="cx_region">' . $regionContent . '</div>';
		}
		return $content;
	}
	
	
	/**
	 * Format module return content for display on page response
	 */
	protected function regionModuleFormat($request, $page, $module, $user, $moduleResponse, $includeControls = true)
	{
		$content = "";
		if(false !== $moduleResponse) {
			if('html' == $request->format) {
				// Module placeholder
				if(true === $moduleResponse || empty($moduleResponse)) {
					$moduleResponse = "<p>&lt;" . $module->name . " Placeholder&gt;</p>";
				}
				$content = '
				<div id="cx_module_' . $module->id . '" class="cx_module cx_module_' . $module->name . '">
				  ' . $moduleResponse;
				// Show controls only for authorized users and requests that are not AJAX
				if($includeControls && $user->isAdmin()) {
					$content .= '
				  <div class="cx_ui cx_ui_modulebar"><span>' . $module->name . ' Module</span></div>
				  <div class="cx_ui cx_ui_controls">
					<ul>
					  <li><a href="' . $this->kernel->url('module', array('page' => $page->url, 'module_name' => ($module->name) ? $module->name : $this->name(), 'module_id' => (int) $module->id, 'module_action' => 'edit')) . '">Edit</a></li>
					  <li><a href="' . $this->kernel->url('module_item', array('page' => $page->url, 'module_name' => 'Page_Module', 'module_id' => 0, 'module_item' => (int) $module->id, 'module_action' => 'delete')) . '">Delete</a></li>
					</ul>
				  </div>';
				}
				$content .= '</div>';
			}
		}
		return $content;
	}
}
