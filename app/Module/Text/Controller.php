<?php
/**
 * Text Module
 */
class Module_Text_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * GET
	 */
	public function indexAction($request, $page, $module)
	{
		$text = $this->mapper()->first(array('module_id' => $module->id));
		if(!$text) {
			return false;
		}
		
		// Return only content for HTML
		if($request->format == 'html') {
			return $text->content;
		}
		return $this->cx->resource($text);
	}
	
	
	/**
	 * @method GET
	 */
	public function newAction($request, $page)
	{
		$itemUrl = $this->cx->url('module', array('url' => $page->url, 'module_name' => $this->name(), 'module_id' => 0));
		return $this->formView()->method('post')->action($itemUrl);
	}
	
	
	/**
	 * @method GET
	 */
	public function editAction($request, $page, $module)
	{
		$cx = $this->cx;
		
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
		$mapper = $this->mapper();
		$entity = $mapper->get()->data($request->post());
		if($mapper->save($entity)) {
			$pageUrl = $this->cx->url('page', array('url' => $entity->url));
			if($request->format == 'html') {
				return $this->cx->redirect($pageUrl);
			} else {
				return $this->cx->resource($entity)->status(201)->location($pageUrl);
			}
		} else {
			$this->cx->response(400);
			return $this->formView()->errors($mapper->errors());
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
		$view = new Cx_View_Generic_Form($this->cx);
		$view->action("")
			->fields($this->mapper()->fields())
			->removeFields(array('id', 'date_created', 'date_modified'));
		return $view;
	}
}