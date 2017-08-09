<?php

function smarty_function_dkma_url($params, &$smarty){
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    if(!$daoLister->isSetMultiAction()){
        Denko::plugin_fatal_error('para acceder a las multiacciones, el multiaction debe crearse previamente.','dkma_url');
    }
    if(empty($params['type'])){
        Denko::plugin_fatal_error('el parámetro <b>type</b> es requerido','dkma_url');
    }
    $multiAction = &$daoLister->getMultiAction();
    $dkName = DAOListerMultiActionPrefix.$daoLister->getName();
    return 'javascript:dkma_select(\''.$dkName.'\',\''.$params['type'].'\');';
}
