<?php
abstract class Cx_Module_Mapper_Abstract extends Spot_Mapper
{
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
        $siteId = Alloy()->config('site.id');
		return parent::select($entityName, $fields)->where(array('site_id' => (int) $siteId));
	}
}