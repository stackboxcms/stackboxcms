<?php
namespace Module\Blog\Post;

use Stackbox;
use Alloy\Request;
use Module\Page\Entity as Page;
use Module\Page\Module\Entity as Module;

/**
 * Blog Module
 * Blog Posts Controller
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction(Request $request, Page $page, Module $module)
    {
        $posts = $this->kernel->mapper()->all('Module\Blog\Post\Entity')
            ->where(array(
                'module_id' => $module->id,
                'status' => Entity::STATUS_PUBLISHED
            ))
            ->order(array('date_created' => 'DESC'));
        
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
    public function viewAction(Request $request, Page $page, Module $module)
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
    public function newAction(Request $request, Page $page, Module $module)
    {
        $form = $this->formView()
            ->method('POST')
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->urlName(), 'module_id' => $module->id), 'module'));
        
        return $this->template('_form')
            ->set(compact('form', 'request', 'page', 'module'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction(Request $request, Page $page, Module $module)
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
    public function postMethod(Request $request, Page $page, Module $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity')->data($request->post());
        $item->module_id = $module->id;
        $item->date_created = new \DateTime();
        $item->date_modified = new \DateTime();

        // Check published date
        if($request->date_published) {
            $item->date_published = date('Y-m-d H:i:s', strtotime($request->date_published));
        }
        if(!$item->date_published) {
            $item->date_published = new \DateTime();
        }
        
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
    public function putMethod(Request $request, Page $page, Module $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post\Entity', $request->id);
        if(!$item) {
            return false;
        }
        $item->data($request->post());
        $item->module_id = $module->id;
        $item->date_modified = $mapper->connection('Module\Blog\Post\Entity')->dateTime();
        
        // Check published date
        if($request->date_published) {
            $item->date_published = date('Y-m-d H:i:s', strtotime($request->date_published));
        }
        if(!$item->date_published) {
            $item->date_published = new \DateTime();
        }

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
    public function deleteMethod(Request $request, Page $page, Module $module)
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
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Blog\Post\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Blog\Post\Entity');
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView($entityName = null)
    {
        $view = parent::formView($entityName);
        $fields = $view->fields();
        
        // Setup editor
        $fields['description']['type'] = 'editor';

        // Set type and options for 'type' select
        $fields['status']['type'] = 'select';
        $fields['status']['options'] = array(
            Entity::STATUS_DRAFT => 'Draft',
            Entity::STATUS_PUBLISHED => 'Published',
        );
        $fields['status']['default'] = Entity::STATUS_PUBLISHED;

        // Set date to today by default
        $fields['date_published']['default'] = date('m/d/Y');
        
        $view->action("")
            ->fields($fields);
        return $view;
    }
}