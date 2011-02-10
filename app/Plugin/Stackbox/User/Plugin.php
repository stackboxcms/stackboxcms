<?php
namespace Plugin\Stackbox\User;
use Alloy;

/**
 * Stackbox User Plugin
 * Enables User functionality and sets User helper on Kernel
 */
class Plugin
{
    protected $kernel;


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Add 'user' method to Kernel
        $kernel->addMethod('user', array($this, 'user'));
    }


    /**
     * User method added to Kernel
     */
    public function user()
    {
        $user = false;

        // User - Custom user code for authentication
        // ==================================
        $sessionKey = null;
        if(isset($_SESSION['user']['session'])) {
            $sessionKey = $_SESSION['user']['session'];
        }
        $user = $kernel->dispatch('User_Session', 'authenticate', array($sessionKey));
        if(!$user->isLoggedin() && isset($_SESSION['user']['session'])) {
            // Unset invalid user key
            unset($_SESSION['user']['session']);
        }
        // ==================================

        return $user;
    }
}