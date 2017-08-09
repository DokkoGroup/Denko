<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_friendly_url} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_friendly_url
 * <br>
 * Purpose: Convierte una URL en una friendly URL
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_friendly_url {dk_friendly_url} (Denko wiki)
 * @param string $string cadena de texto
 * @return string
 */
################################################################################
function smarty_modifier_dk_friendly_url($string){
    return Denko::str_to_friendlyUrl($string);
}
################################################################################
?>