<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_upper} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_upper
 * <br>
 * Purpose: Cambia a mayúsculas una cadena de texto
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_upper {dk_upper} (Denko wiki)
 * @param string $string cadena de texto
 * @param string $charset charset
 * @return string
 */
################################################################################
function smarty_modifier_dk_upper($string,$charset = 'ISO-8859-1'){
    return Denko::upper($string,$charset);
}
################################################################################
?>