<?php
/**
 * Form Helper Functions
 * $Id$
 *
 * General functions available to the View templates
 * 
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_View_Helper_Form
{
	protected $view;

	public function __construct($view)
	{
		$this->view = $view;
	}
	
	/**
	 *	Load other helpers for the view to use
	 */
	public function formOpen($url, $method, $extra)
	{
		$tag = '<form action="' . $this->view->urlTo($url) . '" method="' . $method . '">';
		echo $tag;
	}
	
	
	/**
	 *	Form input element
	 */
	public function input($type, $name, $value='', $extra='')
	{
		$extra['id'] = isset($extra['id']) ? $extra['id'] : trim($name);
		$tag = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '"' . $this->listExtra($extra) . ' />';
		echo $tag;
	}
	
	/**
	 *	Text input
	 */
	public function inputText($name, $value='', $extra='')
	{
		$this->input('text', $name, $value, $extra);
	}
	
	
	/**
	 *	Text input
	 */
	public function inputPassword($name, $value='', $extra='')
	{
		$this->input('password', $name, '', $extra);
	}
	
	
	/**
	 *	Textarea input
	 */
	public function inputTextarea($name, $value='', $extra='')
	{
		$tag = '<textarea name="' . $name . '"' . $this->listExtra($extra) . '>' . $value . '</textarea>';
		echo $tag;
	}
	
	
	/**
	 *	Selection box input (dropdown)
	 */
	public function inputSelect($name, array $options, $value='', $extra='')
	{
		$blankOption = '';
		if(isset($extra['blank'])) {
			// blank selection <option>
			$blankOption = '<option value="">' . $extra['blank'] . '</option>';
			// remove it from the attributes list
			unset($extra['blank']);
		}
		
		// Set 'id' attribute if not set already
		$extra['id'] = isset($extra['id']) ? $extra['id'] : $name;
		// Input array values will also be the keys (literal strings)
		if(isset($extra['literal']) && $extra['literal']) {
			unset($extra['literal']);
			$literal = true;
		} else {
			$literal = false;
		}
		
		// Begin <select> tag
		$tag = '<select name="' . $name . '"' . $this->listExtra($extra) . '>';
		$tag .= $blankOption;
		
		// Loop over options
		foreach($options as $key => $val)
		{
			// Input array values will also be the keys (literal strings)
			if($literal) {
				$key = $val;
			}
			
			$selected = '';
			if(is_array($value)) {
				if(in_array($key, $value)) {
					$selected = ' selected="selected"';
				}
			} elseif($key == $value) {
				$selected = ' selected="selected"';
			}
			// Print option
			$tag .= '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
		}
		$tag .= '</select>';
		echo $tag;
	}
	
	
	/**
	 * Radio selection input
	 */
	/*
	public function inputRadio($name, array $options, $value='', $extra='')
	{
		// Set 'id' attribute if not set already
		$extra['id'] = isset($extra['id']) ? $extra['id'] : $name;
		// Input array values will also be the keys (literal strings)
		if(isset($extra['literal']) && $extra['literal']) {
			unset($extra['literal']);
			$literal = true;
		} else {
			$literal = false;
		}
		
		// Loop over options
		foreach($options as $key => $val)
		{
			// Input array values will also be the keys (literal strings)
			if($literal) {
				$key = $val;
			}
			
			$selected = '';
			if(is_array($value)) {
				if(in_array($key, $value)) {
					$selected = ' checked="checked"';
				}
			} elseif($key == $value) {
				$selected = ' checked="checked"';
			}
			
			// Print radio input
			$tag .= '<input type="radio" name="' . $name . '" value="' . $key . '"' . $this->listExtra($extra) . '' . $selected . ' /><br />';
		}
		echo $tag;
	}
	*/
	
	
	/**
	 * Show checkbox from element
	 */
	public function inputCheckbox($name, $value='', $extra='')
	{
		if(($value > 0 || !empty($value)) && $value) {
			$extra['checked'] = "checked";
		}
		$value = isset($extra['value']) ? $extra['value'] : 1;
		echo $this->input('checkbox', $name, $value, $extra);
	}
	
	
	/**
	 *	Print out extra attributes
	 */
	protected function listExtra($extra)
	{
		$output = '';
		if(is_array($extra) && count($extra) > 0) {
			foreach($extra as $key => $val) {
				if(!empty($val)) {
					$output .= ' ' . $key . '="' . $val . '"';
				}
			}
		}
		return $output;
	}
}