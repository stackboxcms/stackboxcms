<?php
namespace Module\Text;
use Stackbox;

class Entity extends Stackbox\Module\EntityAbstract
{
    // Table
    protected static $_datasource = "module_text";
    
    /**
     * Fields
     */
    public static function fields()
    {
        return array(
            protected $content = array('type' => 'text', 'required' => true),
            protected $type = array('type' => 'string'),
            protected $date_created = array('type' => 'datetime'),
            protected $date_modified = array('type' => 'datetime')
        ) + parent::fields();
    }
}
