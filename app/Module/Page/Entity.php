<?php
namespace Module\Page;

class Entity extends \Cx\EntityAbstract
{
    // Table
    protected static $_datasource = "pages";
    
    // Fields
    protected $id = array('type' => 'int', 'primary' => true, 'serial' => true);
    protected $site_id = array('type' => 'int', 'default' => 0, 'unique' => 'site_page');
    protected $parent_id = array('type' => 'int', 'index' => true, 'default' => 0);
    protected $title = array('type' => 'string', 'required' => true);
    protected $url = array('type' => 'string', 'required' => true, 'unique' => 'site_page');
    protected $meta_keywords = array('type' => 'string');
    protected $meta_description = array('type' => 'string');
    protected $theme = array('type' => 'string');
    protected $template = array('type' => 'string');
    protected $ordering = array('type' => 'int', 'length' => 3, 'default' => 0);
    protected $date_created = array('type' => 'datetime');
    protected $date_modified = array('type' => 'datetime');
    
    // Subpages / hierarchy
    protected $children = array(
        'type' => 'relation',
        'relation' => 'HasMany',
        'entity' => ':self',
        'where' => array('site_id' => ':entity.site_id', 'parent_id' => ':entity.id'),
        'order' => array('ordering' => 'ASC')
        );
    
    // Modules in regions on page
    protected $modules = array(
        'type' => 'relation',
        'relation' => 'HasMany',
        'entity' => 'Module\Page\Module\Entity',
        'where' => array('site_id' => ':entity.site_id', 'page_id' => ':entity.id'),
        'order' => array('ordering' => 'ASC')
        );
    
    
    /**
     * Formats URL on save
     */
    public function beforeSave(\Spot\Mapper $mapper)
    {
        $this->__set('site_id', \Kernel()->config('site.id'));
        $this->__set('url', self::formatPageUrl($this->__get('url')));
        return parent::beforeSave($mapper);
    }
    
    
    /**
     * Format a page URL by ensuring there is a begining and ending slash
     *
     * @param string $url
     * @return string
     */
    public static function formatPageUrl($url)
    {
        if(empty($url)) {
            $url = '/';
        } elseif($url != '/') {
            $url = '/' . trim($url, '/') . '/';
        }
        return $url;
    }
}