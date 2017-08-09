<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_lower} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_lower
 * <br>
 * Purpose: Cambia a minúsculas una cadena de texto
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_lower {dk_lower} (Denko wiki)
 * @param string $string cadena de texto
 * @param string $charset charset
 * @return string
 */
################################################################################
function smarty_modifier_dk_lower($string,$charset = 'ISO-8859-1'){
    return Denko::lower($string,$charset);
}
################################################################################
?>