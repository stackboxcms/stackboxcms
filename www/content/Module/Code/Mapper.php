<?php
namespace Module\Code;
use Stackbox;

class Mapper extends Stackbox\Module\MapperAbstract
{
    /**
     * Get current text entity
     */
    public function currentEntity(\Module\Page\Module\Entity $module)
    {
        $item = $this->all('Module\Code\Entity', array('module_id' => $module->id))
            ->order(array('id' => 'DESC'))
            ->first();
        if(!$item) {
            $item = $this->get('Module\Code\Entity');
        }
        return $item;
    }
}