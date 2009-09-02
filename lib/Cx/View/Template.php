<?php
/**
 * Template Parser Class
 * $Id$
 *
 * Parses view templates that are specific to the Back40 CMS
 *
 * @package Cont-xt Framework
 * @link http://www.cont-xt.com/
 */
class Cx_View_Template
{
	protected $content;
	protected $tagsList;
	
	// Template content regions
	protected $regionTagType = 'region';
	protected $foundRegions = array();
	
	
	/**
	 * Create new instance of class and optionally set template contents
	 *
	 * @param string $content
	 */
	public function __construct($content = null)
	{
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
	public function getTags()
	{
		if($this->tagsList) {
			return $this->tagsList;
		}
		
		// Get template content
		$content = $this->getContent();
		
		// Match all template tags
		$matches = array();
		preg_match_all("@<!--\{([\w]+)[:]*([^\s\}]*)(.*) \/\}-->@", $content, $matches, PREG_SET_ORDER);
		
		// Assemble list of tags by type
		$tagsList = array();
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
			$tagsList[] = array(
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
		
		$this->tagsList = $tagsList;
		return $tagsList;
	}
	
	
	/**
	 * Get array of found regions
	 *
	 * @return string
	 */
	public function getRegions()
	{
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