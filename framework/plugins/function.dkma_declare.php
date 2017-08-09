<?php

function smarty_function_dkma_declare($params,&$smarty){
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $daoLister->createMultiAction($params);
}
