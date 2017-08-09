<?php

/**
 * Funcion dkl_export
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dkl_export($params,&$smarty){
    if(empty($params['assign'])){
        Denko::plugin_fatal_error('el parámetro <b>assign</b> es requerido','dkl_export');
    }
    if(empty($params['column'])){
        Denko::plugin_fatal_error('el parámetro <b>column</b> es requerido','dkl_export');
    }
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $dao = &$daoLister->getDao();
    $smarty->assign($params['assign'],$dao->$params['column']);
}
