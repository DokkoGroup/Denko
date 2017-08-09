<?php
/**
 * Funcion que invoca a una funcion de del DAO que se esta iterando en DaoLister. 
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dkc_callback($params,&$smarty){
    if(empty($params['function'])){
        Denko::plugin_fatal_error('el parámetro <b>function</b> es requerido','dkc_callback');
    }
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $dao = &$daoLister->getDao();
    $result = $dao->$params['function']($params);
    if(isset($params['var'])){
        $smarty->assign($params['var'],$result);
    }else{
        return $result;
    }
}
