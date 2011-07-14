<?php
namespace Module\Page;
use Stackbox, Alloy;
use Alloy\Request;

/**
 * Page controller - sets up whole page for display
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    protected $_path = __DIR__;
    
    /**
     * @method GET
     */
    public function indexAction(Request $request)
    {
        $kernel = $this->kernel;

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
        $pageMapper = $kernel->mapper('Module\Page\Mapper');
        $pageUrl = Entity::formatPageUrl($pageUrl);
        $page = $pageMapper->getPageByUrl($pageUrl);
        if(!$page) {
            if($pageUrl == '/') {
                // Create new page for the homepage automatically if it does not exist
                $page = $pageMapper->get('Module\Page\Entity');
                $page->site_id = $kernel->config('cms.site.id');
                $page->parent_id = 0;
                $page->title = "Home";
                $page->url = $pageUrl;
                $page->date_created = $pageMapper->connection('Module\Page\Entity')->dateTime();
                $page->date_modified = $page->date_created;
                if(!$pageMapper->save($page)) {
                    throw new Alloy\Exception("Unable to automatically create homepage at '" . $pageUrl . "' - Please check data source permissions");
                }
            } else {
                throw new Alloy\Exception\FileNotFound("Page not found: '" . $pageUrl . "'");
            }
        }

        // Single module call?
        // @todo Check against matched route name instead of general request params (? - may restict query string params from being used)
        $mainContent = false;
        if($request->module_name && $request->module_action) {
            $moduleId = (int) $request->module_id;
            $moduleName = $request->module_name;
            $moduleAction = $request->module_action;
            
            if($moduleId) {
                // Get module by ID
                $module = $mapper->first('Module\Page\Module\Entity', array('id' => $moduleId));
            } else {
                // Get new module entity, no ID supplied
                // @todo Possibly restrict callable action with ID of '0' to 'new', etc. because other functions may depend on saved and valid module record
                $module = $mapper->get('Module\Page\Module\Entity');
                $module->id = $moduleId;
                $module->name = $moduleName;
            }
            
            // Setup dummy module object if there is none loaded
            if(!$module) {
                $module = $mapper->get('Module\Page\Module\Entity');
                $module->id = $moduleId;
                $module->name = $moduleName;
            }
            
            // Load requested module
            $moduleObject = $kernel->module($moduleName);

            // Ensure module is a placeable module on the page
            if(!($moduleObject instanceof Stackbox\Module\ControllerAbstract)) {
                throw new Alloy\Exception("Module '" . $moduleName . "' must extend 'Stackbox\Module\ControllerAbstract' to be a placeable Stackbox module");
            }

            // Ensure user can execute requested action
            if(!$moduleObject->userCanExecute($user, $moduleAction)) {
                throw new Alloy\Exception\Auth("User does not have sufficient permissions to execute requested action. Please login and try again.");
            }
            
            // Emulate REST for browsers
            $requestMethod = $request->method();
            if($request->isPost() && $request->post('_method')) {
                $requestMethod = $request->post('_method');
            }
            
            // Append 'Action' or 'Method' depending on HTTP method
            if(strtolower($requestMethod) == strtolower($moduleAction)) {
                $moduleAction = $moduleAction . (false === strpos($moduleAction, 'Method') ? 'Method' : '');
            } else {
                $moduleAction = $moduleAction . (false === strpos($moduleAction, 'Action') ? 'Action' : '');
            }

            // Dispatch to single module
            if(!is_callable(array($moduleObject, $moduleAction))) {
                throw new \BadMethodCallException("Module '" . $moduleName ."' does not have a callable method '" . $moduleAction . "'");
            }
            $moduleResponse = $kernel->dispatch($moduleObject, $moduleAction, array($request, $page, $module));
            
            // Set content as main content (detail view)
            $mainContent = $this->regionModuleFormat($request, $page, $module, $user, $moduleResponse);

            // Return content immediately if ajax request
            if($request->isAjax()) {
                return $mainContent;
            }
        }
        
        // Load page template
        $activeTheme = ($page->theme) ? $page->theme : $kernel->config('cms.site.theme', $kernel->config('cms.default.theme'));
        // Default template or page template
        if(!$page->template) {
            $activeTemplate = $activeTheme . '/' . $kernel->config('cms.default.theme_template');
        } else {
            $activeTemplate = $page->template;   
        }
        $activeTheme = current(explode('/', $activeTemplate));
        $themeUrl = $kernel->config('cms.url.themes') . $activeTheme . '/';
        $template = new Template($activeTemplate);
        $template->format($request->format);
        $template->path($kernel->config('cms.path.themes'));

        // Ensure template exists and set to default if not
        // @todo Also display warning for logged-in users that page fell back to default template
        if(!$template->exists()) {
            $template->file($kernel->config('cms.default.theme') . '/' . $kernel->config('cms.default.theme_template'), 'html');
        }

        $template->parse();

        // Add jQuery as the first item
        $templateHead = $template->head();
        $templateHead->script($kernel->config('cms.url.assets') . 'jquery.min.js');
        
        // Template Region Defaults
        $regionModules = array();
        $mainRegion = $template->regionMain();
        $mainRegionName = $mainRegion['name'];
        foreach($template->regions() as $regionName => $regionData) {
            $regionModules[$regionName] = $regionData['content'];
        }
        
        // Modules
        $modules = $page->modules;
        $unusedModules = array();
        
        // Also include modules in global template regions if global regions are present
        if($template->regionsType('global')) {
            $modules->orWhere(array('site_id' => $kernel->config('cms.site.id'), 'region' => $template->regionsType('global')));
        }
        foreach($modules as $module) {
            // Loop over modules, building content for each region
            $moduleResponse = $kernel->dispatch($module->name, 'indexAction', array($request, $page, $module));
            if(!isset($regionModules[$module->region]) || !is_array($regionModules[$module->region])) {
                $regionModules[$module->region] = array();
            }
            
            // If we have a 'main' module render, don't dispatch/render other content in main region
            if(false !== $mainContent) {
                // If module goes in 'main' region, skip it
                if($mainRegionName == $module->region) {
                    continue;
                }
            }

            // Dispatch to content modules inside regions to render their contents
            $regionModules[$module->region][] = $this->regionModuleFormat($request, $page, $module, $user, $moduleResponse);
        }

        // Replace main region content if set
        if(false !== $mainContent) {
            $regionModules[$mainRegionName] = array($mainContent);
        }
        
        // Replace region content
        $regionModules = $kernel->events('cms')->filter('module_page_template_regions', $regionModules);
        foreach($regionModules as $region => $modules) {
            if(is_array($modules)) {
                // Array = Region has modules
                $regionContent = implode("\n", $modules);
            } else {
                // Use default content between tags in template (no other content)
                $regionContent = (string) $modules;
            }
            $template->regionContent($region, $regionContent);
        }
        
        // Replace template tags
        $tags = $mapper->data($page);
        $tags = $kernel->events('cms')->filter('module_page_template_data', $tags);
        foreach($tags as $tagName => $tagValue) {
            $template->replaceTag($tagName, $tagValue);
        }
        
        // Template string content
        $template->clean(); // Remove all unmatched tokens
        
        // Admin stuff for HTML format
        if($template->format() == 'html') {

            // Add user and admin stuff to the page
            if($user && $user->isAdmin()) {
                // Admin toolbar, javascript, styles, etc.
                $templateHead->script($kernel->config('cms.url.assets') . 'jquery-ui.min.js');
                
                // Setup javascript variables for use
                $templateHead->prepend('<script type="text/javascript">
                var cms = {
                    page: {id: ' . $page->id . ', url: "' . $pageUrl . '"},
                    config: {url: "' . $kernel->config('url.root') . '", url_assets: "' . $kernel->config('url.assets') . '", url_assets_admin: "' . $kernel->config('cms.url.assets_admin') . '"},
                    editor: {
                        fileUploadUrl: "' . $kernel->url(array('action' => 'upload'), 'filebrowser', array('type' => 'file')) . '",
                        imageUploadUrl: "' . $kernel->url(array('action' => 'upload'), 'filebrowser', array('type' => 'image')) . '",
                        fileBrowseUrl: "' . $kernel->url(array('action' => 'files'), 'filebrowser') . '",
                        imageBrowseUrl: "' . $kernel->url(array('action' => 'images'), 'filebrowser') . '"
                    }
                };
                </script>' . "\n");
                $templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/ckeditor/ckeditor.js');
                $templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/ckeditor/adapters/jquery.js');
                $templateHead->script($kernel->config('cms.url.assets_admin') . 'scripts/cms_admin.js');
                $templateHead->stylesheet('jquery-ui/aristo/aristo.css');
                $templateHead->stylesheet($kernel->config('cms.url.assets_admin') . 'styles/cms_admin.css');
                
                // Grab template contents
                $template = $template->content();
                
                // Admin bar and edit controls
                $templateBodyContent = $this->template('_adminBar')
                    ->path(__DIR__ . '/views/')
                    ->set('page', $page);
                $template = str_replace("</body>", $templateBodyContent . "\n</body>", $template);
            }
            
            // Global styles and scripts
            $templateHead->stylesheet($kernel->config('url.assets') . 'styles/cms_modules.css');
            
            // Render head
            $template = str_replace("</head>", $templateHead->content() . "\n</head>", $template);
            
            // Prepend asset path to beginning of "href" or "src" attributes that is prefixed with '@'
            $template = preg_replace("/<(.*?)([src|href]+)=\"@([^\"|:]+)\"([^>]*)>/i", "<$1$2=\"".$themeUrl."$3\"$4>", $template);
            // Replace '!' prepend with web URL root
            $template = preg_replace("/<(.*?)([src|href]+)=\"!([^\"|:]*)\"([^>]*)>/i", "<$1$2=\"".$this->kernel->config('url.root')."$3\"$4>", $template);
        } else {
            // Other output formats not supported at this time
            return false; // 404
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
            ->action($this->kernel->url(array('page' => '/'), 'page'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request)
    {
        $kernel = $this->kernel;
        
        // Ensure page exists
        $mapper = $this->kernel->mapper('Module\Page\Mapper');
        $page = $mapper->getPageByUrl($request->page);
        if(!$page) {
            return false;
        }
        
        return $this->formView()->data($page->toArray());
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request)
    {
        $mapper = $this->kernel->mapper('Module\Page\Mapper');
        $entity = $mapper->get('Module\Page\Entity')->data($request->post());
        $entity->site_id = $this->kernel->config('cms.site.id');
        $entity->parent_id = (int) $request->parent_id;
        $entity->date_created = $mapper->connection('Module\Page\Entity')->dateTime();
        $entity->date_modified = $entity->date_created;
        
        // Auto-genereate URL if not filled in
        if(!$request->url) {
            $entity->url = $this->kernel->formatUrl($request->title);
        }
        if($mapper->save($entity)) {
            $pageUrl = $this->kernel->url(array('page' => $entity->url), 'page');
            if($request->format == 'html') {
                return $this->kernel->redirect($pageUrl);
            } else {
                return $this->kernel->resource($entity)->status(201)->location($pageUrl);
            }
        } else {
            return $this->newAction($request)
                ->data($request->post())
                ->status(400)
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method GET
     */
    public function deleteAction($request)
    {
        if($request->format == 'html') {
            $view = new \Alloy\View\Generic\Form('form');
            $form = $view
                ->method('delete')
                ->action($this->kernel->url(array('page' => $request->page), 'page'))
                ->submit('Delete');
            return "<p>Are you sure you want to delete this page and ALL data and modules on it?</p>" . $form;
        }
        return false;
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request)
    {
        // Ensure page exists
        $mapper = $this->kernel->mapper('Module\Page\Mapper');
        $page = $mapper->getPageByUrl($request->page);
        if(!$page) {
            return false;
        }
        
        $mapper->delete($page);
    }
    
    
    /**
     * @method GET
     */
    public function sitemapAction($request)
    {
        $kernel = $this->kernel;
        
        // Ensure page exists
        $mapper = $kernel->mapper('Module\Page\Mapper');
        $page = $mapper->getPageByUrl($request->url);
        if(!$page) {
            return false;
        }
        
        $pages = $mapper->pageTree();
        
        // View template
        return $this->template(__FUNCTION__)
            ->format($request->format)
            ->set(array('pages' => $pages));
    }


    /**
     * Pages display for admins to sort pages with
     * 
     * @method GET
     */
    public function pagesAction($request)
    {
        $kernel = $this->kernel;
        $mapper = $kernel->mapper('Module\Page\Mapper');

        // Save page order (change parents)
        if($request->isPost()) {
            // Save page order
            $mapper->savePageOrder($request->pages);
            // @todo Need to force a page reload somehow so nav will reflect changes
            return "Saved";
        }
        
        // Ensure page exists
        $page = $mapper->getPageByUrl($request->url);
        if(!$page) {
            return false;
        }

        // All pages in tree form
        $pages = $mapper->pageTree();
        
        // View template
        return $this->template(__FUNCTION__)
            ->format($request->format)
            ->set(compact('page', 'pages'));
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
        // Get all pages for site
        $fields['parent_id']['options'] = array(0 => '[None]') + $this->kernel->mapper('Module\Page\Mapper')->all('Module\Page\Entity', array(
                'site_id' => $this->kernel->config('cms.site.id')
            ))->order(array(
                'ordering' => 'ASC'
            ))->toArray('id', 'title'); // Return records in 'id' => 'title' key/value array
        $fields['parent_id']['title'] = 'Parent Page';

        // Add field to select page template
        $fields['template']['type'] = 'select';
        $fields['template']['options'] = Entity::getPageTemplates();

        // Update 'visibility' field to set allowed options
        $fields['visibility']['type'] = 'select';
        $fields['visibility']['options'] = array(
            Entity::VISIBILITY_VISIBLE => 'Visible in Navigation',
            Entity::VISIBILITY_HIDDEN => 'Hidden from Navigation'
        );
        
        // Prepare view
        $view->action("")
            ->method('post')
            ->fields($fields)
            ->removeFields(array('id', 'ordering', 'date_created', 'date_modified'));
        return $view;
    }
    
    
    /**
     * Format module return content for display on page response
     */
    protected function regionModuleFormat($request, $page, $module, $user, $moduleResponse, $includeControls = true)
    {
        $content = "";
        if(false === $moduleResponse) {
            $content = false;
        } else {
            if('html' == $request->format) {
                $content = '';

                // Module placeholder if empty response
                if(true === $moduleResponse || empty($moduleResponse)) {
                    $moduleResponse = "<p>&lt;" . $module->name . " Placeholder&gt;</p>";
                }

                // Alloy Module Response type
                if($moduleResponse instanceof \Alloy\Module\Response) {
                    // Pass HTTP status code
                    $response = $this->kernel->response($moduleResponse->status());

                    // Display errors
                    if($errors = $moduleResponse->errors()):
                      $content .= '<div class="app_errors"><ul>';
                      foreach($errors as $field => $fieldErrors):
                        foreach((array) $fieldErrors as $error):
                            $content .= '<li>' . $error . '</li>';
                        endforeach;
                      endforeach;
                      $content .= '</ul></div>';
                    endif;

                    // Call 'content' explicitly so Exceptions are not trapped in __toString
                    $moduleResponse = $moduleResponse->content();
                }

                // Build module HTML
                $content .= '
                <div id="cms_module_' . $module->id . '" class="cms_module module_' . strtolower($module->name) . '">
                  ' . $moduleResponse;
                // Show controls only for authorized users and requests that are not AJAX
                if($includeControls && false !== $user && $user->isAdmin()) {
                    $content .= '
                  <div class="cms_ui cms_ui_controls">
                    <div class="cms_ui_title"><span>' . $module->name . '</span></div>
                    <ul>
                      <li><a href="' . $this->kernel->url(array('page' => $page->url, 'module_name' => ($module->name) ? $module->name : $this->name(), 'module_id' => (int) $module->id, 'module_action' => 'editlist'), 'module') . '">Edit</a></li>';
                      
                      // Options submenu
                      $content .= '
                      <li class="cms_ui_controls_menu">
                      <a href="#" class="cms_ui_controls_menu_link"><i></i></a>
                       <ul>';
                      // Only if module is really on page...
                      if($module->id > 0) {
                        // Settings link
                        $content .= '
                        <li><a href="' . $this->kernel->url(array('page' => $page->url, 'module_name' => ($module->name) ? $module->name : $this->name(), 'module_id' => (int) $module->id, 'module_action' => 'settings'), 'module') . '">Settings</a></li>';
                        $content .= '
                        <li><a href="' . $this->kernel->url(array('page' => $page->url, 'module_name' => 'page_module', 'module_id' => 0, 'module_item' => (int) $module->id, 'module_action' => 'delete'), 'module_item') . '">Delete</a></li>';
                      }
                      $content .= '
                       </ul>
                      </li>
                    </ul>
                  </div>
                  ';
                }
                $content .= '</div>';
            }
        }
        return $content;
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\Controller\Abstract
     */
    public function install($action = null, array $params = array())
    {
        // Site
        $this->kernel->mapper()->migrate('Module\Site\Entity');
        $this->kernel->mapper()->migrate('Module\Site\Domain');

        // Page
        $this->kernel->mapper()->migrate('Module\Page\Entity');
        $this->kernel->mapper()->migrate('Module\Page\Module\Entity');
        $this->kernel->mapper()->migrate('Module\Page\Module\Settings\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\Controller\Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Page\Entity');
    }
}