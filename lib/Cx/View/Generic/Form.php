<?php
/**
 * Generic Form View
 * 
 * @package Cont-xt
 * @link http://cont-xt.com/
 */
class Cx_View_Generic_Form extends Cx_View
{
	protected $_cx;
	protected $_fields = array();
	
	
	/**
	 * Create form object
	 */
	public function __construct(Cx $cx)
	{
		$this->_cx = $cx;
		
		// Setup default template vars
		$this->set('request', $cx->request());
		
		// Pick template and set path
		$this->template('form', 'html')
			->path(dirname(__FILE__) . '/templates/');
	}
	
	
	/**
	 * Action param of form
	 *
	 * @param string $action URL form will submit to
	 */
	public function action($action = '')
	{
		$this->set('action', $action);
		return $this;
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
	 * Remove fields by name
	 *
	 * @param mixed $fieldName String or array of field names
	 */
	public function removeFields($fieldName)
	{
		$fields = (array) $fieldName;
		foreach($fields as $field) {
			if(isset($this->_fields[$field])) {
				unset($this->_fields[$field]);
			}
		}
		return $this;
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