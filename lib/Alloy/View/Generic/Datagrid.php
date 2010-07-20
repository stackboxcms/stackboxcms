<?php
/**
 * Generic Datagrid View
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
class Alloy_View_Generic_Datagrid extends Alloy_View
{
	protected $_fields = array();
	protected $_fieldValues = array();
	
	
	/**
	 * Setup form object
	 */
	public function init()
	{
		// Use local path by default
		$this->path(dirname(__FILE__) . '/templates/');
	}
	
	
	/**
	 * HTTP Method param of form
	 *
	 * @param string $action Method used to submit form to server
	 */
	public function method($method = 'POST')
	{
		$this->set('method', strtoupper($method));
		return $this;
	}
	
	
	/**
	 * Field setter/getter
	 */
	public function fields(array $fields = array())
	{
		if(count($fields) > 0 ) {
			$this->_fields = $fields;
			return $this;
		}
		return $this->_fields;
	}
	
	
	/**
	 * Get params set on field
	 *
	 * @param string $field Name of the field to return data for
	 */
	public function field($field)
	{
		if(isset($this->_fields[$field])) {
			return $this->_fields[$field];
		} else {
			return false;
		}
	}
	
	
	/**
	 * Value by field name
	 */
	public function data($field = null, $value = null)
	{
		// Return data array
		if(null === $field) {
			return $this->_fieldValues;
		}
		
		// Set data for single field
		if(null !== $value) {
			$this->_fieldValues[$field] = $value;
			return $this;
		
		// Set data from array for many fields
		} elseif(is_array($field)) {
			foreach($field as $fieldx => $val) {
				$this->_fieldValues[$fieldx] = $val;
			}
			return $this;
		}
		
		// Return data for given field
		return isset($this->_fieldValues[$field]) ? $this->_fieldValues[$field] : null;
	}
	
	
	/**
	 * Return template content
	 */
	public function content($parsePHP = true)
	{
		// Set template vars
		$this->set('fields', $this->fields());
		
		return parent::content($parsePHP);
	}
}