<?php
namespace Module\Blog\Post;

/**
 * Blog Module
 * Blog Posts Controller
 */
class Controller extends \Cx\Module\ControllerAbstract
{
    /**
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
        $form = $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module');
        return $this->template('editAction')->set(compact('form'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $form = $this->formView()
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module')
            ->method('PUT');
        
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post', $request->module_item);
        
        if(!$item) {
            return false;
        }
        
        // Set item data on form
        $form->data($item->data());
        
        // Return view template
        return $this->template(__FUNCTION__)->set(compact('form'));
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post')->data($request->post());
        $item->module_id = $module->id;
        $item->date_created = $mapper->connection('Module\Blog\Post')->dateTime();
        $item->date_modified = $mapper->connection('Module\Blog\Post')->dateTime();
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($mapper->data($item))->status(201)->location($itemUrl);
            }
        } else {
            $this->kernel->response(400);
            return $this->formView()->errors($mapper->errors());
        }
    }
    
    
    /**
     * Save over existing entry (from edit)
     * @method PUT
     */
    public function putMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post', $request->module_item);
        if(!$item) {
            throw new \Alloy\Exception_FileNotFound($this->name() . " module item not found");
        }
        $item->data($request->post());
        $item->module_id = $module->id;
        $item->date_modified = $mapper->connection('Module\Blog\Post')->dateTime();
        
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($mapper->data($item))->status(201)->location($itemUrl);
            }
        } else {
            $this->kernel->response(400);
            return $this->formView()->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post', $request->module_item);
        if(!$item) {
            return false;
        }
        return $mapper->delete($item);
    }
    
    
    /**
     * Install Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Blog\Post');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Blog\Post');
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        $view = parent::formView();
        $fields = $view->fields();
        
        // Set type and options for 'type' select
        $fields['status']['type'] = 'select';
        $fields['status']['options'] = array(
            '0' => 'Draft',
            '1' => 'Published',
            );
        
        $view->action("")
            ->fields($fields);
        return $view;
    }
}