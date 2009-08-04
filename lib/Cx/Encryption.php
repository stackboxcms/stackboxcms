<?php
/**
 * Encryption Class
 * @version 1.0.0
 *
 * Encrypts and decrypts text strings
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 */
class Cx_Encryption
{
	protected $iv;
    protected $secureKey;
	
	const ENCRYPTION_METHOD = 'MCRYPT_RIJNDAEL_256';
	const ENCRYPTION_MODE = 'MCRYPT_MODE_ECB';
	
	
    public function __construct()
	{
		// Ensure server has the MCRYPT extension
		if(!extension_loaded('mcrypt')) {
			throw new RuntimeException('Required extension MCRYPT has not been found.  Please install the MCRYPT extension.');
		}
		
		$iv_size = mcrypt_get_iv_size(constant(self::ENCRYPTION_METHOD), constant(self::ENCRYPTION_MODE));
		$this->iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    }
	
	
	/**
	 *	Set encryption key
	 */
	public function setKey($key)
	{
		$this->secureKey = md5($key);
	}
	
	
	public function checkKey()
	{
		$keyOk = (bool) !empty($this->secureKey);
		if($keyOk) {
			return true;
		} else {
			throw new InvalidArgumentException('Encryption key has not been set.  Please set encryption key with ' . __CLASS__ . '::setKey() method.');
		}
	}
	
	
	/**
	 *	Encrypt text string
	 */
    public function encrypt($input)
	{
		$this->checkKey();
		$key = $this->secureKey;
		
		$rString = mcrypt_encrypt(constant(self::ENCRYPTION_METHOD), $this->secureKey, $input, constant(self::ENCRYPTION_MODE), $this->iv);
        return base64_encode($rString);
    }
	
	
	/**
	 *	Decrypt text string
	 */
    public function decrypt($input)
	{
		$this->checkKey();
		$rString = mcrypt_decrypt(constant(self::ENCRYPTION_METHOD), $this->secureKey, base64_decode($input), constant(self::ENCRYPTION_MODE), $this->iv);
        return trim($rString);
    }
}