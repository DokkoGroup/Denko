<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_browser_update} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_browser_update
 * <br>
 * Purpose: Retorna el código para informar a los usuarios que deben actualizar el navegador.
 * <br>
 * Input:
 * <br>
 * - Opcionales:
 *   - assign = nombre de variable a la que asignará el valor retornado.
 * <br>
 * Examples:
 * <pre>
 * {dk_browser_update}
 * {dk_browser_update assign="browserUpdate"}
 * </pre>
 *
 * @author Denko Developers Group <info at dokkogroup dot com dot ar>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_browser_update {dk_browser_update}
 * @param Array $params parámetros
 * @param Smarty $smarty instancia de smarty
 * @return string
 */
################################################################################
function smarty_function_dk_browser_update($params, &$smarty) {
    $code = '<script> 
var $buoop = {c:2}; 
function $buo_f(){ 
  var e = document.createElement("script"); 
  e.src = "//browser-update.org/update.min.js"; 
  document.body.appendChild(e);
};
try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
catch(e){window.attachEvent("onload", $buo_f)}
</script>';
    # En caso de existir el parámetro assign, asigno el código al template
    if(!empty($params['assign'])){
        $smarty->assign($params['assign'],$code);
        return '';
    }
    # Retorno el código
    return $code;
}
################################################################################
