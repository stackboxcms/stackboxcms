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
     * Access control list for controller methods
     */
    public function acl()
    {
        $acl = parent::acl();

        // Ensure user has rights to create new user account
        $access = false;

        // Catch PDOException to auto-migrate
        try {
            $user = $this->kernel->user();
            if(!$user || !$user->isLoggedIn() || !$user->isAdmin()) {
                // If there are not currently any users that exist, allow access to create the first user, even if not logged in
                // @TODO: Limit this to check users per-site
                $userCount = $this->kernel->mapper()->all('Module\User\Entity')->count();
                if($userCount == 0) {
                    $access = true;
                }
            }
        } catch(\PDOException $e) {
            $this->install();
            $access = true;
        }

        // Allow access
        if(true === $access) {
            $acl = array_merge_recursive($acl, array(
                'view' => array('new', 'newAction', 'post', 'postMethod')
            ));
        }

        return $acl;
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
        $users = $this->kernel->mapper()->all('Module\User\Entity')
            ->where(array('site_id' => $page->site_id));

        return $this->template(__FUNCTION__)
            ->set(compact('users', 'request', 'page', 'module'))->content();
    }
    
    
    /**
     * Create new user
     * @method GET
     */
    public function newAction($request, $page = null, $module = null)
    {
        if(!$page || !$module) {
            return $this->kernel->redirect($this->kernel->url(array('page' => '/', 'module_name' => 'user', 'module_id' => 0, 'module_action' => 'new'), 'module'));
        }

        // Item URL
        $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $module->name, 'module_id' => (int) $module->id), 'module');

        return $this->formView()
            ->method('post')
            ->action($itemUrl);
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $user = $this->kernel->mapper()->get('Module\User\Entity', (int) $request->module_item);
        // if item does not exist or does not belong to current site
        if(!$user || $user->site_id != $page->site_id) {
            return false;
        }

        // Item URL
        $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $module->name, 'module_id' => (int) $module->id, 'module_item' => $user->id), 'module_item');

        $form = $this->formView()
            ->action($itemUrl)
            ->method('put')
            ->data($user->dataExcept(array('site_id', 'password', 'salt')));
        return $form;
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->create('Module\User\Entity', $request->post());

        // Overwrite site_id to ensure this is for same site
        $item->site_id = $page->site_id;
        
        // Attempt save
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $module->name, 'module_id' => (int) $module->id, 'module_action' => 'editlist'), 'module');
            if($request->format == 'html') {
                return $this->editlistAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item)
                    ->status(201)
                    ->location($itemUrl);
            }
        } else {
            return $this->newAction($request, $page, $module)
                ->status(400)
                ->errors($mapper->errors())
                ->data($request->post());
        }
    }
    
    
    /**
     * Edit existing entry
     * @method PUT
     */
    public function putMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\User\Entity', (int) $request->module_item);
        // if item does not exist or does not belong to current site
        if(!$item || $item->site_id != $page->site_id) {
            return false;
        }
        $item->data($request->post());

        // Overwrite site_id to ensure this is for same site
        $item->site_id = $page->site_id;
        
        // Attempt save
        if($mapper->save($item)) {
            $itemUrl = $this->kernel->url(array('page' => $page->url, 'module_name' => $module->name, 'module_id' => (int) $module->id, 'module_action' => 'editlist'), 'module');
            if($request->format == 'html') {
                return $this->editlistAction($request, $page, $module);
            } else {
                return $this->kernel->resource($item)
                    ->status(201)
                    ->location($itemUrl);
            }
        } else {
            return $this->editAction($request, $page, $module)
                ->status(400)
                ->errors($mapper->errors())
                ->data($request->post());
        }
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $item = $this->kernel->mapper->get('Module\User\Entity', $request->module_item);
        // if item does not exist or does not belong to current site
        if(!$item || $item->site_id != $page->site_id) {
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
    protected function formView($entityName = null)
    {
        return $this->kernel->spotForm('Module\User\Entity')
            ->removeFields(array('site_id', 'salt'));
    }
}