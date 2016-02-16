<?php

/**
 * class for undefined variable object
 * This class defines an object for undefined variable handling
 *
 * @package    Smarty
 * @subpackage Template
 */
class Smarty_Undefined_Variable
{
    /**
     * Returns FALSE for 'nocache' and NULL otherwise.
     *
     * @param  UTF8String $name
     *
     * @return bool
     */
    public function __get($name)
    {
        if ($name == 'nocache') {
            return false;
        } else {
            return null;
        }
    }

    /**
     * Always returns an empty string.
     *
     * @return UTF8String
     */
    public function __toString()
    {
        return "";
    }
}
