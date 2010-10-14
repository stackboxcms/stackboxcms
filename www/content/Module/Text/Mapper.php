<?php
class Module_Text_Mapper extends Cx_Module_Mapper_Abstract
{
    /**
     * Get current text entity
     */
    public function currentTextEntity(Module_Page_Module_Entity $module)
    {
        $item = $this->all('Module_Text_Entity', array('module_id' => $module->id))->order(array('id' => 'DESC'))->first();
        if(!$item) {
            $item = $this->get('Module_Text_Entity');
        }
        return $item;
    }
}