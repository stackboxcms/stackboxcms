<?php
/*
 * FileNotFound Class
 * $Id$
 * 
 * Handles 404 site not found errors
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Exception_FileNotFound extends Cx_Exception
{
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 404) {
		// make sure everything is assigned properly
		parent::__construct($message, $code);
	}

	public function getError()
	{
		// Send 404 Header to browser
		header('HTTP/1.0 404 Not Found');
		
		// Print out message
		$output = '<h1 style="font-size:140%;">404 - File Not Found</h1>';
		$output .= $this->getMessage();
		return $output;
	}
}