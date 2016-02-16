<?php

/**
 * Smarty compiler exception class
 *
 * @package Smarty
 */
class SmartyCompilerException extends SmartyException
{
    public function __toString()
    {
        return ' --> Smarty Compiler: ' . $this->message . ' <-- ';
    }

    /**
     * The line number of the template error
     *
     * @type int|null
     */
    public $line = null;
    /**
     * The template source snippet relating to the error
     *
     * @type UTF8String|null
     */
    public $source = null;
    /**
     * The raw text of the error message
     *
     * @type UTF8String|null
     */
    public $desc = null;
    /**
     * The resource identifier or template name
     *
     * @type UTF8String|null
     */
    public $template = null;
}
