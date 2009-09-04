<?php
/**
 * $Id$
 */
class Module_Page_Controller extends Cx_Controller
{
	/**
	 * Display current page
	 */
	public function indexAction()
	{
		$request = $this->getRequest();
		
		// Assign template variables
		$this->getView()->set(array(
			'title' => 'Page Title'
			));
		$this->autoRender = false;
	}
}