<?php
/**
 * Text Module
 */
class Module_Code_Controller extends Cx_Module_Controller_Abstract
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		$item = $this->kernel->mapper('Module_Code_Mapper')->currentEntity($module);
		if(!$item) {
			return true;
		}
		
		// Return only content for HTML
		if($request->format == 'html') {
			// Uses built-in PHP highligher for now, PHP code only until a full syntax highlighting solution is decided upon
			return highlight_string($item->content, true);
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
		
		$mapper = $this->kernel->mapper('Module_Code_Mapper');
		
		if(!$module) {
			$module = $mapper->get('Module_Code_Entity');
			$form->method('POST');
		}
		
		$item = $mapper->currentEntity($module);
		
		return $form->data($mapper->data($item));
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request, $page, $module)
	{
		$mapper = $this->kernel->mapper('Module_Code_Mapper');
		$item = $mapper->data($mapper->get('Module_Code_Entity'), $request->post());
		$item->module_id = $module->id;
		if($mapper->save($item)) {
			$itemUrl = $this->kernel->url('module_item', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id, 'module_item' => $item->id));
			if($request->format == 'html') {
				return $this->indexAction($request, $page, $module);
			} else {
				return $this->kernel->resource($mapper->data($item))->status(201)->location($itemUrl);
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
		$item = $this->mapper()->currentEntity($module);
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
				return $this->kernel->resource($mapper->data($item))->status(201)->location($itemUrl);
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
		$mapper = $this->kernel->mapper('Module_Code_Mapper');
		$item = $mapper->get('Module_Code_Entity', $request->module_item);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		return $mapper->delete($item);
	}
}