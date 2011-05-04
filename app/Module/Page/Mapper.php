<?php
namespace Module\Page;
use Stackbox;

class Mapper extends Stackbox\Module\MapperAbstract
{
    /**
     * Disables automatic adding of 'site_id' field to all queries in base mapper
     * @see Cx_Module_Mapper_Abstract
     */
    protected $_auto_site_id_query = false;
    
    
    /**
     * Get current page by given URL
     *
     * @param string $url
     */
    public function getPageByUrl($url)
    {
        return $this->first('Module\Page\Entity', array(
            'site_id' => \Kernel()->config('app.site.id'),
            'url' => Entity::formatPageUrl($url))
        );
    }
    
    
    /**
     * Return full tree of pages with all children nested properly
     *
     * @param string $url
     */
    public function pageTree($startPage = null)
    {
        if(null === $startPage) {
            $rootPages = $this->all('Module\Page\Entity', array(
                'site_id' => \Kernel()->config('app.site.id'),
                'parent_id' => 0))->order(array('ordering' => 'ASC')
            );
        } else {
            if($startPage instanceof \Module\Page\Entity) {
                $rootPages = $startPage->children;
            } else {
                throw new Stackbox\Exception("Provided start page must be an instance of Module\Page\Entity");
            }
        }
        
        return $rootPages;
    }
}