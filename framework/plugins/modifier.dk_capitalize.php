<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_capitalize} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_capitalize
 * <br>
 * Purpose: Capitaliza una cadena de texto
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_capitalize {dk_capitalize} (Denko wiki)
 * @param string $string cadena de texto
 * @param string $charset charset de la cadena de texto
 * @return string
 */
################################################################################
function smarty_modifier_dk_capitalize($string,$charset = 'ISO-8859-1'){
    return Denko::capitalize($string,$charset);
}
################################################################################
?>