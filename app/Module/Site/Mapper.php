<?php
namespace Module\Site;
use Stackbox;

class Mapper extends Stackbox\Module\MapperAbstract
{
    /**
     * Disables automatic adding of 'site_id' field to all queries in base mapper
     * @see Stackbox\Module\MapperAbstract
     */
    protected $_auto_site_id_query = false;

    
    /**
     * Get current page by given URL
     *
     * @param string $url
     */
    public function getSiteByDomain($hostname)
    {
        $site = false;
        $domain = $this->first('Module\Site\Domain', array(
            'domain' => $hostname
        ));

        // If domain was found, get the site record
        if($domain) {
            $site = $this->first('Module\Site\Entity', array(
                'id' => $domain->site_id
            ));
        }

        return $site;
    }
}