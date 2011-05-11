<?php
namespace Module\Slideshow;
use Alloy, Module, Stackbox;

/**
 * Slideshow Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * Public listing of slideshow items
     * @method GET
     */
    public function indexAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        $kernel = \Kernel();
        $items = $kernel->mapper()->all('Module\Slideshow\Item')
            ->where(array('module_id' => $module->id))
            ->order(array('ordering' => 'ASC'));
        if(!$items) {
            return false;
        }
        
        // HTML template
        if($request->format == 'html') {
            return $this->template(__FUNCTION__)
                ->set(compact('items', 'page', 'module'));
        }
        return $this->kernel->resource($items);
    }


    /**
     * Edit list for admin view
     * @method GET
     */
    public function editlistAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        // Get all blog posts (remember - query is not actually executed yet and can be futher modified by the gridview)
        $mapper = $this->kernel->mapper();
        $items = $mapper->all('Module\Slideshow\Item')
            ->where(array('module_id' => $module->id))
            ->order(array('ordering' => 'ASC'));
        
        // Return view template
        return $this->template(__FUNCTION__)
            ->set(compact('items', 'page', 'module'));
    }
    
    
    /**
     * @method GET
     */
    public function newAction($request, $page, $module)
    {
        $form = $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module');
        return $form;
    }
    
    
    /**
     * Edit single item
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $form = $this->formView()
            ->action($this->kernel->url(array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id), 'module'), 'module')
            ->method('PUT');
        
        $mapper = $this->kernel->mapper();
        
        $item = $mapper->get('Module\Slideshow\Item', $request->module_item);
        if(!$item) {
            return false;
        }
        
        // Set item data on form and return it
        return $form->data($item->data());
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Slideshow\Item')->data($request->post());
        $item->module_id = $module->id;
        $item->date_created = new \DateTime();
        $item->date_modified = new \DateTime();
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
            return $this->newAction($request, $page, $module)
                ->data($request->post())
                ->status(400)
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
        $item = $mapper->get('Module\Slideshow\Item', (int) $request->id);
        if(!$item) {
            return false;
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
            return $this->editAction($request, $page, $module)
                ->data($request->post())
                ->status(400)
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Slideshow\Item', $request->module_item);
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
        $this->kernel->mapper()->migrate('Module\Slideshow\Item');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Slideshow\Item');
    }


    /**
     * Settings init
     * 
     * Define all settings fields and values that will be needed
     */
    public function settings($page, $module)
    {
        return array(
            // Group
            'display' => array(
                // Fields
                'width' => array(
                    'type' => 'int',
                    'default' => 600,
                    'after' => 'Pixel width of the slideshow'
                ),
                'height' => array(
                    'type' => 'int',
                    'default' => 350,
                    'after' => 'Pixel height of the slideshow'
                ),
                'slide_delay' => array(
                    'type' => 'int',
                    'default' => 5000,
                    'after' => 'Time delay between slides (milliseconds)'
                ),
                'slide_speed' => array(
                    'type' => 'int',
                    'default' => 350,
                    'after' => 'Animation time for slide effect (milliseconds)'
                )
            )
        );
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        $view = $this->kernel->spotForm('Module\Slideshow\Item');
        $fields = $view->fields();
        
        // Set text 'content' as type 'editor' to get WYSIWYG
        $fields['url']['after'] = $this->kernel->filebrowserSelectImageLink('url');
        
        $view->fields($fields)
            ->removeFields(array('module_id', 'site_id', 'ordering'));
        return $view;
    }
}
