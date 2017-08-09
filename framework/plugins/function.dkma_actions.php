<?php

function smarty_function_dkma_actions($params, &$smarty){
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    if(!$daoLister->isSetMultiAction()){
        Denko::plugin_fatal_error('para acceder a las multiacciones, el multiaction debe crearse previamente.','dkma_actions');
    }
    $multiAction = &$daoLister->getMultiAction();
    return $multiAction->htmlSelect($params);
}
