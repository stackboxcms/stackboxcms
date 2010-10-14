<?php
namespace Cx\Module;

abstract class MapperAbstract extends \Spot\Mapper
{
    protected $_auto_site_id_query = true;
    
    
    /**
     * Begin a new database query - get query builder
     * Acts as a kind of factory to get the query builder object
     *
     * Override to modify queries so they are specifiec and limited to current active site
     *
     * @param string $entityName Name of the entity class
     * @param mixed $fields String for single field or array of fields
     */
    public function select($entityName, $fields = "*")
    {
        $query = parent::select($entityName, $fields);
        
        // Auto-add site_id to queries?
        if(true === $this->_auto_site_id_query) {
            $siteId = Kernel()->config('site.id');
            $query->where(array('site_id' => (int) $siteId));
        }
        
        return parent::select($entityName, $fields);
    }
}