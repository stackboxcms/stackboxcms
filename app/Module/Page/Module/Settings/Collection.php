<?php
namespace Module\Page\Module\Settings;
use Stackbox;

class Collection extends Stackbox\EntityAbstract implements \Countable, \IteratorAggregate
{
    // Array of key => value settings
    protected $_settings = array();
    protected $_settingsEntities;

    /**
     *
     */
    public function __construct($settings = array())
    {
        // Store resulting collection of entities
        $this->_settingsEntities = $settings;

        // Store as array key => value
        $this->_settings = $settings->toArray('setting_key', 'setting_value');
    }


    /**
     * Get setting value by key name
     */
    public function get($key, $default = null)
    {
        if(isset($this->_settings[$key])) {
            return $this->_settings[$key];
        }
        return $default;
    }
    public function __get($key)
    {
        return $this->get($key);
    }


    /**
     * Settings array (key => value pairs)
     */
    public function toArray()
    {
        return $this->_settings;
    }


    /**
     * SPL Methods
     */
    public function count()
    {
        return count($this->_settingsEntities);
    }
    public function getIterator()
    {
        return $this->_settingsEntities;
    }
}