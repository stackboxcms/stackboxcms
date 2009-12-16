<?php
/**
 * $Id$
 */
class Error_Module extends Cx_Module
{
	/**
	 * Error display and handling function
	 */
	public function indexAction($request, $errorCode = 500, $errorMessage = null)
	{
		$cx = $this->cx;
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
		return $this->view()->set(array(
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