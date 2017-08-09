<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_basehref} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_basehref
 * <br>
 * Purpose: Obtiene el host base del sitio
 * <br>
 * Example:
 * <pre>
 *     <base href="{dk_basehref}" />
 * </pre>
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dokkogroup.com.ar/index.php/http://wiki.dojo/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_basehref {dk_basehref} (Denko wiki)
 * @param array $params parámetros
 * @param Smarty &$smarty instancia de Smarty
 * @return string
 */
################################################################################
function smarty_function_dk_basehref($params, &$smarty){
    return Denko::getBaseHref();
}
################################################################################
?>
