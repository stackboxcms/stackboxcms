<?php
namespace Module\Page\Module\Settings;
use Stackbox;

class Collection extends Stackbox\EntityAbstract implements \Countable, \IteratorAggregate
{
    // Array of key => value settings
    protected $_settings = array();

    /**
     *
     */
    public function __construct(array $settings = array())
    {
        $this->_settings = $settings;
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
        return count($this->_settings);
    }
    public function getIterator()
    {
        return $this->_settings;
    }
}