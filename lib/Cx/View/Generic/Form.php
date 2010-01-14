<?php
/**
 * Generic Form View
 * 
 * @package Cont-xt
 * @link http://cont-xt.com/
 */
class Cx_View_Generic_Form extends Cx_View
{
	protected $_mapper;
	protected $_fields = array();
	
	
	/**
	 * Create form object
	 */
	public function __construct(phpDataMapper_Base $mapper)
	{
		$this->_mapper = $mapper;
		$this->fields($mapper->fields());
		
		// Set template vars
		$this->set('fields', $this->fields());
		
		// Pick template and set path
		$this->template('form', 'html')
			->path(dirname(__FILE__) . '/templates/');
	}
	
	
	/**
	 * Field setter/getter
	 */
	public function fields(array $fields = array())
	{
		if(count($fields) > 0 ) {
			$this->_fields = $fields;
		}
		return $this->_fields;
	}
}