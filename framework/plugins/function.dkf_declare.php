<?php

/**
 *
 */
function smarty_function_dkf_declare($params,&$smarty){
    if(empty($params['name'])){
        Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dkf_declare');
    }
    $daolister = &DK_DAOLister::getDaoLister($smarty);
    $daolister->filterDeclare($params);
}
