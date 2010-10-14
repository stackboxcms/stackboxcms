<?php
namespace Module\Code;

/**
 * Text Module
 */
class Controller extends \Cx\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        $item = $this->kernel->mapper('Module\Code\Mapper')->currentEntity($module);
        if(!$item) {
            return false;
        }
        
        // Return only content for HTML
        if($request->format == 'html') {
            // Return view with formatting
            return $this->template(__FUNCTION__)
                ->set(compact('item'));
        }
        return $this->kernel->resource($item);
    }
    
    
    /**
     * @method GET
     */
    public function newAction($request, $page, $module)
    {
        $form = $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'));
        return $this->template('editAction')->set(compact('form'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $form = $this->formView()
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'))
            ->method('PUT');
        
        $mapper = $this->kernel->mapper('Module\Code\Mapper');
        
        if(!$module) {
            $module = $mapper->get('Module\Code\Entity');
            $form->method('POST');
        }
        
        $item = $mapper->currentEntity($module);
        
        // Set item data on form
        $form->data($mapper->data($item));
        
        // Return view template
        return $this->template(__FUNCTION__)->set(compact('form'));
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper('Module\Code\Mapper');
        $item = $mapper->data($mapper->get('Module\Code\Entity'), $request->post());
        $item->module_id = $module->id;
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
        $mapper = $this->kernel->mapper('Module\Code\Mapper');
        $item = $mapper->currentEntity($module);
        if(!$item) {
            return false;
        }
        $mapper->data($item, $request->post());
        $item->module_id = $module->id;
        
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
     * Install Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper('Module\Code\Mapper')->migrate('Module\Code\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper('Module\Code\Mapper')->dropDatasource('Module\Code\Entity');
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper('Module\Code\Mapper');
        $item = $mapper->get('Module\Code\Entity', $request->module_item);
        if(!$item) {
            return false;
        }
        return $mapper->delete($item);
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        $view = parent::formView();
        $fields = $view->fields();
        
        // Set type and options for 'type' select
        $fields['type']['type'] = 'select';
        $fields['type']['options'] = array(
            'generic' => 'Generic',
            'css' => 'CSS',
            'html' => 'HTML',
            'ini' => 'INI Conifg',
            'js' => 'JavaScript',
            'php' => 'PHP',
            'shell' => 'Shell',
            'text' => 'Text'
            );
        
        $view->action("")
            ->fields($fields);
        return $view;
    }
}