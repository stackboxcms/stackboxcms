<?php
/**
 * $Id$
 */
class Module_Model_Page extends phpDataMapper_Model
{
	// Custom row class
	protected $rowClass = 'Module_Model_Page_Row';
	
	// Setup table and fields
	protected $table = "pages";
	protected $fields = array(
		'id' => array('type' => 'int', 'primary' => true),
		'account_id' => array('type' => 'int', 'key' => true),
		'username' => array('type' => 'string', 'required' => true),
		'password' => array('type' => 'string', 'required' => true),
		'email' => array('type' => 'string', 'required' => true),
		'date_created' => array('type' => 'date', 'autodate' => 'insert'),
		'date_last_login' => array('type' => 'date'),
		'logins' => array('type' => 'int'),
		'is_active' => array('type' => 'int', 'length' => 1, 'default' => 1),
		);
}


// Custom row object
class Module_Model_Page_Row extends phpDataMapper_Model_Row
{
	
}