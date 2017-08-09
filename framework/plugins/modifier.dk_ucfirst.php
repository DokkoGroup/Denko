<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_ucfirst} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_ucfirst
 * <br>
 * Purpose: Convierte a mayúsculas el primer caracter de una cadena de texto
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_ucfirst {dk_ucfirst} (Denko wiki)
 * @param string $string cadena de texto
 * @param string $charset charset
 * @return string
 */
################################################################################
function smarty_modifier_dk_ucfirst($string,$charset = 'ISO-8859-1'){
    return Denko::ucfirst($string,$charset);
}
################################################################################
?>