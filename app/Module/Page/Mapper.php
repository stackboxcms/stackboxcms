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

    // Page tree cache for fully sorted page tree nested set
    protected static $_pageTree;
    
    
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
        // Get _ALL_ pages for current site - they will get sorted with PHP instead of the database
        // Only real way to make Adjacency model efficient and avoid all the SQL horror of storing hierarchy in relational databases
        $pages = $this->all('Module\Page\Entity')
            ->where(array('site_id' => \Kernel()->config('app.site.id')))
            ->order(array('parent_id' => 'ASC', 'ordering' => 'ASC'));
        
        $index = array();
        $tree  = array();

        // step 1: build index (note that I use &$row references!) 
        foreach($pages as $page) {
          $index[$page->id] = $page;
          if(!$page->parent_id) {
            $tree[] = $page;
          }
        }

        // step 2: link tree (references here as well!)
        foreach ($pages as $page) {
          $index[$page->parent_id]->children[] = $page;
        }

        //var_dump($tree);
        
        // Return only a portion of the tree
        /*
        if($startPage instanceof \Module\Page\Entity) {
            // Do stuff to return requested portion of tree
        } else {
            throw new \InvalidArgumentException("Provided start page must be an instance of Module\Page\Entity");
        }
        */

        return $tree;
    }
}