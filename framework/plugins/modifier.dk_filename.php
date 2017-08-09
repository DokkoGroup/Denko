<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_filename} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_filename
 * <br>
 * Purpose: Convierte una cadena a nombre válido de archivo
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_filename {dk_filename} (Denko wiki)
 * @param string $string cadena de texto
 * @return string
 */
################################################################################
function smarty_modifier_dk_filename($string, $replaceSpaces = '_'){
    return DK_File::filenameFormat($string,$replaceSpaces);
}
################################################################################
?>