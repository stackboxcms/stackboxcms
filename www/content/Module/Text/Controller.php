<?php
/**
 * Text Module
 */
class Module_Text_Controller extends Cx_Module_Controller_Abstract
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request, $page, $module)
	{
		$item = $this->kernel->mapper('Module_Text_Mapper')->currentTextEntity($module);
		if(!$item) {
			return false;
		}
		// Return only content for HTML
		if($request->format == 'html') {
			// Return view with formatting
            return $this->view(__FUNCTION__)
			    ->set(array(
			        'item' => $item
			    ));
		}
		return $this->kernel->resource($item);
	}
	
	
	/**
	 * @method GET
	 */
	public function newAction($request, $page, $module)
	{
		$form = $this->formView()
			->method('post')
			->action($this->kernel->url('module', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id)));
		return $this->view('editAction')->set(compact('form'));
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		$form = $this->formView()
			->action($this->kernel->url('module', array('page' => $page->url, 'module_name' => $this->name(), 'module_id' => $module->id)))
			->method('PUT');
		
		$mapper = $this->kernel->mapper('Module_Text_Mapper');
		
		if(!$module) {
			$module = $mapper->get('Module_Text_Entity');
			$form->method('POST');
		}
		
		$item = $mapper->currentTextEntity($module);
		
		// Set item data on form
		$form->data($mapper->data($item));
		
		// Return view template
		return $this->view(__FUNCTION__)->set(compact('form'));
	}
	
	
	/**
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request, $page, $module)
	{
		$mapper = $this->kernel->mapper('Module_Text_Mapper');
		$item = $mapper->data($mapper->get('Module_Text_Entity'), $request->post());
		$item->module_id = $module->id;
		$item->date_created = $mapper->connection('Module_Text_Entity')->dateTime();
		$item->date_modified = $mapper->connection('Module_Text_Entity')->dateTime();
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
		$mapper = $this->kernel->mapper('Module_Text_Mapper');
		//$item = $mapper->get($request->module_item);
		$item = $mapper->currentTextEntity($module);
		if(!$item) {
			throw new Alloy_Exception_FileNotFound($this->name() . " module item not found");
		}
		$mapper->data($item, $request->post());
		$item->module_id = $module->id;
		$item->date_modified = $mapper->connection('Module_Text_Entity')->dateTime();
		
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
		$mapper = $this->kernel->mapper('Module_Text_Mapper');
		$item = $mapper->get('Module_Text_Entity', $request->module_item);
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
		$this->kernel->mapper('Module_Text_Mapper')->migrate('Module_Text_Entity');
		return parent::install($action, $params);
	}
	
	
	/**
	 * Uninstall Module
	 *
	 * @see Cx_Module_Controller_Abstract
	 */
	public function uninstall()
	{
		return $this->kernel->mapper('Module_Text_Mapper')->dropDatasource('Module_Text_Entity');
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$view = parent::formView();
		$fields = $view->fields();
		
		// Set text 'content' as type 'editor' to get WYSIWYG
		$fields['content']['type'] = 'editor';
		
		// Set type and options for 'type' select
		$fields['type']['type'] = 'select';
		$fields['type']['options'] = array(
		    '' => 'None',
    		'note' => 'Note',
    		'warning' => 'Warning',
    		'code' => 'Code'
		    );
		
		$view->action("")
			->fields($fields);
		return $view;
	}
}
