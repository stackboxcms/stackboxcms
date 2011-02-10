<?php
namespace Stackbox;

class Kernel extends \Alloy\Kernel
{
    /**
    * Dispatch module action - Override for CMS module auto-install
    *
    * @param string $moduleName Name of module to be called
    * @param optional string $action function name to call on module
    * @param optional array $params parameters to pass to module function
    *
    * @return mixed String or object that has __toString method
    */
    public function dispatch($module, $action = 'index', array $params = array())
    {
        try {
            // Attempt parent dispatch
            $result = parent::dispatch($module, $action, $params);
            
        // Database table/datasource missing - attempt to autoinstall CMS module
        } catch(\Spot\Exception_Datasource_Missing $e) {
            $module = $this->module($module, false); // Don't run init function for install
            $result = $this->dispatch($module, 'install', array($action, $params));
        }
        
        return $result;
    }
}