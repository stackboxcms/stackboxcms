<?php
/**
 * Event Handling Plugin
 */
class Plugin_Event_Controller extends Cx_Plugin_Controller
{
	/**
	 * Trigger a named event and execute callbacks that have been hooked onto it
	 */
	public function trigger($name)
	{
		// Fire event
		
		$this->cx->trace('[Event] Fired: ' . $name);
	}
	
	
	/**
	 * Add callback to be triggered on event name
	 */
	public function bind($name, $hookName, $callback)
	{
		$this->cx->trace('[Event] Hook callback added: ' . $name);
	}
	
	
	/**
	 * Remove callback by name
	 */
	public function unbind($hookName)
	{
		$this->cx->trace('[Event] Hook callback added: ' . $name);
	}
}