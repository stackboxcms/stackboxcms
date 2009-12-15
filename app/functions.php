<?php
/**
 * Custom error reporting
 */
function cx_errorHandler($errno, $errstr, $errfile, $errline) {
	$errorMsg = $errstr . " (Line: " . $errline . ")";
	if($errno != E_WARNING && $errno != E_NOTICE && $errno != E_STRICT) {
		throw new Cx_Exception($errorMsg, $errno);
	} else {
		return false; // Let PHP handle it
	}
}
set_error_handler("cx_errorHandler");