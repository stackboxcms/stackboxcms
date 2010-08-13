<?php
class Module_Page_Mapper extends Cx_Module_Mapper_Abstract
{
	/**
	 * Get current page by given URL
	 *
	 * @param string $url
	 */
	public function getPageByUrl($url)
	{
		return $this->first('Module_Page_Entity', array('url' => Module_Page_Entity::formatPageUrl($url)));
	}
	
	
	/**
	 * Return full tree of pages with all children nested properly
	 *
	 * @param string $url
	 */
	public function pageTree($startPage = null)
	{
		if(null === $startPage) {
			$rootPages = $this->all('Module_Page_Entity', array('parent_id' => 0))->order(array('ordering' => 'ASC'));
		} else {
			if($startPage instanceof Module_Page_Entity) {
				$rootPages = $startPage->children;
			} else {
				throw new Exception("Provided start page must be an instance of Module_Page_Entity");
			}
		}
		
		return $rootPages;
	}
}