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
		$kernel = $this->kernel;
		
		// Use error code in request if found (custom HTTP error pages)
		$errorCode = ($request->errorCode) ? $request->errorCode : $errorCode;
		
		// Send response status
		$responseText = $kernel->response()->status($errorCode);
		
		// Custom error page titles
		$title = 'Error ' . $errorCode . ' - ' . $responseText;
		if($errorCode == 404) {
			if(empty($errorMessage)) {
				$errorMessage = 'The page or file you were looking for does not exist.';
			}
		}
		if(empty($errorMessage)) {
			$errorMessage = 'An error occured that has prevented this page from displaying properly.';
		}
		
		if($request->format == 'json') {
			return json_encode(array(
				'error' => array(
					'code' => $errorCode,
					'message' => $errorMessage
					)
				));
		} else {
			// Assign template variables
			return $this->view(__FUNCTION__)->set(array(
				'title' => $title,
				'errorCode' => $errorCode,
				'errorMessage' => $errorMessage,
				'responseText' => $responseText
				));
		}
	}
}