<?php
namespace Module\Blog\Post;
use Stackbox;

class Entity extends Stackbox\Module\EntityAbstract
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    
    protected static $_datasource = "module_blog_posts";
    
    /**
     * Fields
     */
    public static function fields()
    {
        return array(
            'title' => array('type' => 'string', 'required' => true),
            'description' => array('type' => 'text'),
            'status' => array('type' => 'int', 'default' => self::STATUS_DRAFT),
            'date_created' => array('type' => 'datetime'),
            'date_modified' => array('type' => 'datetime'),
            'date_published' => array('type' => 'datetime')
        ) + parent::fields();
    }
}