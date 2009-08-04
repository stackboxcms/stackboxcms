<?php
/**
 * Cx loader / factory
 */
function cx() {
	$cx = Cx_Locator::getInstance();
	return $cx;
}
 

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


/**
 * Return configuration value
 * 
 * @param string $value Value key to search for
 * @param string $default Default value to return if $value not found
 */
function cx_config($value = null, $default = false) {
	global $cfg;
	
	// No value passed - return entire config array
	if($value === null) { return $cfg; }
	
	// Find value to return
	if(strpos($value, '.') !== false) {
		$cfgValue = $cfg;
		$valueParts = explode('.', $value);
		foreach($valueParts as $valuePart) {
			if(isset($cfgValue[$valuePart])) {
				$cfgValue = $cfgValue[$valuePart];
			} else {
				$cfgValue = $default;
			}
		}
	} else {
		$cfgValue = $cfg;
		if(isset($cfgValue[$value])) {
			$cfgValue = $cfgValue[$value];
		} else {
			$cfgValue = $default;
		}
	}
	return $cfgValue;
}


/**
 *	Generates random string of specified length
 *
 *	@param string $length
 */
function cx_randomString($length)
{
    // Generate random 32 charecter string
    $string = md5(time());

    // Position Limiting
    $highest_startpoint = 32-$length;

    // Take a random starting point in the randomly
    // Generated String, not going any higher then $highest_startpoint
    $randomString = substr($string,rand(0,$highest_startpoint),$length);

    return $randomString;
}


/**
 *	Print out an array or object contents in preformatted text
 *	Useful for debugging and quickly determining contents of arrays or objects
 */
function cx_dump($object)
{
	echo "\n<pre>\n";
	print_r($object);
	echo "\n</pre>\n";
}


/**
 *	Truncates a string to a certian length & adds a "..." to the end
 */
function cx_truncate($string, $endlength="30", $end="...") {
    $strlen = strlen($string);
    if($strlen > $endlength) {
        $trim = $endlength-$strlen;
        $string = substr($string, 0, $trim); 
        $string .= $end;
    }
    return $string;
}


/**
 *	Filesize Calculating function
 *	Retuns the size of a file in a "human" format
 */
function cx_filesize($size) {
    $kb=1024;
    $mb=1048576;
    $gb=1073741824;
    $tb=1099511627776;

    if($size < $kb) {
        return $size." B";
    } else if($size < $mb) {
        return round($size/$kb,2)." KB";
    } else if($size < $gb) {
        return round($size/$mb,2)." MB";
    } else if($size < $tb) {
        return round($size/$gb,2)." GB";
    } else {
        return round($size/$tb,2)." TB";
    }
}


/**
 * Checks if a value is not 0 and empty
 * Needed because PHP's 'empty' function returns true for numeric 0 and string "0"
 */
function cx_empty($value) {
	if(is_array($value)) {
		$empty = empty($value);
	} else {
		$empty = ((is_numeric($value) && $value < 0) || $value == null || trim($value) == '');
	}
	return (bool) $empty;
}


/**
 * Converts underscores to spaces and capitalizes first letter of each word
 */
function cx_wordize($word) {
	return ucwords(str_replace('_', ' ', $word));
}


/**
 * Format given string to valid URL string
 */
function cx_urlFormat($string)
{
	// Allow only alphanumerics, underscores and dashes
	$string = preg_replace('/([^a-zA-Z0-9_\-]+)/', '-', strtolower($string));
	// Replace extra spaces and dashes with single dash
	$string = preg_replace('/\s+/', '-', $string);
	$string = preg_replace('|-+|', '-', $string);
	// Trim extra dashes
	$string = trim($string, '-');

	return $string;
}

/**
 * check if a data is serialized or not
 *
 * @param mixed $data   variable to check
 * @return boolean
*/
if(!function_exists('is_serialized')) {
function is_serialized($data){
   if (is_array($data) || trim($data) == "") {
      return false;
   }
   if (preg_match("/^(i|s|a|o|d)(.*);/si",$data)) {
      return true;
   }
   return false;
}
}


/**
 * Convert to useful array style from form input style
 * 
 * Input an array like this:
 * [name]	=>	[0] => "Google"
 *				[1] => "Yahoo!"
 * [url]	=>	[0] => "http://www.google.com"
 *				[1] => "http://www.yahoo.com"
 *
 * And you will get this:
 * [0]	=>	[name] => "Google"
 *			[title] => "http://www.google.com"
 * [1]	=>	[name] => "Yahoo!"
 *			[title] => "http://www.yahoo.com"
 */
function cx_arrayFlipConvert(array $input) {
	$output = array();
	foreach($input as $key => $val) {
		foreach($val as $key2 => $val2) {
			$output[$key2][$key] = $val2;
		}
	}
	return $output;
}