<?php
class Module_User_Mapper extends Cx_Mapper_Abstract
{
	// Table
	protected $_datasource = "users";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
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
		'where' => array('user_id' => ':entity.id'),
		'order' => array('date_created' => 'DESC')
	);
	
	
	/**
	 * Save with salt and encrypted password
	 */
	public function beforeSave(Module_User_Entity $entity)
	{
		$data = $entity->data();
		$newData = $entity->dataModified();
		
		// If password has been modified or set for the first time
		if(!$entity->id || (isset($newData['password']) && $newData['password'] != $data['password'])) {
			$entity->salt = $entity->randomSalt();
			$entity->password = $entity->encryptedPassword($newData['password']);
		}
	}
}