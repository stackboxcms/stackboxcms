<?php
namespace Stackbox\Module;
use Stackbox;

abstract class EntityAbstract extends Stackbox\EntityAbstract
{
    /**
     * Base fields on all CMS module entities
     */
    public static function fields()
    {
        // Add moudle_id and page_id automatically
        return array_merge(parent::fields(), array(
            'page_id' => array('type' => 'int', 'index' => true, 'default' => 0),
            'module_id' => array('type' => 'int', 'index' => true, 'required' => true)
        ));
	}    
}