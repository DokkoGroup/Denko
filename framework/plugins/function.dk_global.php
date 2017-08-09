<?php

/**
 * Funcion dk_global
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dk_global($params, & $smarty) {
    $key=$params['var'];
    if(!isset($params['value'])){
        if(isset($params['assign'])){
            $smarty->assign($params['assign'],$GLOBALS[$key]);
            return '';
        }
        return $GLOBALS[$key];
    }
    $GLOBALS[$key]=$params['value'];
    return '';
}
