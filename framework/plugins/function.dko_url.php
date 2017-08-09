<?php

function smarty_function_dko_url($params,&$smarty){
    // Compruebo que esté seteado el parámetro 'nombre'
    if(empty($params['name'])){
        Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dko_url');
    }

    // Compruebo que esté seteado el parámetro 'order'
    if(empty($params['order'])){
        Denko::plugin_fatal_error('el parámetro <b>order</b> es requerido','dko_url');
    }

    // Compruebo que el parámetro 'order' sea 'asc' o 'desc'
    if($params['order'] != 'asc' && $params['order'] != 'desc'){
        Denko::plugin_fatal_error('el parámetro <b>order</b> debe ser <b>asc</b> o <b>desc</b>','dko_url');
    }

    $params['name'] = strtolower($params['name']);
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    return $daoLister->getOrderUrl($params);
}
