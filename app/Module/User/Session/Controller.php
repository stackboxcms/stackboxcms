<?php
/**
 * User Session module
 */
class Module_User_Session_Controller extends Alloy_Module_Controller
{
	protected $_file = __FILE__;
	
	
	/**
	 * @method GET
	 */
	public function indexAction($request)
	{
		// User list? Maybe later...
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
		$userMapper = $this->mapper('Module_User');
		
		// Get user by username first so we can get salt for hashing encrypted password
		$userTest = $userMapper->first(array('username' => $request->username));
		if(!$userTest || !$request->password) {
			$this->kernel->response(401);
			return $this->formView()->errors(array('username' => array('Incorrect username/password combination provided')));
		}
		
		// Test user login credentials
		$user = $userMapper->first(array(
			'username' => $request->username,
			'password' => $userTest->encryptedPassword($request->password)
			));
		if(!$user) {
			$this->kernel->response(401);
			return $this->formView()->errors(array('username' => array('Incorrect username/password combination provided')));
		}
		
		// Create new session
		$sessionMapper = $this->mapper();
		
		$session = $sessionMapper->get();
		$session->user_id = $user->id;
		$session->session_id = session_id();
		$session->date_created = date($sessionMapper->adapter()->dateTimeFormat());
		
		if($sessionMapper->save($session)) {
			// Set session cookie and user object on Kernel
			$_SESSION['user']['session'] = $user->id . ":" . $session->session_id;
			$this->kernel->user($user);
			
			// Redirect to index
			return $this->kernel->redirect($this->kernel->url('page', array('page' => '/')));
		} else {
			$this->kernel->response(401);
			return $this->formView()->errors($sessionMapper->errors());
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
			throw new Alloy_Exception_FileNotFound("Unable to logout. User not logged in");
		}
		
		// Clear all session values for 'user'
		if(isset($_SESSION['user'])) {
			unset($_SESSION['user']);
			session_write_close();
		}
		
		// Delete all sessions matched for current user
		return $this->mapper()->delete(array('user_id' => $user->id));
	}
	
	
	/**
	 * Authenticate user for given session key
	 */
	public function authenticate($sessionKey = null)
	{
		$userSessionMapper = $this->mapper();
		$userMapper = $this->mapper('Module_User');
		$user = false;
		
		// Return user based on session key, if valid
		if($sessionKey && strpos($sessionKey, ':')) {
			list($userId, $userSession) = explode(':', $sessionKey);
			$userSession = $userSessionMapper->first(array('user_id' => $userId, 'session_id' => $userSession));
			if($userSession) {
				return $userMapper->get($userSession->user_id);
			}
		}
		
		// Return empty 'guest' user object
		if(!$user) {
			$user = $userMapper->get();
		}
		
		return $user;
	}
	
	
	/**
	 * Return view object for the add/edit form
	 */
	protected function formView()
	{
		$fields = $this->mapper('Module_User')->fields();
		
		$view = new Alloy_View_Generic_Form('form');
		$view->action($this->kernel->url('login'))
			->method('post')
			->fields(array(
				'username' => array('type' => 'string', 'required' => true),
				'password' => array('type' => 'password', 'required' => true)
				));
		return $view;
	}
}