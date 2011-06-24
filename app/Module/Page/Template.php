<?php
namespace Module\Page;
use Alloy;

/**
 * Page Template Parser
 * 
 * Parses page templates for tokens to replace with content
 *
 * @package Stackbox CMS
 * @link http://stackboxcms.com/
 */
class Template extends Alloy\View\Template
{
    // Extension type (inherited from \Alloy\View\Template)
    protected $_default_format = 'html';
    protected $_default_extenstion = 'tpl';
    
    // Parsing storage
    protected $_dom;
    protected $_content;
    protected $_tokens;
    
    // Special predefined types
    protected $_tokenTagType = 'tag';
    protected $_tags = array();
    protected $_tokenRegionType = 'region';
    protected $_regions = array();
    protected $_regionsType = array();
    
    
    /**
     * Parse template contents
     * 
     * @param $content string
     */
    public function parse()
    {
        if($this->_tokens) {
            return $this->_tokens;
        }
        
        // Get template content
        $content = $this->content();
        
        // Ensure errors due to malformed HTML document will not throw PHP errors
        libxml_use_internal_errors(true);

        // Parse template with DOMDocument
        $dom = new \DOMDocument();
        $dom->registerNodeClass('DOMElement', 'Module\Page\Template\DOMElement');
        $dom->loadHTML($content);

        // Clear internal error buffer (free memory)
        libxml_clear_errors();

        // REGIONS
        $tokens = array();
        $xpath = new \DOMXPath($dom);
        $regions = $xpath->query("//*[contains(@class, 'cms_region') or contains(@class, 'cms_region_main') or contains(@class, 'cms_region_global')]");
        foreach($regions as $region) {

            $regionName = $region->getAttribute('id');
            $regionClass = $region->getAttribute('class');
            $regionType = (false !== strpos($regionClass, 'cms_region_global')) ? 'global' : 'page';
            if(!$regionName) {
                throw new Template\Exception("Template region does not have an id attribute.\n<br /> Parsing (" . \htmlentities($region->saveHTML()) . ")");
            }

            // Ouput array
            $token = array(
                'element' => $region,
                'type' => $this->_tokenRegionType,
                'namespace' => 'cms',
                'attributes' => $region->getAttributes(),
                'content' => $region->innerHTML
            );
            
            $this->_regions[$regionName] = $token;
            $this->_regionsType[$regionType][] = $regionName;
            
            $tokens[] = $token;
        }


        // TAGS
        $xpath = new \DOMXPath($dom);
        $tags = $xpath->query("//*[contains(@class, 'cms_tag_')]");
        foreach($tags as $tag) {
            $tagName = str_replace('cms_tag_', '', $tag->getAttribute('class'));

            $token = array(
                'element' => $tag,
                'type' => $this->_tokenTagType,
                'namespace' => 'cms',
                'attributes' => $tag->getAttributes(),
                'content' => $tag->innerHTML
            );
            $this->_tags[$tagName][] = $token;

            $tokens[] = $token;
        }
        
        $this->_dom = $dom;
        $this->_tokens = $tokens;
        return $tokens;
    }
    
    
    /**
     * Get all found tokens, regardless of type
     * 
     * @return array
     */
    public function tokens()
    {
        // Parse template if is has not been parsed already
        if(!$this->_tokens) {
            $this->parse();
        }
        
        return $this->_tokens;
    }
    
    
    /**
     * Get all found tags
     * 
     * @return array
     */
    public function tags()
    {
        // Parse template if is has not been parsed already
        if(!$this->_tags) {
            $this->parse();
        }
        
        return $this->_tags;
    }
    
    
    /**
     * Get all found regions
     * 
     * @return array
     */
    public function regions()
    {
        // Parse template if is has not been parsed already
        if(!$this->_regions) {
            $this->parse();
        }
        
        return $this->_regions;
    }
    
    
    /**
     * Get all found global regions
     * 
     * @return array
     */
    public function regionsType($type)
    {
        // Parse template if is has not been parsed already
        if(!$this->_regionsType) {
            $this->parse();
        }
        
        return isset($this->_regionsType[$type]) ? $this->_regionsType[$type] : array();
    }
    
    
    /**
     * Cleans up template and replaces content with current DOM tree
     */
    public function clean()
    {
        $this->_content = $this->_dom->saveHTML();

        // Remove unused tags
        foreach($this->tags() as $tags) {
            foreach($tags as $tag) {
                $el = $tag['element'];
                $this->_content = str_replace($el->saveHTML(), '', $this->_content);
            }

            //var_dump($el->saveHTML());
            //$el->replaceWith($tag['content']);
            //$el->innerHTML = $tag['content'];
        }
    }
    
    
    /**
     * Replace given tag with content
     *
     * @param $tag string Full token string to replace
     * @param $replacement string
     * 
     * @return boolean
     */
    public function replaceToken($tag, $replacement)
    {
        $this->_content = str_replace($tag, $replacement, $this->content());
        return true;
    }
    
    
    /**
     * Replace template tag with content
     *
     * @param $tagName string Name of template tag to replace
     * @param $replacement string
     * 
     * @return boolean
     */
    public function replaceTag($tagName, $replacement)
    {
        $tags = $this->tags();
        if(isset($tags[$tagName])) {
            $tags = $tags[$tagName];
            foreach($tags as $tag) {
                //$this->_content = str_replace($tags[$tagName]['tag'], $replacement, $this->content());
                $el = $tag['element'];
                $el->replaceWith($replacement);
            }
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Place content inside template region
     *
     * @param $regionName string Name of template region to replace
     * @param $content string
     * 
     * @return boolean
     */
    public function regionContent($regionName, $content)
    {
        $regions = $this->regions();
        if(isset($regions[$regionName])) {
            //$region = $this->_dom->getElementById($regionName);
            $region = $regions[$regionName]['element'];
            $region->innerHTML = $content;
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * Returns full template filename with format and extension
     *
     * @param OPTIONAL $template string (Name of the template to return full file format)
     * @return string
     */
    public function templateFilename($template = null)
    {
        if(null === $template) {
            $template = $this->template();
        }
        return $template . '.' . $this->_default_extenstion . '.' . $this->format();
    }
    
    
    /**
     * Set template contents
     * 
     * @param string $content
     * @return string
     */
    public function content($parsePHP = true)
    {
        if(null === $this->_content) {
            $this->_content = parent::content(false);
        }
        return $this->_content;
    }
    
    
    /**
     * Converts view object to string on the fly
     *
     * @return string
     */
    public function __toString()
    {
        $this->clean();
        return parent::__toString();
    }
}