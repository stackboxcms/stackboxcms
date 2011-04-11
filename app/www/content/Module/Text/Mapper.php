<?php
namespace Module\Text;
use Stackbox;
use Spot;

class Mapper extends Stackbox\Module\MapperAbstract
{
    /**
     * Get current text entity
     */
    public function currentEntity(\Module\Page\Module\Entity $module)
    {
        $item = $this->all('Module\Text\Entity', array('module_id' => $module->id))
            ->order(array('id' => 'DESC'))
            ->first();
        if(!$item) {
            $item = $this->get('Module\Text\Entity');
        }
        return $item;
    }
}