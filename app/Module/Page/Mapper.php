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

        // Step 1: Build index-based array of page IDs to their respective page objects
        foreach($pages as $page) {
            $index[$page->id] = $page;
        }

        // Step 2: Link tree children using built index with parent_id
        foreach($pages as $page) {
            if(isset($index[$page->parent_id])) {
                $ip = $index[$page->parent_id];
                $ip->children[] = $page;
            }
        }
        
        // Return only a portion of the tree
        /*
        if($startPage instanceof \Module\Page\Entity) {
            // Do stuff to return requested portion of tree
        } else {
            throw new \InvalidArgumentException("Provided start page must be an instance of Module\Page\Entity");
        }
        */

        return $this->buildPageTree($index);
    }


    /**
     * 
     */
    private function buildPageTree($index, $parentId = 0, $level = 0) {
        static $usedPages = array();
        $pages = isset($index[$parentId]) ? $index[$parentId]->children : $index;

        $items = array();
        //$itemCount = 0;
        foreach($pages as $p) {
            // Ensure we don't use a page more than once
            if(in_array($p->id, $usedPages)) {
                continue;
            }
            $usedPages[] = $p->id;

            //echo '<h2>' . str_repeat('-', $level) . ' ' . $p->title . ' (' . count($p->children) . ' children)</h2>';

            $hasChildren = isset($index[$p->id]);
            if($hasChildren) {
                $p->children = call_user_func(array($this, __FUNCTION__), $index, $p->id, $level+1);
            }

            // Add page to item array
            $items[] = $p;
            //$itemCount++;
        }
        return $items;
    }
}