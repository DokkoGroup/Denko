<?php
require_once '../denko/dk.daolister.php';
################################################################################
function smarty_block_dk_daolister($params, $content, &$smarty, &$repeat){

    # En caso que se invoque cuando se abra el bloque
    if($repeat == true){

        # Verifico que esté seteado el parámetro 'name'
        if(empty($params['name'])){
            Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dk_daolister');
        }

        # En caso que esté seteado, creo el DAOLister
        new DK_DAOLister($params);
    }

    # retorno el contenido del bloque
    return $content;
}
################################################################################