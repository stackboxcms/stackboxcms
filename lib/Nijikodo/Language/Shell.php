<?php
namespace Nijikodo\Language;

/**
 * Shell code processor
 *
 * @package Language
 * @subpackage Shell
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Shell extends Generic
{
    /**
     * regex rules for shell scripts
     *
     * @return void
     */
    protected function _preProcess()
    {
        parent::_addStringPattern();
        $this->_addPattern('/(&amp;&amp;|export)/', '<span class="' . $this->_css_prefix . 'keyword">$1</span>');
        $this->_addPattern('/((^|\s)(ls|ln|rm|wget|cd|mkdir|gzip|unzip|sh|tar|cat))/', '<span class="' . $this->_css_prefix . 'function">$1</span>');
    }
}
