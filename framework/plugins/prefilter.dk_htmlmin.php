<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_htmlmin} prefilter
 *
 * Type: prefilter
 * <br>
 * Name: dk_htmlmin
 * <br>
 * Purpose: Minifica el codigo html de los templates compilados
 * <br>
 * Example:
 * <pre>
 *     $smarty->load_filter('pre','dk_htmlmin');
 * </pre>
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @param string $tpl_souce Codigo fuente del template a minificar
 * @param Smarty &$smarty instancia de Smarty
 * @return string
 */
################################################################################
function smarty_prefilter_dk_htmlmin($tpl_source, &$smarty){
    return '{strip}'.$tpl_source.'{/strip}';
}
