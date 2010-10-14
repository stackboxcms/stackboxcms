<?php
namespace Cx;

/**
 * Base Cont-xt Entity
 */
abstract class EntityAbstract
{
    // Site id for future multi-site possibilities
    protected $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
    
    
    /**
     * Set site_id with current site based on config
     */
    public function beforeSave(\Spot\Mapper $mapper)
    {
        $this->site_id = \Kernel()->config('site.id', 0);
    }
}