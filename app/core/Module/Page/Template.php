<?php
/**
 * Page Template Parser
 * 
 * Parses page templates for tokens to replace with content
 *
 * @package Cont-xt
 * @link http://cont-xt.com/
 */
class Module_Page_Template
{
	protected $content;
	protected $tokens;
	
	// Template content regions
	protected $regionTagType = 'region';
	protected $foundRegions = array();
	
	
	/**
	 * Create new instance of class and optionally set template contents
	 * 
	 * @param string $content
	 */
	public function __construct($template)
	{
		$content = @file_get_contents($template);
		if(!$content) {
			throw new Module_Page_Template_Exception("Template not found: '" . $template . "'");
		}
		$this->setContent($content);
	}
	
	/**
	 * Set template contents
	 * 
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	
	/**
	 * Get template contents
	 * 
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}
	
	
	/**
	 * Parse template contents
	 * 
	 * @param $content string
	 */
	public function parse()
	{
		if($this->tokens) {
			return $this->tokens;
		}
		
		// Get template content
		$content = $this->getContent();
		
		// Match all template tags
		$matches = array();
		preg_match_all("@<!--\{([\w]+)[:]*([^\s\}]*)(.*) \/\}-->@", $content, $matches, PREG_SET_ORDER);
		
		// Assemble list of tags by type
		$tokens = array();
		foreach ($matches as $val) {
			$tagFull = $val[0];
			$tagNamespace = $val[1];
			$tagType = $val[2];
			$tagAttributes = trim($val[3]);
			$matchedAttributes = array();
			if(!empty($tagAttributes)) {
				// Match all attributes (key="value")
				preg_match_all("/([^\s=]*)=\"([^\"]*)\"/", $tagAttributes, $matchedAttributes, PREG_SET_ORDER);
				// Assemble tag attributes
				$tagAttributes = array();
				foreach($matchedAttributes as $attr) {
					$tagAttributes[$attr[1]] = $attr[2];
				}
			} else {
				$tagAttributes = array();
			}
			
			// Ouput array
			$tokens[] = array(
				'tag' => $tagFull,
				'ns' => $tagNamespace,
				'type' => $tagType,
				'attributes' => $tagAttributes
				);
			
			// Store found regions
			if($tagType == $this->regionTagType) {
				$this->foundRegions[] = $tagName;
			}
		}
		
		$this->tokens = $tagsList;
		return $tokens;
	}
	
	
	/**
	 * Get all found tags
	 * 
	 * @return array
	 */
	public function getTags()
	{
		// Parse template if is has not been parsed already
		if(!$this->tokens) {
			$this->parse();
		}
		
		return $this->tokens;
	}
	
	
	/**
	 * Get all found regions
	 * 
	 * @return array
	 */
	public function getRegions()
	{
		// Parse template if is has not been parsed already
		if(!$this->tokens) {
			$this->parse();
		}
		
		return $this->foundRegions;
	}
	
	
	/**
	 * Replace template tag with content
	 *
	 * @param $tag string
	 * @param $replacement string
	 */
	public function replaceTag($tag, $replacement)
	{
		$this->content = str_replace($tag, $replacement, $this->content);
	}
}