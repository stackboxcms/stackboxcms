<?php
namespace Module\Text;

class Entity extends \Cx\Module\EntityAbstract
{
    // Table
    protected $_datasource = "module_text";
    
    // Fields
    protected $content = array('type' => 'text', 'required' => true);
    protected $type = array('type' => 'string');
    protected $date_created = array('type' => 'datetime');
    protected $date_modified = array('type' => 'datetime');
}
