<?php
/**
 * Text Module
 */
class Module_Text_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		$item = $this->mapper()->currentTextEntity($module);
		if(!$item) {
			return true;
		}
		
		// Return only content for HTML
		if($request->format == 'html') {
			return $item->content;
		}
		return $this->kernel->resource($item);
	}
	
	
	/**
	 * @method GET
	 */
	public function newAction($request, $page, $module)
	{
		return $this->formView()
			->method('post')
			->action($this->kernel->url('module', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id)));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		$form = $this->formView()
			->action($this->kernel->url('module', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id)))
			->method('PUT');
		
		if(!$module) {
			$module = $this->mapper()->get();
			$form->method('POST');
		}
		
		$item = $this->mapper()->currentTextEntity($module);
		
		return $form->data($item->data());
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request, $page, $module)
	{
		$mapper = $this->mapper();
		$item = $mapper->get()->data($request->post());
		$item->module_id = $module->id;
		if($mapper->save($item)) {
			$itemUrl = $this->kernel->url('module_item', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id));
			if($request->format == 'html') {
				return $this->indexAction($request, $page, $module);
			} else {
				return $this->kernel->resource($item)->status(201)->location($itemUrl);
			}
		} else {
			$this->kernel->response(400);
			return $this->formView()->errors($mapper->errors());
		}
	}
	
	
	/**
	 * Save over existing entry (from edit)
	 * @method PUT
	 */
	public function putMethod($request, $page, $module)
	{
		$mapper = $this->mapper();
		//$item = $mapper->get($request->module_item);
		$item = $this->mapper()->currentTextEntity($module);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		$item->data($request->post());
		$item->module_id = $module->id;
		
		if($mapper->save($item)) {
			$itemUrl = $this->kernel->url('module_item', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id));
			if($request->format == 'html') {
				return $this->indexAction($request, $page, $module);
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
		
		// Set text 'content' as type 'editor' to get WYSIWYG
		$fields['content']['type'] = 'editor';
		
		$view = new Alloy_View_Generic_Form('form');
		$view->action("")
			->fields($fields)
			->removeFields(array('id', 'module_id', 'date_created', 'date_modified'));
		return $view;
	}
}