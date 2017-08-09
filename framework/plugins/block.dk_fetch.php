<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_fetch} block plugin
 *
 * Type: block
 * <br>
 * Name: dk_fetch
 * <br>
 * Purpose: Se usa para fetchear un DAO
 * <br>
 * Input:
 * <br>
 * - Requeridos
 *   - dao = Dao que fetchear
 * <br>
 * Examples:
 * <pre>
 * <table cellpadding="2" cellspacing="2">
 * {dk_fetch dao=$daoUsuario}
 *     <tr>
 *         <td>nombre: {$daoUsuario->nombre}</td>
 *         <td>dni: {$daoUsuario->dni}</td>
 *         <td>sexo: {$daoUsuario->sexo}</td>
 *     </tr>
 * {/dk_fetch}
 * </table>
 * </pre>
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20bloque%20dk_fetch {dk_fetch} (Denko wiki)
 * @param array $params parámetros
 * @param string $content En caso de que la etiqueta sea de apertura, este será null, si la etiqueta es de cierre el valor será del contenido del bloque del template
 * @param Smarty &$smarty instancia de Smarty
 * @param boolean &$repeat es true en la primera llamada de la block-function (etiqueta de apertura del bloque) y false en todas las llamadas subsecuentes
 * @return string
 */
################################################################################
function smarty_block_dk_fetch($params, $content, &$smarty, &$repeat){
    $dao = $params['dao'];
    $dao instanceof DB_DataObject;
    $repeat = ($dao->fetch()) ? true : false;
    return $content;
}
################################################################################
?>