<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_basename} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_basename
 * <br>
 * Purpose: Devuelve la parte del path correspondiente al nombre del archivo
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_basename {dk_basename} (Denko wiki)
 * @param string $string path de archivo
 * @param string $sufijo sufijo del nombre de archivo
 * @return string
 */
################################################################################
function smarty_modifier_dk_basename($string,$sufijo = null){
    return $sufijo == null ? basename($string) : basename($string,$sufijo);
}
################################################################################
?>