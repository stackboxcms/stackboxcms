<?php
/**
 * $Id$
 */
class Module_Error_Controller extends Cx_Controller
{
	/**
	 * Error display and handling function
	 */
	public function indexAction($errorCode = 500, $errorMessage = null)
	{
		$cx = $this->cx;
		$request = $cx->request();
		$response = $cx->response();
		
		// Set response status
		$response->status($errorCode);
		
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
		$this->view()->set(array(
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