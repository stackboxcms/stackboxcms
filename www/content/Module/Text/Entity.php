<?php
class Module_Text_Entity extends Cx_Module_Entity_Abstract
{
    // Table
    protected $_datasource = "module_text";
    
    // Fields
    public $content = array('type' => 'text', 'required' => true);
    public $type = array('type' => 'string');
    public $date_created = array('type' => 'datetime');
    public $date_modified = array('type' => 'datetime');
}
