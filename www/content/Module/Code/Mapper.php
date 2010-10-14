<?php
class Module_Code_Mapper extends Cx_Module_Mapper_Abstract
{
    /**
     * Get current text entity
     */
    public function currentEntity(Module_Page_Module_Entity $module)
    {
        $item = $this->all('Module_Code_Entity', array('module_id' => $module->id))->order(array('id' => 'DESC'))->first();
        if(!$item) {
            $item = $this->get('Module_Code_Entity');
        }
        return $item;
    }
}