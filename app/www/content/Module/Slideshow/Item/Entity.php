<?php
namespace Module\Slideshow\Item;
use Stackbox;

class Entity extends Stackbox\Module\EntityAbstract
{
    // Table
    protected static $_datasource = "module_slideshow_items";
    
    /**
     * Fields
     */
    public static function fields()
    {
        return array_merge(parent::fields(), array(
            'url' => array('type' => 'string', 'required' => true),
            'caption' => array('type' => 'string'),
            'link' => array('type' => 'string'),
            'ordering' => array('type' => 'int', 'length' => 4),
            'date_created' => array('type' => 'datetime'),
            'date_modified' => array('type' => 'datetime')
        ));
    }
}