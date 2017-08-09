<?php
################################################################################
function smarty_function_dkp_hrefpage($params,&$smarty){

    # Verifico que esté seteado el parámetro 'number'
    if(empty($params['number'])){
        Denko::plugin_fatal_error('el parámetro <b>number</b> es requerido','dkp_hrefpage');
    }

    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $paramPage = $daoLister->getParamPage();
    $href = basename($_SERVER['PHP_SELF']).'?'.$paramPage.'='.$params['number'];

    #Armo la querystring con el resto de los parámetros GET
    $queryString = '';
    foreach($_GET as $param => $value){
        if($param == $paramPage) continue;
        $queryString.= '&'.$param.'='.htmlspecialchars($value);
    }

    # Retorno la url
    return $href.$queryString;
}
################################################################################