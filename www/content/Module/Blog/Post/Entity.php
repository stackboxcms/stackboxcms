<?php
namespace Module\Blog\Post;

class Entity extends \Cx\Module\EntityAbstract
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    
    protected static $_datasource = "module_blog_posts";
    
    // Fields
    protected $title = array('type' => 'string', 'required' => true);
    protected $description = array('type' => 'text');
    protected $status = array('type' => 'int', 'default' => self::STATUS_DRAFT);
    protected $date_created = array('type' => 'datetime');
    protected $date_modified = array('type' => 'datetime');
    protected $date_published = array('type' => 'datetime');
}