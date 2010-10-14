<?php
namespace Nijikodo\Language;

/**
 * Generic code processor
 *
 * @package Language
 * @subpackage Generic
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Generic
{
    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_css_prefix;

    /**
     * @var string
     */
    protected $_html;

    /**
     * @var bool
     */
    protected $_processed = false;

    /**
     * @var array
     */
    protected $_strings = array();

    /**
     * @var array
     */
    protected $_patterns = array();

    /**
     * instantiates code highlighting class
     *
     * @param string $code
     * @param string $css_prepend
     * @return void
     */
    public function __construct($code)
    {
        $this->_code = $this->_html = $code;
    }

    /**
     * sets css class to prepend
     *
     * @param string
     * @return Generic
     */
    public function setCssPrefix($prefix)
    {
        $this->_css_prefix = $prefix;
        return $this;
    }

    /**
     * preprocesses the code with some generic patterns
     *
     * @return void
     */
    protected function _preProcess()
    {
        $this->_addStringPattern();
        $this->_addMathPattern();
        $this->_addNumberPattern();
        $this->_addConstantsPattern();
        $this->_addKeywordPattern();
        $this->_addBoolPattern();
        $this->_addVarPattern();
    }

    /**
     * processes all the regex to convert this code to html
     *
     * @return Generic
     */
    public function process()
    {
        $this->_preProcess();

        $this->_runPatterns();
        $this->_processStrings();

        $this->_strings = array();
        $this->_patterns = array();

        $this->_processed = true;
        return $this;
    }

    /**
     * gets html output
     *
     * @return string
     */
    public function getHtml()
    {
        if ($this->_processed === false) {
            $this->process();
        }

        return $this->_html;
    }

    /**
     * output the class as a string
     *
     * @return string
     */
    public final function __toString()
    {
        return $this->getHtml();
    }

    /**
     * process all the regex patterns
     *
     * @return void
     */
    protected final function _runPatterns()
    {
        $this->_html = preg_replace(array_keys($this->_patterns), array_values($this->_patterns), $this->_html);
    }

    /**
     * process all strings and comments within the code block
     *
     * @return void
     */
    protected final function _processStrings()
    {
        $this->_html = str_replace(array_keys($this->_strings), array_values($this->_strings), $this->_html);
    }

    /**
     * adds a regex pattern
     *
     * @param string $pattern
     * @param string $replacement
     * @return void
     */
    protected final function _addPattern($pattern, $replacement)
    {
        $this->_patterns[$pattern] = $replacement;
    }

    /**
     * prepends a regex pattern to the front of the list
     *
     * @param string $pattern
     * @param string $replacement
     * @return void
     */
    protected final function prependPattern($pattern, $replacement)
    {
        $pattern = array($pattern => $replacement);
        $this->_patterns = array_merge($pattern, $this->_patterns);
    }

    /**
     * tokenize strings
     *
     * @param string $match
     * @return string
     */
    protected function _tokenize($match) {
        $key = '##' . uniqid() . '##';

        if (isset($match[0]) && ($match[0] == '/' || $match[0] == '#')) {
            $this->_strings[$key] = '<span class="' . $this->_css_prefix . 'comment">' . $match . '</span>';
            return $key;
        }

        $this->_strings[$key] = '<span class="' . $this->_css_prefix . 'string">' . $match . '</span>';
        return $key;
    }

    /**
     * regex for strings
     *
     * @return void
     */
    protected function _addStringPattern()
    {
        $this->_addPattern('/(\/\*.*?\*\/|\/\/.*?\n|\#.*?\n|(?<!\\\)&quot;.*?(?<!\\\)&quot;|(?<!\\\)\'(.*?)(?<!\\\)\')/isex', 'self::_tokenize(\'$1\')');
    }

    /**
     * regex for math stuff
     *
     * @return void
     */
    protected function _addMathPattern()
    {
        $this->_addPattern('/(&gt;=|&amp;|&lt;=|&gt;|&lt;(?![\?])|=(?![^<>]*>)|\+|\-|\*|[:]{2}|[\|]{2}|[&]{2}|\!)/', '<span class="' . $this->_css_prefix . 'keyword">$1</span>');
    }

    /**
     * regex for number pattern
     *
     * @return void
     */
    protected function _addNumberPattern()
    {
        $this->_addPattern('/(?<!\w)(0x[\da-f]+|\d+)(?!\w)/ix', '<span class="' . $this->_css_prefix . 'int">$1</span>');
    }

    /**
     * regex for constants
     *
     * @return void
     */
    protected function _addConstantsPattern()
    {
        $this->_addPattern('/(?<!\w|>|\$)([A-Z_0-9]{2,})(?!\w|\[)/x', '<span class="' . $this->_css_prefix . 'int">$1</span>');
    }

    /**
     * regex for keywords
     *
     * @return void
     */
    protected function _addKeywordPattern()
    {
        $this->_addPattern('/(?<!\w|\$|\%|\@|>|\\\)(and|or|xor|for|do|while|foreach|as|return|die|exit|if|then|else|
            elseif|new|delete|try|throw|catch|finally|endif|endforeach|endswitch|class|abstract|function|string|
            array|object|resource|var|bool|boolean|int|integer|float|double|
            real|string|array|global|const|case|break|continue|static|public|private|protected|
            published|extends|switch|void|this|self|struct|
            char|signed|unsigned|short|long)(?!\w|=")/ix', '<span class="' . $this->_css_prefix . 'keyword">$1</span>');
    }

    /**
     * regex for booleans
     *
     * @return void
     */
    protected function _addBoolPattern()
    {
        $this->_addPattern('/(?<!\w|\$|\%|\@|>)(true|false|null)(?!\w|=")/ix', '<span class="' . $this->_css_prefix . 'int">$1</span>');
    }

    /**
     * regex for variables
     *
     * @return void
     */
    protected function _addVarPattern()
    {
        $this->_addPattern('/(?<!\w)((\$|\%|\@)(\-&gt;|\w)+)(?!\w)/ix', '<span class="' . $this->_css_prefix . 'variable">$1</span>');
    }
}
