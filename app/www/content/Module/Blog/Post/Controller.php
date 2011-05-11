<?php
namespace Module\Blog\Post;
use Stackbox;

/**
 * Blog Module
 * Blog Posts Controller
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        $posts = $this->kernel->mapper()->all('Module\Blog\Post\Entity')
            ->where(array(
                'module_id' => $module->id,
                'status' => Entity::STATUS_PUBLISHED
            ))
            ->order('date_created');
        
        // Return only content for HTML
        if($request->format == 'html') {
            $view = $this->template(__FUNCTION__)
                ->set(compact('posts', 'page', 'module'));
            return $view;
        }
        return $this->kernel->resource($posts);
    }


    /**
     * View single post
     * @method GET
     */
    public function viewAction($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $post = $mapper->get('Module\Blog\Post\Entity', $request->module_item);
        
        if(!$post) {
            return false;
        }
        
        // Return only content for HTML
        if($request->format == 'html') {
            $view = $this->template(__FUNCTION__)
                ->set(compact('post', 'page', 'module'));
            return $view;
        }
        return $this->kernel->resource($posts);
    }
    
    
    /**
     * @method GET
     */
    public function newAction($request, $page, $module)
    {
        $form = $this->formView()
            ->method('POST')
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->urlName(), 'module_id' => $module->id), 'module'));
        return $form;
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $form = $this->formView()
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->urlName(), 'module_id' => $module->id), 'module'))
            ->method('PUT');
        
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity', $request->module_item);
        
        if(!$item) {
            return false;
        }
        
        // Set item data on form
        $form->data($item->data());
        
        // Return view template
        return $form;
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity')->data($request->post());
        $item->module_id = $module->id;
        $item->date_created = $mapper->connection('Module\Blog\Post\Entity')->dateTime();
        $item->date_modified = $mapper->connection('Module\Blog\Post\Entity')->dateTime();
        
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->urlName(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item->data())
                    ->status(201)
                    ->location($itemUrl);
            }
        } else {
            return $this->formView()
                ->status(400)
                ->data($request->post())
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * Save over existing entry (from edit)
     * @method PUT
     */
    public function putMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity', $request->module_item);
        if(!$item) {
            return false;
        }
        $item->data($request->post());
        $item->module_id = $module->id;
        $item->date_modified = $mapper->connection('Module\Blog\Post\Entity')->dateTime();
        
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $this->urlName(), 'module_id' => $module->id, 'module_item' => $item->id), 'module_item');
            if($request->format == 'html') {
                return $this->indexAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item->data($item))
                    ->status(201)
                    ->location($itemUrl);
            }
        } else {
            return $this->formView()
                ->status(400)
                ->data($request->post())
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity', $request->module_item);
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
        $this->kernel->mapper()->migrate('Module\Blog\Post\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Blog\Post\Entity');
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
            Entity::STATUS_DRAFT => 'Draft',
            Entity::STATUS_PUBLISHED => 'Published',
            );
        
        $view->action("")
            ->fields($fields);
        return $view;
    }
}