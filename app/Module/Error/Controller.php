<?php
/**
 * $Id$
 */
class Module_Error_Controller extends Cx_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * Error display and handling function
	 */
	public function display($errorCode = 500, $errorMessage = null)
	{
		$cx = $this->cx;
		$request = $cx->request();
		
		// Send response status
		$responseText = $cx->response($errorCode);
		
		// Custom error page titles
		$title = 'Error ' . $errorCode . ' - ' . $responseText;
		if($errorCode == 404) {
			if(empty($errorMessage)) {
				$errorMessage = 'The page or file you were looking for does not exist.';
			}
		}
		
		// Assign template variables
		return $this->view(__FUNCTION__)->set(array(
			'title' => $title,
			'errorCode' => $errorCode,
			'errorMessage' => $errorMessage,
			'responseText' => $responseText
			));
	}
}