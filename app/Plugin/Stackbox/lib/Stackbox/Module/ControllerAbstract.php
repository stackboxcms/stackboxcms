<?php
namespace Stackbox\Module;

use Alloy;
use Stackbox;
use Alloy\Request;
use Module\Page\Entity as Page;
use Module\Page\Module\Entity as Module;

/**
 * Base application module controller
 * Used as a base module class other modules must extend from
 */
abstract class ControllerAbstract extends Alloy\Module\ControllerAbstract
{
    protected $_path;


    /**
     * Access control list for controller methods
     */
    public function acl()
    {
        return array(
            'view' => array('index', 'view', 'get', 'indexAction', 'viewAction', 'getMethod'),
            'edit' => array('new', 'edit', 'editlist', 'delete', 'post', 'put', 'newAction', 'editAction', 'editlistAction', 'deleteAction', 'postMethod', 'putMethod', 'deleteMethod')
        );
    }
    
    
    /**
     * Authorize user to execute action
     */
    public function userCanExecute(\Module\User\Entity $user, $action)
    {
        // Default role for all users
        $roles = array('view');
        
        // Add roles for current user
        if($user && $user->isLoggedIn()) {
            if($user->isAdmin()) {
                return true; // Admin users can always do everything
                $roles = array('view', 'edit', 'admin');
            }
        }
        
        // Get required role to execute requested action
        $requiredRole = null;
        foreach($this->acl() as $role => $acl) {
            if(in_array($action, $acl)) {
                $requiredRole = $role;
                break;
            }
        }
        
        // If required role is in user's roles
        if(in_array($requiredRole, $roles)) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    public function urlName()
    {
        return str_replace('\\', '_', $this->name());
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView($entityName = null)
    {
        if(null === $entityName) {
            if(defined('static::ENTITY')) {
                $entityName = static::ENTITY;
            } else {
                $entityName = "Module\\" . $this->name() . "\\Entity";   
            }
        }
        $fields = $this->kernel->mapper()->fields($entityName);
        $view = new \Alloy\View\Generic\Form('form');
        $view->action("")
            ->fields($fields)
            ->removeFields(array('id', 'site_id', 'page_id', 'module_id', 'date_created', 'date_modified'));
        return $view;
    }
    
    
    /**
     * Install Module
     *
     * @param string $action Action to execute on module when install is complete (passed when autoinstall is triggered)
     * @param array $params Params to execute action with
     */
    public function install($action = null, array $params = array())
    {
        $response = true;
        if(null !== $action) {
            $response = $this->kernel->dispatchRequest($this->kernel->request(), $this->name(), $action, array($this->kernel->request()) + $params);
        }
        return $response;
    }
    
    
    /**
     * Uninstall Module
     */
    public function uninstall() { return true; }
    
    
    /**
     * Return current class path
     * Attempts to guess based on common 'modules' directory
     * Can be overridded by setting a varialbe to the $_path directoy on the extending class (like __DIR__)
     */
    public function path()
    {
        if(null !== $this->_path) {
            return $this->_path;
        }

        $class = get_called_class();
        $path = str_replace('\\', '/', str_replace('\\Controller', '', $class));
        return $this->kernel->config('cms.path.modules') . '/' . $path;
    }


    /**
     * Settings display
     */
    public function settingsAction(Request $request, Page $page, Module $module)
    {
        $settings = array();
        if(method_exists($this, 'settings')) {
            $settings = $this->settings($page, $module);
        }

        // Dispatch to settings module to handle this and pass in retrieved settings to template
        return $this->kernel->dispatch('Settings', 'editlistAction', array($request, $page, $module))
            ->set(array('settingsInit' => $settings));
    }
}