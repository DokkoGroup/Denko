<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_pad} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_pad
 * <br>
 * Purpose: Rellena una cadena de texto con caracteres, utilizando la función dk_pad de PHP
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_pad {dk_pad} (Denko wiki)
 * @param string $string cadena de texto
 * @param integer $longitud_relleno longitud que debe tener la cadena luego de ser rellenada
 * @param string $cadena_relleno caracter de relleno de la cadena
 * @param string $tipo_relleno modo en que se rellena la cadena [STR_PAD_RIGHT|STR_PAD_LEFT|STR_PAD_BOTH]
 * @return string
 */
################################################################################
function smarty_modifier_dk_pad($string , $longitud_relleno = 5, $cadena_relleno = '0', $tipo_relleno = STR_PAD_LEFT){
    return str_pad($string,$longitud_relleno,$cadena_relleno,$tipo_relleno);
}
################################################################################
?>