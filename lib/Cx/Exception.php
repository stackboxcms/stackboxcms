<?php
/*
 * Cx_Exception Class
 * $Id$
 *
 * Handles application errors
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Exception extends Exception
{
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0) {
		// some code
		
		// make sure everything is assigned properly
		parent::__construct($message, $code);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

	public function getError()
	{
		// Show friendly error message
		$error = '<div style="font-size:14px; color:#000000;">';
		$error .= '<div style="padding:8px; background:#eee; font:\'Courier New\', Courier, mono; font-weight:bold;">';
		$error .= $this->getMessage();
		$error .= '</div>';
		
		// Show stack trace if not in production mode
		if(cx()->config('cx.debug')) {
			$error .= '<div style="font-size:12px; padding:2px 8px; background:#FFFFCC; font:\'Courier New\', Courier, mono"><pre>';
			$error .= "Code: " . $this->getCode() . "\n" . "File: " . $this->getFile() . "\n" . "Line: " . $this->getLine() . " at: \n";
			$error .= $this->getTraceAsString() . "\n";
			$error .= '</pre></div>';
		}
		
		$error .= '</div>';
		
		return $error;
	}
}