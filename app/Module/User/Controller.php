<?php
namespace Module\User;
use Stackbox;
use Alloy;

/**
 * User Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    protected $_path = __DIR__;

    /**
     * Access control
     */
    public function init($action = null)
    {
        // Ensure user has rights to create new user account
        $access = false;
        $user = $this->kernel->user();
        if($user && $user->isAdmin()) {
            // If user has admin access
            $access = true;
        } else {
            // If there are not currently any users that exist
            $userCount = $this->kernel->mapper()->all('Module\User\Entity')->count();
            if($userCount == 0) {
                $access = true;
            }
        }
        
        if(!$access) {
            throw new Alloy\Exception_Auth("User is not logged in or does not have proper permissions to perform requested action");
        }
        
        return parent::init();
    }
    
    
    /**
     * Index listing
     * @method GET
     */
    public function indexAction($request)
    {
        return false;
    }


    /**
     * List users for editing or adding
     * @method GET
     */
    public function editlistAction($request, $page, $module)
    {
        $users = $this->kernel->mapper()->all('Module\User\Entity');

        return $this->template(__FUNCTION__)
            ->set(compact('users', 'request', 'page', 'module'))->content();
    }
    
    
    /**
     * Create new user
     * @method GET
     */
    public function newAction($request, $page, $module)
    {
        return $this->formView()
            ->method('post')
            ->action($this->kernel->url(array('action' => 'post'), 'user'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $user = $this->kernel->mapper()->get('Module\User\Entity', (int) $request->module_item);
        if(!$user) {
            return false;
        }

        $form = $this->formView()
            ->action($this->kernel->url(array('action' => 'post'), 'user'))
            ->method('put')
            ->data($user->dataExcept(array('password')));
        return $form;
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->data($mapper->get('Module\User\Entity'), $request->post());
        $item->site_id = 0;
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => '/'), 'page');
            if($request->format == 'html') {
                return $this->kernel->redirect($itemUrl);
            } else {
                return $this->kernel->resource($item)->status(201)->location($itemUrl);
            }
        } else {
            $this->kernel->response(400);
            return $this->formView()->errors($mapper->errors())->data($request->post());
        }
    }
    
    
    /**
     * Edit existing entry
     * @method PUT
     */
    public function putMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\User\Entity', $request->id);
        if(!$item) {
            return false;
        }
        $mapper->data($item, $request->post());
        $item->site_id = 0;
        
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('action' => 'index'), 'user');
            if($request->format == 'html') {
                return $this->indexAction($request);
            } else {
                return $this->kernel->resource($item)->status(201)->location($itemUrl);
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
        $item = $this->kernel->mapper->get('Module\User\Entity', $request->module_item);
        if(!$item) {
            return false;
        }
        return $this->kernel->mapper()->delete($item);
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\User\Entity');
        $this->kernel->mapper()->migrate('Module\User\Session\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        $this->kernel->mapper()->dropDatasource('Module\User\Entity');
        $this->kernel->mapper()->dropDatasource('Module\User\Session\Entity');
        return parent::uninstall();
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
    {
        return $this->kernel->spotForm('Module\User\Entity')
            ->removeFields(array('site_id', 'salt'));
    }
}