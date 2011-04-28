<?php
namespace Module\Blog;
use Stackbox;

/**
 * Blog Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        return $this->kernel->dispatch('Blog_Post', __FUNCTION__, compact('request', 'page', 'module'));
    }
    
    
    /**
     * @method GET
     */
    public function editAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        // Get all blog posts (remember - query is not actually executed yet and can be futher modified by the gridview)
        $mapper = $this->kernel->mapper();
        $posts = $mapper->all('Module\Blog\Post\Entity');
        
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