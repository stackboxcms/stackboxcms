<?php
class Module_Page_Entity extends Cx_Module_Entity
{
	/**
	 * Setter override: 'url'
	 * Formats URL string to have both beginning and trailing slashes
	 */
	public function set_url($url)
	{
		if(empty($url)) {
			$url = '/';
		} elseif($url != '/') {
			$url = '/' . trim($url, '/') . '/';
		}
		$this->_data['url'] = $url;
	}
}