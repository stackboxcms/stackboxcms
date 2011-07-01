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
    protected $user;


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;
        
        // Add 'user' method to Kernel
        $kernel->addMethod('user', array($this, 'user'));
        $this->user();
    }


    /**
     * User method added to Kernel
     */
    public function user()
    {
        if(null === $this->user) {
            $user = false;

            // User - Custom user code for authentication
            // ==================================
            $sessionKey = null;
            if(isset($_SESSION['user']['session'])) {
                $sessionKey = $_SESSION['user']['session'];
            }
            $user = $this->kernel->dispatch('User_Session', 'authenticate', array($sessionKey));
            if(!$user->isLoggedin() && isset($_SESSION['user']['session'])) {
                // Unset invalid user key
                unset($_SESSION['user']['session']);
            }
            // ==================================

            // Ensure there is always a User object available if false
            if(!$user) {
                $user = $kernel->mapper()->create('Module\User\Entity', array('id' => 0));
            }

            $this->user = $user;
            return $user;
        }
        return $this->user;
    }
}