<?php
/**
 * Validation Class
 * $Id$
 *
 * Validates input against certian criteria
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Validator {
	
	protected $fields = array();
	protected $errors = array();
	
	
	/**
	 *	Setup validation
	 */
	public function __construct($array, $fields = array())
	{
		foreach($array as $key => $value)
		{
			if(empty($fields) || (!empty($fields) && in_array($key, $fields)))
			{
				$this->fields[$key] = $value;
			}
		}
	}
	
	
	/**
	 *	Required field validator
	 */
	public function required($field, $message = NULL)
	{
		// Allow single field or array
		if(is_array($field)) {
			$fields = $field;
		} else {
			$fields[] = $field;
		}
		
		// Loop through fields
		foreach($fields as $key)
		{
			// Default message
			if(empty($message) || is_array($field)) {
				$message = "Required field '$key' was left blank";
			}

			// Logical validation check (0 will not evaluate as empty)
			$var = isset($this->fields[$key]) ? $this->fields[$key] : null;
			if(((is_null($var) || (is_string($var) && rtrim($var) == "")) && $var !== false))
			{
				$this->addError($message);
			}
		}
	}

	
	/**
	 *	Alpha-Numeric field validator
	 */
	public function alphaNumeric($field, $message = NULL)
	{
		// Allow single field or array
		if(is_array($field)) {
			$fields = $field;
		} else {
			$fields[] = $field;
		}
		
		// Loop through fields
		foreach($fields as $key)
		{
			// Default message
			if(empty($message)) {
				$message = "Field '$key' can only contain letters and numbers";
			}

			// Logical validation check
			if(!empty($this->fields[$key]) && !ctype_alnum($this->fields[$key]))
			{
				$this->addError($message);
			}
		}
	}
	
	
	/**
	 *	Aplha field validator (letters only)
	 */
	public function alpha($field, $message = NULL)
	{
		// Allow single field or array
		if(is_array($field)) {
			$fields = $field;
		} else {
			$fields[] = $field;
		}
		
		// Loop through fields
		foreach($fields as $key)
		{
			// Default message
			if(empty($message)) {
				$message = "Field '$key' can only contain letters";
			}

			// Logical validation check
			if(!empty($this->fields[$key]) && !ctype_alpha($this->fields[$key]))
			{
				$this->addError($message);
			}
		}
	}


	/**
	 *	Numeric field validator
	 */
	public function numeric($field, $message = NULL)
	{
		// Allow single field or array
		if(is_array($field)) {
			$fields = $field;
		} else {
			$fields[] = $field;
		}
		
		// Loop through fields
		foreach($fields as $key)
		{
			// Default message
			if(empty($message)) {
				$message = "Field '$key' can only contain numbers";
			}

			// Logical validation check
			if(!empty($this->fields[$key]) && !is_numeric($this->fields[$key]))
			{
				$this->addError($message);
			}
		}
	}

	
	/**
	 *	Email Validator
	 */
	public function email($field, $message = NULL)
	{
		// Allow single field or array
		if(is_array($field)) {
			$fields = $field;
		} else {
			$fields[] = $field;
		}
		
		// Loop through fields
		foreach($fields as $key)
		{
			// Default message
			if(empty($message)) {
				$message = "Field '$key' is not a valid email address";
			}

			// Logical validation check
			if(!empty($this->fields[$key]) && !filter_var($this->fields[$key], FILTER_VALIDATE_EMAIL))
			{
				$this->addError($message);
			}
		}
	}
	
	
	/**
	 *	Password strength checker
	 */
	public function passwordStrength($password, $username = null)
	{
		if (!empty($username))
		{
			$password = str_replace($username, '', $password);
		}
	
		$strength = 0;
		$password_length = strlen($password);
	
		if ($password_length < 4)
		{
			return $strength;
		}
	
		else
		{
			$strength = $password_length * 4;
		}
	
		for ($i = 2; $i <= 4; $i++)
		{
			$temp = str_split($password, $i);
	
			$strength -= (ceil($password_length / $i) - count(array_unique($temp)));
		}
	
		preg_match_all('/[0-9]/', $password, $numbers);
	
		if (!empty($numbers))
		{
			$numbers = count($numbers[0]);
	
			if ($numbers >= 3)
			{
				$strength += 5;
			}
		}
	
		else
		{
			$numbers = 0;
		}
	
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^¨\\\]/', $password, $symbols);
	
		if (!empty($symbols))
		{
			$symbols = count($symbols[0]);
	
			if ($symbols >= 2)
			{
				$strength += 5;
			}
		}
	
		else
		{
			$symbols = 0;
		}
	
		preg_match_all('/[a-z]/', $password, $lowercase_characters);
		preg_match_all('/[A-Z]/', $password, $uppercase_characters);
	
		if (!empty($lowercase_characters))
		{
			$lowercase_characters = count($lowercase_characters[0]);
		}
	
		else
		{
			$lowercase_characters = 0;
		}
	
		if (!empty($uppercase_characters))
		{
			$uppercase_characters = count($uppercase_characters[0]);
		}
	
		else
		{
			$uppercase_characters = 0;
		}
	
		if (($lowercase_characters > 0) && ($uppercase_characters > 0))
		{
			$strength += 10;
		}
	
		$characters = $lowercase_characters + $uppercase_characters;
	
		if (($numbers > 0) && ($symbols > 0))
		{
			$strength += 15;
		}
	
		if (($numbers > 0) && ($characters > 0))
		{
			$strength += 15;
		}
	
		if (($symbols > 0) && ($characters > 0))
		{
			$strength += 15;
		}
	
		if (($numbers == 0) && ($symbols == 0))
		{
			$strength -= 10;
		}
	
		if (($symbols == 0) && ($characters == 0))
		{
			$strength -= 10;
		}
	
		if ($strength < 0)
		{
			$strength = 0;
		}
	
		if ($strength > 100)
		{
			$strength = 100;
		}
	
		return $strength;
	} 
	
	
	/**
	 *	Check if any errors exist
	 */
	public function hasErrors()
	{
		return count($this->errors);
	}
	
	
	/**
	 *	Get array of error messages
	 */
	public function getErrors()
	{
		return $this->errors;
	}
	
	
	/**
	 *	Add an error to error messages array
	 */
	public function addError($msg)
	{
		// Add to error array
		$this->errors[] = $msg;
	}	
}