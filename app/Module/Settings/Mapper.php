<?php
namespace Module\Settings;
use Stackbox;

class Mapper extends Stackbox\Module\MapperAbstract
{   
    /**
     * Get current page by given URL
     *
     * @param string $url
     */
    public function getSettingsForModule($moduleName, $moduleId)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();
        $settings = $mapper->all('Module\Settings\Entity')
            // Module name+id match
            ->where(array('site_id' => $kernel->site()->id, 'type' => $moduleName, 'type_id' => $moduleId))
            // Module name match with id of 0 (global module setting)
            ->orWhere(array('site_id' => $kernel->site()->id, 'type' => $moduleName, 'type_id' => 0));

        $settingsCollection = new \Module\Settings\Collection($settings);
        return $settingsCollection;
    }
}