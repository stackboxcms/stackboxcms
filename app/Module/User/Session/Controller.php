<?php
namespace Module\User\Session;
use Stackbox;

/**
 * User Session module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    const COOKIE_NAME = '_mcnc';


    /**
     * Access control list for controller methods
     */
    public function acl()
    {
        $acl = parent::acl();

        // Ensure users always have access to login
        $acl = array_merge_recursive($acl, array(
            'view' => array('new', 'newAction', 'post', 'postMethod', 'authenticate')
        ));

        return $acl;
    }


    /**
     * @method GET
     */
    public function indexAction($request)
    {
        return false;
    }
    
    
    /**
     * @method GET
     */
    public function newAction($request)
    {
        return $this->formView();
    }
    
    
    /**
     * Create a new user session
     * @method POST
     */
    public function postMethod($request)
    {
        $mapper = $this->kernel->mapper();
        
        // Get user by username first so we can get salt for hashing encrypted password
        $userTest = $mapper->first('Module\User\Entity', array('username' => $request->username));
        if(!$userTest || !$request->password) {
            return $this->formView()
                ->status(401)
                ->errors(array('username' => array('Incorrect username/password combination provided')));
        }
        
        // Test user login credentials
        $user = $mapper->first('Module\User\Entity', array(
            'username' => $request->username,
            'password' => $userTest->encryptedPassword($request->password)
            ));
        if(!$user) {
            return $this->formView()
                ->status(401)
                ->errors(array('username' => array('Incorrect username/password combination provided')));
        }
        
        // Create new session
        $session = $mapper->get('Module\User\Session\Entity');
        $session->user_id = $user->id;
        $session->session_id = session_id();
        $session->date_created = $mapper->connection('Module\User\Session\Entity')->dateTime();
        
        if($mapper->save($session)) {
            // Set session cookie and user object on Kernel
            $_SESSION['user']['session'] = $user->id . ":" . $session->session_id;
            $this->kernel->user($user);
            
            // Redirect to index
            return $this->kernel->redirect($this->kernel->url(array('page' => '/'), 'page'));
        } else {
            return $this->formView()
                ->status(401)
                ->errors($mapper->errors());
        }
    }
    
    
    /**
     * @method GET
     */
    public function deleteAction($request)
    {
        return $this->deleteMethod($request);
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request)
    {
        $user = $this->kernel->user();
        if(!$user) {
            throw new Alloy\Exception_FileNotFound("Unable to logout. User not logged in");
        }
        
        // Clear all session values for 'user'
        if(isset($_SESSION['user'])) {
            unset($_SESSION['user']);
            session_write_close();
        }

        // Delete logged-in cookie
        setcookie(static::COOKIE_NAME, '0', time()-28800);
        
        // Delete all sessions matched for current user
        $this->kernel->mapper()->delete('Module\User\Session\Entity', array('user_id' => $user->id));
        return $this->kernel->redirect($this->kernel->url(array('page' => '/'), 'page'));
    }
    
    
    /**
     * Authenticate user for given session key
     */
    public function authenticate($sessionKey = null)
    {
        $mapper = $this->kernel->mapper();
        $user = false;

        // Return user based on session key, if valid
        if($sessionKey && strpos($sessionKey, ':')) {
            list($userId, $userSession) = explode(':', $sessionKey);
            $userSession = $mapper->first('Module\User\Session\Entity', array('user_id' => $userId, 'session_id' => $userSession));
            if($userSession) {
                // Set cookie to flag logged in user (useful for cache busting)
                setcookie(static::COOKIE_NAME, '1', time()+28800); // 28,800 = 8 hours

                // Return user object
                return $mapper->get('Module\User\Entity', $userSession->user_id);
            }
        }
        
        // Return empty 'guest' user object
        if(!$user) {
            $user = $mapper->get('Module\User\Entity');
        }
        
        return $user;
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\User\Entity');
        $this->kernel->mapper()->migrate('Module\User\Session\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\User\Session\Entity');
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView($entityName = null)
    {
        $view = new \Alloy\View\Generic\Form('form');
        $view->action($this->kernel->url('login'))
            ->method('post')
            ->fields(array(
                'username' => array('type' => 'string', 'required' => true),
                'password' => array('type' => 'password', 'required' => true)
                ));
        return $view;
    }
}