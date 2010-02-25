<?php
/**
 * Page module controller - Add, move, or delete modules
 */
class Module_Page_Module_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		return false;
	}
	
	
	/**
	 * @method GET
	 */
	public function newAction($request, $page, $module)
	{
		$pageUrl = $this->kernel->url('page', array('page' => '/'));
		return $this->formView()->method('post')->action($pageUrl);
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		$kernel = $this->kernel;
		
		// Ensure page exists
		$mapper = $this->mapper();
		$page = $mapper->getPageByUrl($request->url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $request->url . "'");
		}
		
		
		
		return $this->formView();
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request, $page, $module)
	{
		$kernel = $this->kernel;
		
		// Attempt to load module
		
		
		// Save it
		$mapper = $this->mapper();
		$entity = $mapper->get()->data($request->post())->data(array(
			'page_id' => $page->id,
			'date_created' => date($mapper->adapter()->dateTimeFormat())
			));
		if($mapper->save($entity)) {
			$pageUrl = $this->kernel->url('page', array('page' => $entity->url));
			if($request->format == 'html') {
				// Dispatch to return module content
				return $kernel->dispatch($entity->name, 'indexAction', array($request, $page, $entity));
			} else {
				return $this->kernel->resource($entity)->status(201)->location($pageUrl);
			}
		} else {
			$this->kernel->response(400);
			return $this->formView()
				->data($request->post())
				->errors($mapper->errors());
		}
	}
	
	
	/**
	 * @method DELETE
	 */
	public function deleteMethod($request, $page, $module)
	{
		// Ensure page exists
		$page = $this->mapper()->getPageByUrl($request->url);
		if(!$page) {
			throw new Cx_Exception_FileNotFound("Page not found: '" . $this->mapper()->formatPageUrl($url) . "'");
		}
		
		$this->mapper()->delete($page);
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$view = new Cx_View_Generic_Form('form');
		$view->action("")
			->fields($this->mapper()->fields())
			->removeFields(array('id', 'date_created', 'date_modified'));
		return $view;
	}
}