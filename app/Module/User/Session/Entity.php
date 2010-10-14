<?php
namespace Module\User\Session;

class Entity extends \Cx\EntityAbstract
{
    // Table
    protected $_datasource = "user_session";
    
    // Fields
    protected $id = array('type' => 'int', 'primary' => true, 'serial' => true);
    protected $user_id = array('type' => 'int', 'key' => true, 'required' => true);
    protected $session_id = array('type' => 'string', 'required' => true, 'key' => true);
    protected $date_created = array('type' => 'datetime');
    
    // User session/login
    protected $user = array(
        'type' => 'relation',
        'relation' => 'HasOne', // Actually a 'BelongsTo', but that is currently not implemented in Spot
        'entity' => 'Module\User\Entity',
        'where' => array('id' => ':entity.user_id')
    );
}