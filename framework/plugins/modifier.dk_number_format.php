<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_number_format} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_number_format
 * <br>
 * Purpose: Formatea un número, aplicando la función number_format de PHP
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_number_format {dk_number_format} (Denko wiki)
 * @param string $string cadena de texto
 * @param integer $decimales cantidad de decimales
 * @param string $punto_dec punto decimal
 * @param string $sep_miles caracter de separación de miles
 * @return string
 */
################################################################################
function smarty_modifier_dk_number_format($string, $decimales = 2, $punto_dec = '.', $sep_miles = ''){
    return number_format($string,$decimales,$punto_dec,$sep_miles);
}
################################################################################
?>