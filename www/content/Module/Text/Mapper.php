<?php
namespace Module\Text;

class Mapper extends \Cx\Module\MapperAbstract
{
    /**
     * Get current text entity
     */
    public function currentTextEntity(\Module\Page\Module\Entity $module)
    {
        $item = $this->all('Module\Text\Entity', array('module_id' => $module->id))->order(array('id' => 'DESC'))->first();
        if(!$item) {
            $item = $this->get('Module\Text\Entity');
        }
        return $item;
    }
}