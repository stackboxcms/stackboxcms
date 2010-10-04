<?php
class Module_User_Session_Entity extends Cx_Entity_Abstract
{
	// Table
	protected $_datasource = "user_session";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $user_id = array('type' => 'int', 'key' => true, 'required' => true);
	public $session_id = array('type' => 'string', 'required' => true, 'key' => true);
	public $date_created = array('type' => 'datetime');
	
	// User session/login
	public $user = array(
		'type' => 'relation',
		'relation' => 'HasOne', // Actually a 'BelongsTo', but that is currently not implemented in Spot
		'entity' => 'Module_User_Entity',
		'where' => array('id' => ':entity.user_id')
	);
}