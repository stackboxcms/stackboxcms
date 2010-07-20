<?php
class Module_User_Entity extends Cx_Module_Entity
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
		'entity' => 'Module_User_Session_Entity',
		'where' => array('user_id' => ':entity.id'),
		'order' => array('date_created' => 'DESC')
	);
	
	
	/**
	 * Save with salt and encrypted password
	 */
	public function beforeSave(Spot_Mapper $mapper)
	{
		$data = $mapper->data($this);
		$newData = $mapper->dataModified($this);
		
		// If password has been modified or set for the first time
		if(!$this->id || (isset($newData['password']) && $newData['password'] != $data['password'])) {
			$this->salt = $this->randomSalt();
			$this->password = $this->encryptedPassword($newData['password']);
		}
	}
	
	
	/**
	 * Is user logged-in?
	 *
	 * @return boolean
	 */
	public function isLoggedIn()
	{
		return $this->id ? true : false;
	}
	
	
	/**
	 * Is user admin? (Has all rights)
	 *
	 * @return boolean
	 */
	public function isAdmin()
	{
		return (boolean) $this->is_admin;
	}
	
	
	/**
	 * Return existing salt or generate new random salt if not set
	 */
	public function randomSalt()
	{
		$length = 20;
		$string = "";
		$possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()[]{}<>-_+=|\/;:,.";
		$possibleLen = strlen($possible);
		 
		for($i=0;$i < $length;$i++) {
			$char = $possible[mt_rand(0, $possibleLen-1)];
			$string .= $char;
		}
		
		return $string;
	}
	
	
	/**
	 * Encrypt password
	 *
	 * @param string $pass Password needing encryption
	 * @return string Encrypted password with salt
	 */
	public function encryptedPassword($pass)
	{
		// Hash = <salt>:<password>
		return sha1($this->salt . ':' . $pass);
	}
}