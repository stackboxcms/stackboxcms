<?php
namespace Module\Blog;

/**
 * Blog Module
 */
class Controller extends \Cx\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        $posts = $this->kernel->mapper()->all('Module\Blog\Post')->order('date_created');
        
        $view = $this->view(__FUNCTION__)
            ->set(array(
                    'posts' => $posts
                ));
        
        // Return only content for HTML
        if($request->format == 'html') {
            return $view;
        }
        return $this->kernel->resource($posts);
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        $posts = $this->kernel->mapper()->all('Module\Blog\Post')->order('date_created');
        
        $view = $this->view(__FUNCTION__)
            ->set(array(
                    'posts' => $posts
                ));
        
        // Return only content for HTML
        if($request->format == 'html') {
            return $view;
        }
        return false;
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Blog\Post', $request->module_item);
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
        $this->kernel->mapper()->migrate('Module\Blog\Entity');
        $this->kernel->mapper()->migrate('Module\Blog\Post');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        $this->kernel->mapper()->dropDatasource('Module\Blog\Entity');
        $this->kernel->mapper()->dropDatasource('Module\Blog\Post');
        return true;
    }
}