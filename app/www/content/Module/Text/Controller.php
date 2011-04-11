<?php
namespace Module\Text;
use Stackbox;

/**
 * Text Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        $item = $this->kernel->mapper('Module\Text\Mapper')->currentEntity($module);
        if(!$item) {
            return false;
        }
        // Return only content for HTML
        if($request->format == 'html') {
            // Return view with formatting
            return $this->template(__FUNCTION__)
                ->set(array(
                    'item' => $item
                ));
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
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module');
        return $this->template('editAction')
            ->set(compact('form'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $form = $this->formView()
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module')
            ->method('PUT');
        
        $mapper = $this->kernel->mapper('Module\Text\Mapper');
        
        if(!$module) {
            $module = $mapper->get('Module\Text\Entity');
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
        $mapper = $this->kernel->mapper('Module\Text\Mapper');
        $item = $mapper->get('Module\Text\Entity')->data($request->post());
        $item->module_id = $module->id;
        $item->date_created = $mapper->connection('Module\Text\Entity')->dateTime();
        $item->date_modified = $mapper->connection('Module\Text\Entity')->dateTime();
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item->data())
                    ->status(201)
                    ->location($itemUrl);
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
        $mapper = $this->kernel->mapper('Module\Text\Mapper');
        $item = $mapper->currentEntity($module);
        if(!$item) {
            throw new Exception_FileNotFound($this->name() . " module item not found");
        }
        $item->data($request->post());
        $item->module_id = $module->id;
        $item->date_modified = new \DateTime();

        if($mapper->save($item)) {
            //var_dump($module, $item->data());
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item->data())
                    ->status(201)
                    ->location($itemUrl);
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
        $mapper = $this->kernel->mapper('Module\Text\Mapper');
        $item = $mapper->get('Module\Text\Entity', $request->module_item);
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
        $this->kernel->mapper('Module\Text\Mapper')->migrate('Module\Text\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper('Module\Text\Mapper')->dropDatasource('Module\Text\Entity');
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        $view = parent::formView();
        $fields = $view->fields();
        
        // Set text 'content' as type 'editor' to get WYSIWYG
        $fields['content']['type'] = 'editor';
        
        // Set type and options for 'type' select
        $fields['type']['type'] = 'select';
        $fields['type']['options'] = array(
            '' => 'None',
            'note' => 'Note',
            'warning' => 'Warning',
            'code' => 'Code'
            );
        
        $view->action("")
            ->fields($fields);
        return $view;
    }
}
