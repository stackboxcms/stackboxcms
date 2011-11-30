<?php
namespace Alloy;

/**
 * Application Class
 *
 * Base Alloy application class
 *
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class App
{
    /**
     * Constructor
     */
    public function __construct()
    {
        
    }


    /**
     * Is this a Flash request?
     * 
     * @return bool
     */
    public function isFlash()
    {
        return ($this->header('USER_AGENT') == 'Shockwave Flash');
    }
}