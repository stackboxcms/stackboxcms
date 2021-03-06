<?php
namespace Module\Page;
use Stackbox;
use Spot;

class Entity extends Stackbox\EntityAbstract
{
    const VISIBILITY_HIDDEN = 0;
    const VISIBILITY_VISIBLE = 1;


    // Table
    protected static $_datasource = "cms_pages";

    // Public property that will contain child pages when Mapper::pageTree() is called
    public $children = array();
    
    // Public property that will contain path to this page when Mapper::pageTree() is called
    public $id_path = '';
    public $is_in_path = false;
    
    /**
     * Fields
     */
    public static function fields() {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'site_id' => array('type' => 'int', 'default' => 0, 'unique' => 'site_page'),
            'parent_id' => array('type' => 'int', 'index' => true, 'default' => 0),
            'title' => array('type' => 'string', 'required' => true),
            'navigation_title' => array('type' => 'string'),
            'url' => array('type' => 'string', 'required' => true, 'unique' => 'site_page'),
            'meta_keywords' => array('type' => 'string'),
            'meta_description' => array('type' => 'string'),
            'template' => array('type' => 'string'),
            'ordering' => array('type' => 'int', 'length' => 4, 'default' => 0),
            'visibility' => array('type' => 'int', 'length' => 1, 'default' => self::VISIBILITY_VISIBLE),
            'date_created' => array('type' => 'datetime'),
            'date_modified' => array('type' => 'datetime')
        ) + parent::fields();
    }
    
    /**
     * Relations
     */
    public static function relations() {
        return array(
            /*
            // Subpages / hierarchy
            'children' => array(
                'type' => 'HasMany',
                'entity' => ':self',
                'where' => array('site_id' => ':entity.site_id', 'parent_id' => ':entity.id'),
                'order' => array('ordering' => 'ASC')
                ),
            */
            // Modules in regions on page
            'modules' => array(
                'type' => 'HasMany',
                'entity' => 'Module\Page\Module\Entity',
                'where' => array('site_id' => ':entity.site_id', 'page_id' => ':entity.id'),
                'order' => array('ordering' => 'ASC')
                )
        ) + parent::relations();
    }
    
    
    /**
     * Formats URL on save
     */
    public function beforeSave(Spot\Mapper $mapper)
    {
        $this->__set('site_id', \Kernel()->config('cms.site.id'));
        $this->__set('url', self::formatPageUrl($this->__get('url')));
        $this->__set('title', htmlentities($this->__get('title'), ENT_QUOTES, "UTF-8"));
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


    /**
     * Get templates available to page
     */
    public static function getPageTemplates()
    {
        $kernel = \Kernel();
        $site = $kernel->site();

        // Build array of theme directories to look in
        $tplDir = $kernel->config('cms.path.themes');
        $themeDirs = array();
        foreach($site->themes() as $theme) {
            // Global themes folder
            $themeDirs[] = rtrim($tplDir, '/') . '/' . $theme . '/';
            // Site themes folder
            $themeDirs[] = rtrim($site->dirThemes(), '/') . '/' . $theme . '/';
        }

        // Ensure directories exist before giving them to Finder
        foreach($themeDirs as $ti => $themeDir) {
            if(!is_dir($themeDir)) {
                unset($themeDirs[$ti]);
            }
        }

        // Ensure there is at least ONE directory if no others exist (fallback to default template)
        if(0 == count($themeDirs)) {
            $themeDirs[] = rtrim($tplDir, '/') . '/default/';
        }

        // Find template files
        $templates = $kernel->finder()
            ->in($themeDirs)
            ->files()
            ->name('*.html.tpl')
            ->depth(0)
            ->sortByName();
        
        $tpls = array();
        foreach($templates as $tpl) {
            // Remove path info
            $tplRelPath = str_replace($tplDir, '', $tpl->getPathname());
            $tplRelPath = str_replace($site->dirThemes(), '', $tpl->getPathname());
            // Remove extensions
            $tplRelPath = str_replace('.html.tpl', '', $tplRelPath);
            // Set in array to use
            $tpls[$tplRelPath] = $tplRelPath;
        }

        return $tpls;
    }


    /**
     * Is page visible?
     * 
     * @return boolean
     */
    public function isHomepage()
    {
        return ('/' == $this->url);
    }


    /**
     * Is page visible?
     * 
     * @return boolean
     */
    public function isVisible()
    {
        return ($this->visibility == self::VISIBILITY_VISIBLE);
    }


    /**
     * Navigation title
     */
    public function navigationTitle()
    {
        return $this->navigation_title ? $this->navigation_title : $this->title;
    }


    /**
     * Get and return settings in special collction with direct access to settings by 'setting_key' name
     */
    public function settings()
    {
        if(null !== $this->_settings) {
            return $this->_settings;
        }

        $kernel = \Kernel();
        $this->_settings = $kernel->mapper('Module\Settings\Mapper')->getSettingsForModule('page', $this->id);
        return $this->_settings;
    }


    /**
     * Get setting value by key name
     */
    public function setting($key, $default = null)
    {
        $settings = $this->settings();
        if($v = $settings->$key) {
            return $v;
        }
        return $default;
    }
}