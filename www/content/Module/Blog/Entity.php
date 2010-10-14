<?php
namespace Module\Blog;

class Entity extends \Cx\Module\EntityAbstract
{
    protected $_datasource = "module_blog";
    
    // Fields
    protected $title = array('type' => 'string', 'required' => true);
    protected $description = array('type' => 'text');
}