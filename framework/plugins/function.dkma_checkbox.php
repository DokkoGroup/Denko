<?php
################################################################################
function smarty_function_dkma_checkbox($params, &$smarty){
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $checkBox = new DK_HTMLElement('input',false);
    $checkBox->addProperty('type','checkbox');
    foreach($params as $param => $value){
        $checkBox->addProperty($param,$value);
    }

    $dao = &$daoLister->getDAO();
    $keys = $dao->keys();
    $checkBox->addProperty('id',$dao->$keys[0]);
    $checkBox->addProperty('dkma','true');
    return $checkBox->html();
}
################################################################################