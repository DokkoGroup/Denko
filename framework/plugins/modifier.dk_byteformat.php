<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_byteformat} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_byteformat
 * <br>
 * Purpose: Muestra un número en bytes, kilobytes o megabytes
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_byteformat {dk_byteformat} (Denko wiki)
 * @param string $string numero de bytes
 * @return string
 */
################################################################################
function smarty_modifier_dk_byteformat($string){

    $kilobytes = 1024;
    $megabytes = 1048576;

    if($string < $kilobytes){
        return $string.' bytes';
    }
    elseif($string < $megabytes){
        return number_format($string / $kilobytes,2,'.','').' KB';
    }
    return number_format($string / $megabytes,2,'.','').' MB';
}
################################################################################
?>