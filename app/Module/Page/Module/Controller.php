<?php
namespace Module\Page\Module;

/**
 * Page module controller - Add, move, or delete modules
 */
class Controller extends \Cx\Module\ControllerAbstract
{
    /**
     * Module listing
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        return false;
    }
    
    
    /**
     * @method GET
     */
    public function newAction($request, $page, $module)
    {
        return $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('page' => '/'), 'page'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $kernel = $this->kernel;
        
        return $this->formView();
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, \Module\Page\Module\Entity $module)
    {
        $kernel = $this->kernel;
        
        // @todo Attempt to load module before saving it so we know it will work
        
        // Save it
        $mapper = $kernel->mapper();
        $entity = $mapper->get('Module\Page\Module\Entity')
            ->data($request->post() + array(
                'site_id' => $page->site_id,
                'page_id' => $page->id,
                'date_created' => $mapper->connection('Module\Page\Module\Entity')->dateTime()
            ));
        if($mapper->save($entity)) {
            $pageUrl = $this->kernel->url(array('page' => $page->url), 'page');
            if($request->format == 'html') {
                // Set module data for return content
                $mapper->data($module, $mapper->data($entity));
                // Dispatch to return module content
                return $kernel->dispatch($entity->name, 'indexAction', array($request, $page, $entity));
            } else {
                return $this->kernel->resource($entity)->status(201)->location($pageUrl);
            }
        } else {
            $this->kernel->response(400);
            return "<h1>ERROR!</h1>" . (string) $this->formView()
                ->data($request->post())
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method GET
     */
    public function deleteAction($request, $page, $module)
    {
        if($request->format == 'html') {
            $view = new \Alloy\View\Generic\Form('form');
            $form = $view
                ->method('delete')
                ->action($this->kernel->url(array('page' => '/', 'module_name' => $this->name(), 'module_id' => 0, 'module_item' => $request->module_item), 'module_item'))
                ->data(array('item_dom_id' => 'cx_module_' . $request->module_item))
                ->submitButtonText('Delete');
            return "<p>Are you sure you want to delete this module?</p>" . $form;
        }
        return false;
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $item = $this->kernel->mapper()->get('Module\Page\Module\Entity', $request->module_item);
        if($item) {
            $this->kernel->mapper()->delete($item);
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Save module sorting
     * @method POST
     */
    public function saveSortAction($request, $page, $module)
    {
        if($request->modules && is_array($request->modules)) {
            $mapper = $this->kernel->mapper();
            foreach($request->modules as $regionName => $modules) {
                foreach($modules as $orderIndex => $moduleId) {
                    $item = $mapper->get('Module\Page\Module\Entity', $moduleId);
                    if($item) {
                        $item->region = $regionName;
                        $item->ordering = $orderIndex;
                        $mapper->save($item);
                    }
                }
            }
        }
        return true;
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        $view = new \Alloy\View\Generic\Form('form');
        $view->action("")
            ->fields($this->kernel->mapper()->fields('Module\Page\Module\Entity'))
            ->removeFields(array('id', 'date_created', 'date_modified'));
        return $view;
    }
    
    
    /**
     * Install Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Page\Module\Entity');
        return parent::install($action);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Page\Module\Entity');
    }
}