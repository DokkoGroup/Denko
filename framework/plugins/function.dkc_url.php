<?php

/**
 * Funcion dkc_url
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dkc_url($params,&$smarty){
    require_once $smarty->_get_plugin_filepath('function','dk_url');
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $dao = &$daoLister->getDao();

    if(!isset($params['column'])){
        $params['column'] = 'id_'.$dao->__table;
    }
    if($dao->$params['column'] == null){
        return isset($params['default'])?$params['default']:'';
    }
    $params[$params['column']] = $dao->$params['column'];
    unset($params['column']);

    if(isset($params['confirmMessage'])){
        $confirm = $params['confirmMessage'];
        unset($params['confirmMessage']);
        return 'javascript:confirmAction(\''.str_replace('"','',str_replace("'","'",$confirm)).'\',\''.smarty_function_dk_url($params,$smarty).'\');';
    }

    return smarty_function_dk_url($params,$smarty);
}
