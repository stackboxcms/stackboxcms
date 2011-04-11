<?php
namespace Module\Code;
use Stackbox;

class Entity extends Stackbox\Module\EntityAbstract
{
    // Table
    protected static $_datasource = "module_code";

    /**
     * Fields
     */
    public static function fields()
    {
        return array(
            'content' => array('type' => 'text', 'required' => true),
            'type' => array('type' => 'string'),
            'date_created' => array('type' => 'datetime'),
            'date_modified' => array('type' => 'datetime')
        ) + parent::fields();
    }
}