<?php
namespace Nijikodo\Language;
include_once 'Php.php';

/**
 * Html code processor
 *
 * @package Language
 * @subpackage Html
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Html extends Generic
{
    /**
     * @var array
     */
    protected $_php_blocks = array();

    /**
     * regex rules for html
     *
     * @return void
     */
    protected function _preProcess()
    {
        $this->_addStringPattern();
        $this->_addPattern('/(&gt;)((.|\n)*?)(&lt;)/i', '&gt;<span class="' . $this->_css_prefix . 'default">$2</span>&lt;');
        $this->_addPattern('/(&lt;\!--)(.*?)(--&gt;)/', '<span class="' . $this->_css_prefix . 'comment">$1$2$3</span>');
    }

    /**
     * overriding parent process() so we can get php blocks
     *
     * @return Html
     */
    public function process()
    {
        $this->_tokenizePhp();
        parent::process();

        foreach ($this->_php_blocks as $key => $value) {
            $this->_html = str_replace($key, $value, $this->_html);
        }
    }

    /**
     * takes matching php block and converts to php code
     *
     * @param array $code matches from preg_replace_callback
     * @return string $token
     */
    protected function _processPhp($code)
    {
        $token = '$' . uniqid() . '$';
        $php = new Php($code[2]);
        $php->setCssPrefix($this->_css_prefix);
        $html = $code[1] . $php->getHtml() . $code[3];
        $this->_php_blocks[$token] = '<span class="' . $this->_css_prefix . 'default">' . $html . '</span>';
        return $token;
    }

    /**
     * takes html input and replaces php blocks with tokens
     *
     * @return void
     */
    protected function _tokenizePhp()
    {
        $this->_html = preg_replace_callback('/(&lt;\?)(.+?)(\?&gt;)/i', array($this, '_processPhp'), $this->_html);
    }
}
