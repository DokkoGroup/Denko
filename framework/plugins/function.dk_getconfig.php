<?php
/**
 * Requeridos:
 * - name: nombre de la configuración
 * Opcionales:
 * - indice1:
 * - indice2:
 * - assign: en lugar de retornar un valor, lo asigna a una variable
 */
require_once '../commons/dokko_configurator.php';
################################################################################
/**
 * Funcion dk_getconfig (implementacion de getconfig para smarty)
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dk_getconfig($params, &$smarty) {

    # Verifico que esté el parámetro 'name'
    if(empty($params['name'])){
        Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dk_getconfig');
    }

    # Obtengo la configuración de la DB
    $config = getConfig($params['name'],!empty($params['indice1'])?$params['indice1']:null,!empty($params['indice2'])?$params['indice2']:null);

    # En caso que deba asignar el valor al template
    if(!empty($params['assign'])){
        $smarty->assign($params['assign'],$config);

        # No retorno HTML
        return '';
    }
    return $config;
}
################################################################################