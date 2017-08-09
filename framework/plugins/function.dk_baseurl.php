<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_baseurl} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_baseurl
 * <br>
 * Purpose: Retorna la URL base del sitio
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dokkogroup.com.ar/index.php/http://wiki.dojo/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_baseurl {dk_baseurl} (Denko wiki)
 * @param array $params parámetros
 * @param Smarty &$smarty instancia de Smarty
 * @return string
 */
################################################################################
function smarty_function_dk_baseurl($params, &$smarty){
    return Denko::getBaseUrl();
}
################################################################################
?>