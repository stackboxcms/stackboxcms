<?php
/**
 * Base View Helper
 * 
 * @package Cont-xt
 * @link http://cont-xt.com/
 */
abstract class Cx_View_Helper
{
	protected $_view;
	
	public function __construct(Cx_View $view)
	{
		$this->_view = $view;
	}
}