<?php
namespace Module\User\Session;
use Stackbox;

/**
 * User Session module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
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
            $this->kernel->response(401);
            return $this->formView()->errors(array('username' => array('Incorrect username/password combination provided')));
        }
        
        // Test user login credentials
        $user = $mapper->first('Module\User\Entity', array(
            'username' => $request->username,
            'password' => $userTest->encryptedPassword($request->password)
            ));
        if(!$user) {
            $this->kernel->response(401);
            return $this->formView()->errors(array('username' => array('Incorrect username/password combination provided')));
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
            $this->kernel->response(401);
            return $this->formView()->errors($mapper->errors());
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
        
        // Delete all sessions matched for current user
        $this->kernel->mapper()->delete('Module\User\Entity', array('user_id' => $user->id));
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
     * @see Cx_Module_Controller_Abstract
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
     * @see Cx_Module_Controller_Abstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\User\Session\Entity');
    }
    
    
    /**
     * Return view object for the add/edit form
     */
    protected function formView()
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