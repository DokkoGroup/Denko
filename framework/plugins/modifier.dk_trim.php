<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_trim} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_trim
 * <br>
 * Purpose: Elimina espacios en blanco de un string
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @param string $string cadena de texto
 * @param boolean $inside indica si debe buscar espacios dentro del string o solo en los bordes
 * @return string
 */
################################################################################
function smarty_modifier_dk_trim($string,$inside=false){
    if(!$inside) return trim($string);
    $string=str_replace(array("\x0B","\n","\r","\t"),' ',$string);
    $string=trim($string);
    
    while(($n=str_replace('  ',' ',$string))!=$string){
       $string=$n;
    }
    return $string;
}
################################################################################
?>
