<?php
class Module_User_Entity extends Cx_Module_Entity
{
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