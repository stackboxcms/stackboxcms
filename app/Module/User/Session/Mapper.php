<?php
class Module_User_Session_Mapper extends Alloy_Module_Mapper
{
	// Table
	protected $source = "user_session";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $user_id = array('type' => 'int', 'key' => true, 'required' => true);
	public $session_id = array('type' => 'string', 'required' => true, 'key' => true);
	public $date_created = array('type' => 'datetime');
}