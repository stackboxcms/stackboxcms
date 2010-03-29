<?php
// Custom entity object
class Module_User_Entity extends Cx_Module_Entity
{
	/**
	 * Is user logged-in?
	 */
	public function isLoggedIn()
	{
		return $this->id ? true : false;
	}
	
	
	/**
	 * Is user admin? (Has all rights)
	 */
	public function isAdmin()
	{
		return $this->is_admin ? true : false;
	}
	
	
	/**
	 * Return existing salt or generate new random salt if not set
	 */
	public function salt()
	{
		if(!$this->salt) {
			$length = 20;
			$string = "";
			$possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()-_+=";
			 
			for($i=0;$i < $length;$i++) {
				$char = $possible[mt_rand(0, strlen($possible)-1)];
				$string .= $char;
			}
			$this->salt = $string;
		}
		return $this->salt;
	}
	
	
	/**
	 * Encrypt password
	 *
	 * @param string $pass Password needing encryption
	 * @return string Encrypted password with salt
	 */
	public function encryptedPassword($pass)
	{
		// Hash = <salt>:<password>:<id>
		return sha1($this->salt() . ':' . $pass . ':' . $this->id);
	}
}