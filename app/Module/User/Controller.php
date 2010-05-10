<?php
/**
 * User Module
 */
class Module_User_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * Access control
	 */
	public function init()
	{
		// Ensure user has rights to create new user account
		$access = false;
		if($this->kernel->user()->isAdmin()) {
			// If user has admin access
			$access = true;
		} else {
			// If there are not currently any users that exist
			$userCount = $this->mapper()->all()->count();
			if($userCount == 0) {
				$access = true;
			}
		}
		
		if(!$access) {
			throw new Alloy_Exception_Auth("User is not logged in or does not have proper permissions to perform requested action");
		}
		
		return parent::init();
	}
	
	
	/**
	 * Index listing
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		return false;
	}
	
	
	/**
	 * Create new user
	 * @method GET
	 */
	public function newAction($request)
	{
		return $this->formView()
			->method('post')
			->action($this->kernel->url('user', array('action' => 'new')));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request)
	{
		$form = $this->formView()
			->action($this->kernel->url('user', array('action' => 'post')))
			->method('put');
		
		if(!$module) {
			$module = $this->mapper()->get();
			$form->method('post');
		}
		
		$item = $this->mapper()->currentEntity($module);
		
		return $form->data($item->data());
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request)
	{
		$mapper = $this->mapper();
		$item = $mapper->get()->data($request->post());
		$item->module_id = 0;
		$item->site_id = 0;
		$item->salt = $item->salt();
		if($mapper->save($item)) {
			$itemUrl = $this->kernel->url('page', array('page' => '/'));
			if($request->format == 'html') {
				return $this->kernel->redirect($itemUrl);
			} else {
				return $this->kernel->resource($item)->status(201)->location($itemUrl);
			}
		} else {
			$this->kernel->response(400);
			return $this->formView()->errors($mapper->errors());
		}
	}
	
	
	/**
	 * Edit existing entry
	 * @method PUT
	 */
	public function putMethod($request)
	{
		$mapper = $this->mapper();
		//$item = $mapper->get($request->module_item);
		$item = $this->mapper()->get($request->id);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		$item->data($request->post());
		$item->module_id = 0;
		$item->site_id = 0;
		
		if($mapper->save($item)) {
			$itemUrl = $this->kernel->url('user', array('action' => 'index'));
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
		$item = $mapper->get($request->module_item);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		return $this->mapper()->delete($item);
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$fields = $this->mapper()->fields();
		
		$view = new Alloy_View_Generic_Form('form');
		$view->action("")
			->fields($fields)
			->removeFields(array('id', 'salt', 'site_id', 'module_id', 'date_created', 'date_modified'));
		return $view;
	}
}