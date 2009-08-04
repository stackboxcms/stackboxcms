<?php
/*
 * Module Exception Class
 * $Id$
 * 
 * Shows a nicer message when a module operation fails
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Exception_Module extends Cx_Exception
{
	public function getError()
	{
		// Show friendly error message
		$error = '<div style="font-size:14px; color:#000000;" class="cx_msg cx_msg_error">';
		$error .= '<div style="font-weight:bold;"><p>';
		$error .= '<big><strong>Module Error</strong></big> <br /><br /> ' . $this->getMessage();
		$error .= '</p></div></div>';
		
		return $error;
	}
}