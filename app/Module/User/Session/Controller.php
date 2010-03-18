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
	 * Create a new resource with the given parameters
	 * @method POST
	 */
	public function postMethod($request)
	{
		$userMapper = $this->mapper('Module_User');
		
		// Get user by username first so we can get salt for hashing encrypted password
		$userTest = $userMapper->first(array(
			'username' => $request->username
			));
		if(!$userTest) {
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
			throw new Cx_Exception_FileNotFound("Unable to logout. User not logged in");
		}
		
		// Delete all sessions matched for current user
		return $this->mapper()->delete(array('user_id' => $user->id));
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