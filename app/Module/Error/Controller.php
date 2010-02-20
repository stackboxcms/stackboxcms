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
	public function displayAction($request, $errorCode = 500, $errorMessage = null)
	{
		$cx = $this->cx;
		
		// Use error code in request if found (custom HTTP error pages)
		$errorCode = ($request->errorCode) ? $request->errorCode : $errorCode;
		
		// Send response status
		$responseText = $cx->response()->status($errorCode);
		
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