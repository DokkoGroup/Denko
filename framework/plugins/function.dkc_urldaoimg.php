<?php

/**
 * Funcion dkc_urldaoimg
 * Parámetros Obligatorios:
 *    - colimg: columna donde estará almacenada la imagen.
 * Parámetros Opcionales:
 *    - resize: parámetro que indica si la imagen será redimensionada [DEFAULT: false]
 *    - quality: porcentaje de calidad de la imagen, que vá desde el 0 al 100 [DEFAULT: 100]
 *    - width: ancho de la imagen [DEFAULT: ancho original]
 *    - height: alto de la imagen [DEFAULT: alto original]
 *    
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dkc_urldaoimg($params,&$smarty){
    if(empty($params['colimg'])){
        Denko::plugin_fatal_error('el parámetro <b>colimg</b> es requerido','dkc_urldaoimg');
    }
    if(!isset($params['action'])){
        $params['action'] = 'image';
    }
    if(!isset($params['resize'])){
        $params['resize'] = 'false';
    }
    if($params['resize'] == 'true' && (empty($params['width']) || empty($params['height']))){
        Denko::plugin_fatal_error('si <b>resize</b> es <b>true</b>, los parámetros <b>width</b> y <b>height</b> son requeridos','dkc_urldaoimg');
    }

    require_once $smarty->_get_plugin_filepath('function','dk_url');
    $daoLister = &DK_DAOLister::getDaoLister($smarty);
    $dao = &$daoLister->getDao();

    $id_dao = 'id_'.$dao->__table;
    $params[$id_dao] = $dao->$id_dao;

    return smarty_function_dk_url($params,$smarty);
}
###############################################################################