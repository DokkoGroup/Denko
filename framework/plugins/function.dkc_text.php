<?php

/**
 *
 */
function smarty_function_dkc_text($params,&$smarty){
    if(empty($params['column'])){
        Denko::plugin_fatal_error('el parámetro <b>column</b> es requerido','dkc_text');
    }
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $dao = &$daoLister->getDao();
    return $dao->$params['column']!=null?$dao->$params['column']:(isset($params['default'])?$params['default']:'');
}
###############################################################################