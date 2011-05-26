<?php
namespace Module\Blog;

use Stackbox;
use Alloy\Request;
use Module\Page\Entity as Page;
use Module\Page\Module\Entity as Module;

/**
 * Blog Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction(Request $request, Page $page, Module $module)
    {
        return $this->kernel->dispatch('Blog_Post', __FUNCTION__, compact('request', 'page', 'module'));
    }
    
    
    /**
     * @method GET
     */
    public function editlistAction(Request $request, Page $page, Module $module)
    {
        // Get all blog posts (remember - query is not actually executed yet and can be futher modified by the gridview)
        $mapper = $this->kernel->mapper();
        $posts = $mapper->all('Module\Blog\Post\Entity')
            ->where(array('module_id' => $module->id))
            ->order(array('date_published' => 'DESC'));
        
        // Return view template
        return $this->template(__FUNCTION__)
            ->set(compact('posts', 'page', 'module'));
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Blog\Entity');
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
        $this->kernel->mapper()->dropDatasource('Module\Blog\Entity');
        $this->kernel->mapper()->dropDatasource('Module\Blog\Post\Entity');
        return true;
    }
}