<?php
################################################################################
function smarty_block_dkp_ini($params, $content, &$smarty, &$repeat){

    # Obtengo el DAOLister actual
    $daoLister = DK_DAOLister::getDaoLister($smarty);

    # En caso de apertura del bloque, seteo las variables
    if($repeat == true){
        $daoLister->setPageVars($smarty);
    }

    # En caso de cierre del bloque, restauro las variables anteriores
    else{
        $daoLister->restorePageVars($smarty);
        return $content;
    }
}
################################################################################