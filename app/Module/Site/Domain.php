<?php
namespace Module\Site;
use Stackbox;

/**
 * Site domain entity
 */
class Domain extends Stackbox\EntityAbstract
{
    const TYPE_NORMAL = 1;
    const TYPE_PRIMARY = 2;
    const TYPE_REDIRECT = 3;

    // Table
    protected static $_datasource = "site_domains";
    
    /**
     * Fields
     */
    public static function fields() {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'site_id' => array('type' => 'int', 'index' => true, 'default' => 0),
            'domain' => array('type' => 'string', 'required' => true),
            'type' => array('type' => 'int', 'length' => 1, 'default' => self::TYPE_NORMAL),
            'redirect_url' => array('type' => 'string'),
            'date_created' => array('type' => 'datetime')
        );
    }
}