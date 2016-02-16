<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFilter
 */

/**
 * Smarty htmlspecialchars variablefilter plugin
 *
 * @param UTF8String $source input string
 *
 * @return UTF8String filtered output
 */
function smarty_variablefilter_htmlspecialchars($source)
{
    return htmlspecialchars($source, ENT_QUOTES, Smarty::$_CHARSET);
}
