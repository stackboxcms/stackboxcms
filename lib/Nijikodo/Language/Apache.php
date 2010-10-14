<?php
namespace Nijikodo\Language;

/**
 * Apache code processor
 *
 * @package Language
 * @subpackage Apache
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Apache extends Generic
{
    /**
     * regex rules for apache
     *
     * @return void
     */
    protected function _preProcess()
    {
        $this->_addStringPattern();
        $this->_addNumberPattern();
        $this->_addMathPattern();
        $this->_addPattern('/(SetEnv)/', '<span class="' . $this->_css_prefix . 'function">$1</span>');
    }
}
