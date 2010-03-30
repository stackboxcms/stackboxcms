<?php
class Module_User_Mapper extends Alloy_Module_Mapper
{
	// Table
	protected $source = "users";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true);
	public $username = array('type' => 'string', 'required' => true, 'unique' => true);
	public $password = array('type' => 'password', 'required' => true);
	public $salt = array('type' => 'string', 'length' => 20, 'required' => true);
	public $email = array('type' => 'string', 'required' => true);
	public $is_admin = array('type' => 'boolean', 'default' => 0);
	public $date_created = array('type' => 'datetime');
	
	// Custom entity class
	protected $_entityClass = 'Module_User_Entity';
	
	// User session/login
	public $session = array(
		'type' => 'relation',
		'relation' => 'HasOne',
		'mapper' => 'Module_User_Session_Mapper',
		'where' => array('user_id' => 'entity.id'),
		'order' => array('date_created' => 'DESC')
	);
}