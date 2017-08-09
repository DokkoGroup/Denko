<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_js_hide} block plugin
 *
 * Type: block
 * <br>
 * Name: dk_js_hide
 * <br>
 * Purpose: Ofusca un texto y lo muestra por javascript
 * <br>
 * Examples:
 * <pre>
 * {dk_js_hide}
 *    Este texto completo se va a mostrar por <span class="test">Javascript</span>
 * {/dk_js_hide}
 * </pre>
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20bloque%20dk_js_hide {dk_js_hide} (Denko wiki)
 * @param array $params parámetros
 * @param string $content En caso de que la etiqueta sea de apertura, este será null, si la etiqueta es de cierre el valor será del contenido del bloque del template
 * @param Smarty &$smarty instancia de Smarty
 * @param boolean &$repeat es true en la primera llamada de la block-function (etiqueta de apertura del bloque) y false en todas las llamadas subsecuentes
 * @return string
 */
################################################################################
function smarty_block_dk_js_hide($params,$content,&$smarty,&$repeat){
    if($content==null) return;
    return Denko::jsHide($content);    
}
################################################################################
?>
