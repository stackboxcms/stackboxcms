<?php
namespace Module\Blog;

class Post extends \Cx\Module\EntityAbstract
{
    protected static $_datasource = "module_blog_posts";
    
    // Fields
    protected $title = array('type' => 'string', 'required' => true);
    protected $description = array('type' => 'text');
    protected $status = array('type' => 'int', 'default' => 1, 'required' => true);
    protected $date_published = array('type' => 'datetime');
    protected $date_created = array('type' => 'datetime');
    protected $date_modified = array('type' => 'datetime');
}