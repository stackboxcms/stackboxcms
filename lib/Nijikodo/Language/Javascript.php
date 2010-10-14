<?php
namespace Nijikodo\Language;

/**
 * Javascript code processor
 *
 * @package Language
 * @subpackage Javascript
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Javascript extends Generic
{
    /**
     * regex rules for javascript
     *
     * @return void
     */
    protected function _preProcess()
    {
        parent::_addStringPattern();
        $this->_addPattern('/\$\(/', '<span class="' . $this->_css_prefix . 'keyword">$</span>(');
        $this->_addPattern('/(.*)(\.)(.*)(\s?=\s?)function/', '<span class="' . $this->_css_prefix . 'class">$1</span>$2$3$4function');
        $this->_addPattern('/(.*?(\s)?)=(\s)?function/', '<span class="' . $this->_css_prefix . 'method">$1</span>=$2function');
        $this->_addPattern('/(.*?(\s)?)\:(\s)?function/', '<span class="' . $this->_css_prefix . 'method">$1</span>:$2function');
        $this->_addPattern('/\./', '<span class="' . $this->_css_prefix . 'default">.</span>');
        $this->_addPattern('/(document|window)/', '<span class="' . $this->_css_prefix . 'class">$1</span>');

        // add the generic code handling stuff
        parent::_preProcess();
    }

    /**
     * overriding parent function to not do anything when we process the other stuff
     *
     * @return void
     */
    protected function _addStringPattern()
    {
    }
}
