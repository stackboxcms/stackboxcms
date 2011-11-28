<?php
namespace Stackbox\Module\Site;
use Stackbox;

abstract class EntityAbstract extends Stackbox\Module\EntityAbstract
{
    /**
     * Datasource getter/setter
     */
    public static function datasource($ds = null)
    {
        if(null !== $ds) {
            static::$_datasource = $ds;
            return $this;
        }

        // Override set datasource name with "site_<sitename>" to prevent global table conflicts
        return 'site_' . \Kernel()->site()->shortname . '_' . static::$_datasource;
    }
}