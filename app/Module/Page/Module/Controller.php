<?php
/**
 * Page module controller - Add, move, or delete modules
 */
class Module_Page_Module_Controller extends Cx_Module_Controller_Abstract
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
		return $this->formView()
			->method('post')
			->action($this->kernel->url('page', array('page' => '/')));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		$kernel = $this->kernel;
		
		return $this->formView();
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request, $page, Module_Page_Module_Entity $module)
	{
		$kernel = $this->kernel;
		
		// @todo Attempt to load module before saving it so we know it will work
		
		// Save it
		$mapper = $kernel->mapper();
		$entity = $mapper->data($mapper->get('Module_Page_Module_Entity'), $request->post() + array(
			'page_id' => $page->id,
			'date_created' => $mapper->connection('Module_Page_Module_Entity')->dateTime()
			));
		if($mapper->save($entity)) {
			$pageUrl = $this->kernel->url('page', array('page' => $page->url));
			if($request->format == 'html') {
				// Set module data for return content
				$mapper->data($module, $mapper->data($entity));
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
	 * @method GET
	 */
	public function deleteAction($request, $page, $module)
	{
		if($request->format == 'html') {
			$view = new Alloy_View_Generic_Form('form');
			$form = $view
				->method('delete')
				->action($this->kernel->url('module_item', array('page' => '/', 'module_name' => $this->name(), 'module_id' => 0, 'module_item' => $request->module_item)))
				->data(array('item_dom_id' => 'cx_module_' . $request->module_item))
				->submitButtonText('Delete');
			return "<p>Are you sure you want to delete this module?</p>" . $form;
		}
		return false;
	}
	
	
	/**
	 * @method DELETE
	 */
	public function deleteMethod($request, $page, $module)
	{
		$item = $this->kernel->mapper()->get('Module_Page_Module_Entity', $request->module_item);
		if($item) {
			$this->kernel->mapper()->delete($item);
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Save module sorting
	 * @method POST
	 */
	public function saveSortAction($request, $page, $module)
	{
		if($request->modules && is_array($request->modules)) {
			$mapper = $this->mapper();
			foreach($request->modules as $regionName => $modules) {
				foreach($modules as $orderIndex => $moduleId) {
					$item = $mapper->get($moduleId);
					if($item) {
						$item->region = $regionName;
						$item->ordering = $orderIndex;
						$mapper->save($item);
					}
				}
			}
		}
		return true;
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$view = new Alloy_View_Generic_Form('form');
		$view->action("")
			->fields($this->kernel->mapper()->fields('Module_Page_Module_Entity'))
			->removeFields(array('id', 'date_created', 'date_modified'));
		return $view;
	}
}