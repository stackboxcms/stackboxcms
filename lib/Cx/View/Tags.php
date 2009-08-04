<?php	
/**
 * Absrtact View Class
 * $Id$
 *
 * View abstract class with some global helper functions like linking relationships
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */
class Cx_View_Tags
{
	const TAG_START = '{{';
	const TAG_END = '}}';
	const TAG_COLLECTION_SEPARATOR = '.';
	
	protected $tags = array();
	
	
	/**
	 * Add tags from collection and prefix them with given prefix
	 */
	public function addTagsFromCollection($prefix, array $replacements)
	{
		foreach($replacements as $tag => $replacement) {
			$key = self::TAG_START . $prefix . self::TAG_COLLECTION_SEPARATOR . $tag . self::TAG_END;
			$this->tags[$key] = $replacement;
		}
	}
	
	
	/**
	 * Perform tag replacement on given text
	 */
	public function parse($text)
	{
		$tags = array_keys($this->tags);
		$replacements = array_values($this->tags);
		return str_replace($tags, $replacements, $text);
	}
}