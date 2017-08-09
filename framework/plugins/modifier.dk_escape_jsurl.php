<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_escape_jsurl} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_escape_jsurl
 * <br>
 * Purpose: Escapa una URL para ser usada en javascript
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_escape_jsurl {dk_escape_jsurl} (Denko wiki)
 * @param string $string url
 * @return string
 */
################################################################################
function smarty_modifier_dk_escape_jsurl($string){
    return urlencode(urlencode($string));
}
################################################################################
?>