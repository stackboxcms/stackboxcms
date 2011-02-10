<?php
namespace Nijikodo;
require 'Language/Generic.php';

/**
 * server side library for syntax highlighting code snippets
 *
 * @package Nijikodo
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class Parser
{
    /**
     * @var string
     */
    const DEFAULT_CSS_PREFIX = 'niji_';

    /**
     * @var string
     */
    protected static $_css_prefix = self::DEFAULT_CSS_PREFIX;

    /**
     * @var array
     */
    protected static $_tokenized = array();

    /**
     * converts specific code block to html
     *
     * @param string $code
     * @param string $language (see ./Language for supported languages)
     * @param int $height (optional height if set it will limit the height of the output to that many pixels and add a scrollbar)
     */
    public static function toHtml($code, $language = null, $height = null)
    {
        $code = htmlspecialchars($code, ENT_COMPAT, 'UTF-8', false);
        $language = strtolower($language);

        switch ($language) {
            case 'php':
                require_once 'Language/Php.php';
                $code = new Language\Php($code);
                break;
            case 'html':
            case 'xml':
                require_once 'Language/Html.php';
                $code =  new Language\Html($code);
                break;
            case 'css':
                require_once 'Language/Css.php';
                $code = new Language\Css($code);
                break;
            case 'javascript':
                require_once 'Language/Javascript.php';
                $code = new Language\Javascript($code);
                break;
            case 'shell':
                require_once 'Language/Shell.php';
                $code = new Language\Shell($code);
                break;
            case 'apache':
                require_once 'Language/Apache.php';
                $code = new Language\Apache($code);
                break;
            case 'ini':
                require_once 'Language/Ini.php';
                $code = new Language\Ini($code);
                break;
            case 'text':
                require_once 'Language/Text.php';
                $code = new Language\Text($code);
                break;
            default:
                $code = new Language\Generic($code);
                break;
        }

        $code->setCssPrefix(static::$_css_prefix);

        // hate outputting html in php but that is the point of this library
        $output = '<div class="' . static::$_css_prefix . 'code';

        if ($language !== null) {
            $output .= ' ' . static::$_css_prefix . $language . '" style="';
        }

        // $output .= 'font-family: \'monaco\',courier,monospace; white-space: pre-wrap;';

        if ($height !== null) {
            $output .= ' height: ' . $height . 'px;';
        }

        $output .= '">' . $code . '</div>';

        return $output;
    }

    /**
     * sets class name to prepend to css classes
     *
     * classes will look like {$class}_int or {$class}_keyword or ${class}_variable
     *
     * @param string
     * @return void
     */
    public static function setCssPrefix($prefix = self::DEFAULT_CSS_PREFIX)
    {
        static::$_css_prefix = $prefix;
    }

    /**
     * takes text input and finds {code} blocks and turns them into pretty code
     *
     * @param string $text
     * @return string
     */
    public static function process($text, $use_pre_tag = false)
    {
        $text = static::tokenizeCodeBlocks($text);
        return static::replaceTokens($text, $use_pre_tag);
    }

    /**
     * takes text input and strips out code blocks so that other text can be processed
     *
     * @param string
     * @return string text without code blocks
     */
    public static function tokenizeCodeBlocks($text)
    {
        $text = preg_replace_callback('/\{code(:)?([^\}]+\b)?\}(.+?)(\{code\})(\n)?/is', 'static::_tokenizeCodeBlock', $text);
        return $text;
    }

    /**
     * puts tokenized code blocks back in
     *
     * @param string
     * @param bool
     * @return string
     */
    public static function replaceTokens($text, $use_pre_tag = false)
    {
        foreach (static::$_tokenized as $key => $value) {
            $text = str_replace($key, $use_pre_tag ? '<code><pre>' . $value . '</pre></code>' : $value, $text);
        }

        return $text;
    }

    /**
     * allows you to format code in a view with output buffering
     *
     * @return void
     */
    public static function captureStart()
    {
        ob_start();
    }

    /**
     * output what was captured
     *
     * @return string
     */
    public static function output()
    {
        $text = ob_get_contents();
        ob_end_clean();
        // replace code blocks with magic things we will replace later
        return static::process($text);
    }

    /**
     * tokenizes regex code match so we can replace it in the block of text later
     *
     * @param array $matches
     * @return string $token
     */
    protected static function _tokenizeCodeBlock($matches)
    {
        $language = isset($matches[2]) ? $matches[2] : null;

        $height = null;
        if (strpos($language, '|') !== false) {
            $bits = explode('|', $language);
            $language = $bits[0];
            $height = isset($bits[1]) ? str_replace('height:', '', $bits[1]) : null;
        }

        $code = $matches[3];

        $code = ltrim($code, "\n");

        $token = 'code:' . uniqid();
        $html = static::toHtml($code, $language, $height);

        static::$_tokenized[$token] = $html;

        return $token;
    }
}
