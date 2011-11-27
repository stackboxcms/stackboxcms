<?php
namespace Module\Site;
use Stackbox, Spot;

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
            'site_id' => array('type' => 'int', 'index' => true, 'required' => true),
            'domain' => array('type' => 'string', 'required' => true),
            'type' => array('type' => 'int', 'length' => 1, 'default' => self::TYPE_NORMAL),
            'redirect_url' => array('type' => 'string'),
            'date_created' => array('type' => 'datetime', 'default' => new \DateTime())
        );
    }


    /**
     * Overridden without calling parent so base Entity will not try to set 'site_id' automatically
     */
    public function beforeSave(Spot\Mapper $mapper) {}
}