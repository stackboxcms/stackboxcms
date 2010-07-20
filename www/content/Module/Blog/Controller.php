<?php
/**
 * Blog Module
 */
class Module_Blog_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		$posts = $this->mapper('Module_Blog_Post')->all()->order('date_created');
		
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
		$posts = $this->mapper('Module_Blog_Post')->all()->order('date_created');
		
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
		$item = $mapper->get($request->module_item);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		return $this->mapper()->delete($item);
	}
}