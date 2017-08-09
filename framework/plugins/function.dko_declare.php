<?php

function smarty_function_dko_declare($params,&$smarty){
    if(empty($params['name'])){
        Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dko_declare');
    }
    if(empty($params['column']) && (empty($params['onAsc']) && empty($params['onDesc']))){
        Denko::plugin_fatal_error('se debe especificar el parámetro <b>column</b>, o los parámetros <b>onAsc</b> y <b>onDesc</b>','dko_declare');
    }
    $daolister = &DK_DAOLister::getDaoLister($smarty);
    $daolister->orderDeclare($params);
}
