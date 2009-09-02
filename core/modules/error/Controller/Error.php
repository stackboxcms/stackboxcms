<?php
/**
 * $Id$
 */
class Module_Controller_Error extends Cx_Controller
{
	/**
	 * Initialization function, unset all filters
	 * We don't need any kind of authentication to show errors
	 */
	public function init()
	{
		unset($this->filters);
	}
	
	
	/**
	 * User login function
	 */
	public function indexAction($errorCode = 500, $errorMessage = null)
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		
		// Set response status
		$response->setStatus($errorCode);
		
		// Custom error page titles
		switch($errorCode) {
			case 404:
				$title = 'File Not Found';
				if(empty($errorMessage)) {
					$errorMessage = 'The page or file you were looking for does not exist.';
				}
			break;
			
			case 405:
				$title = 'Whoops!';
			break;
			
			case 500:
				$title = 'Application Error';
			break;
			
			default:
				$title = 'Error ' . $errorCode;
			break;
		}
		
		// Assign template variables
		$this->getView()->set(array(
			'title' => $title,
			'errorCode' => $errorCode,
			'errorMessage' => $errorMessage
			));
	}
	
	
	/**
	 * Missed function call passthrough
	 */
	public function __call($func, $args)
	{
		$errorCode = str_replace("Action", "", $func);
		$this->forward($this, 'indexAction', array($errorCode));
	}
}