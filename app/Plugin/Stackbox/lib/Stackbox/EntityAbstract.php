<?php
namespace Stackbox;
use Spot;

/**
 * Base Cont-xt Entity
 */
abstract class EntityAbstract extends Spot\Entity
{
	/**
	 * Base fields on all CMS module entities
	 */
	public static function fields()
	{
		// Site id for multi-site installations
		return array(
			'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
    		'site_id' => array('type' => 'int', 'index' => true, 'default' => 0)
		);
	}
    
    
    /**
     * Set site_id with current site based on config
     */
    public function beforeSave(Spot\Mapper $mapper)
    {
        $this->site_id = \Kernel()->config('site.id', 0);
    }
}