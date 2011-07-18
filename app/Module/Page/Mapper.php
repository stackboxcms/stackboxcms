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
    protected static $_pageIndex;
    
    
    /**
     * Get current page by given URL
     *
     * @param string $url
     */
    public function getPageByUrl($url)
    {
        return $this->first('Module\Page\Entity', array(
            'site_id' => \Kernel()->site()->id,
            'url' => Entity::formatPageUrl($url))
        );
    }
    
    
    /**
     * Return full tree of pages with all children nested properly
     *
     * @param string $url
     */
    public function pageTree(Entity $currentPage, $startPage = null)
    {
        // Return only a portion of the tree
        $startPageId = 0;
        if(null !== $startPage) {
            if($startPage instanceof \Module\Page\Entity) {
                $startPageId = $startPage->id;
            } elseif(is_numeric($startPage)) {
                $startPageId = $startPage;
            } else {
                throw new \InvalidArgumentException("Provided start page must be an instance of Module\Page\Entity");
            }
        }

        // Return cached page index
        if(null === self::$_pageIndex) {
            // Get _ALL_ pages for current site - they will get sorted with PHP instead of the database
            // Only real way to make Adjacency model efficient and avoid all the SQL horror of storing hierarchy in relational databases
            $pages = $this->all('Module\Page\Entity')
                ->where(array('site_id' => \Kernel()->site()->id))
                ->order(array('parent_id' => 'ASC', 'ordering' => 'ASC'));
            
            self::$_pageIndex = array();

            // Step 1: Build index-based array of page IDs to their respective page objects
            foreach($pages as $page) {
                // Mark page as 'current page'
                if($page->id == $currentPage->id) {
                    $page->is_in_path = true;
                }

                // Add to index by ID
                self::$_pageIndex[$page->id] = $page;
            }

            // Step 2: Link tree children using built index with parent_id's
            foreach($pages as $page) {
                if(isset(self::$_pageIndex[$page->parent_id])) {
                    // Set page children
                    self::$_pageIndex[$page->parent_id]->children[] = $page;

                    // Add to path to current page
                    self::$_pageIndex[$page->id]->id_path = trim(self::$_pageIndex[$page->parent_id]->id_path . '.' . $page->parent_id, '.');
                }
            }

            // Mark all pages in path of current page
            $currentPage = self::$_pageIndex[$currentPage->id];
            if($currentPage->id_path) {
                foreach(explode('.', $currentPage->id_path) as $pId) {
                    self::$_pageIndex[$pId]->is_in_path = true;
                }
            }
        }

        return $this->buildPageTree(self::$_pageIndex, $startPageId);
    }


    /**
     * Build a page tree from index-based array
     */
    private function buildPageTree($index, $parentId = 0, $level = 0)
    {
        static $usedPages = array();

        // Return empty array if parent is specified and cannot be found
        if(0 !== $parentId && !isset($index[$parentId])) {
            return array();
        }

        // Start with current page's children or at the top if no parent given
        $pages = ($parentId) ? $index[$parentId]->children : $index;

        $items = array();
        foreach($pages as $p) {
            // Ensure we don't use a page more than once
            if(in_array($p->id, $usedPages)) {
                continue;
            }
            $usedPages[] = $p->id;

            $hasChildren = isset($index[$p->id]);
            if($hasChildren) {
                $func = __FUNCTION__;
                $p->children = $this->$func($index, $p->id, $level+1);
            }

            // Add page to item array
            $items[] = $p;
        }

        // Clear used pages array
        if(0 === $level) {
            $usedPages = array();
        }

        return $items;
    }


    /**
     * Update page order and parent hierarchy structure
     */
    public function savePageOrder(array $pages)
    {
        $datasource = $this->datasource('Module\Page\Entity');
        $adapter = $this->connection('Module\Page\Entity');

        // Update each page
        $i = 0;
        foreach($pages as $id => $parentId) {
            $adapter->update($datasource, array(
                // SET
                'parent_id' => (int) $parentId,
                'ordering' => $i
                ),
                // WHERE
                array(
                    'id' => (int) $id
            ));
            $i++;
        }
    }
}