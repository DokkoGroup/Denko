<?php
require_once '../denko/dk.daolister.php';

################################################################################
/**
 * Bloque DK_Lister
 * @param Array $params
 * @param String $content
 * @param Smarty $smarty
 * @param Boolean $repeat
 * @return String
 */

function smarty_block_dk_lister($params, $content, &$smarty, &$repeat) {
    $daoLister = DK_DAOLister::getDaoLister($smarty);
    if (! $daoLister->fetch()){
        $repeat = false;
        return $content . ($daoLister->isSetMultiAction() ? '
                </form>' : '');
    }
    //////////////// Seteo los valores de los campos ///////////////////////
    $dao = $daoLister->getDao();
    
    # En caso que se especifique que campos deben setearse en el template
    if (!isset($params ['export'])){
    	$array = array_keys($dao->table());
    } else {
	    $array = explode(',', $params ['export']);
    }
    
    foreach ( $array as $var ){
        $campo_nombre = trim($var);
        if (!empty($campo_nombre)){
            $smarty->assign($campo_nombre, $dao->$campo_nombre);
        }
    }
    
    if ($daoLister->isSetMultiAction() && $repeat == true){
        require_once $smarty->_get_plugin_filepath('function', 'dk_include');
        $multiAction = $daoLister->getMultiAction();
        $formName = DAOListerMultiActionPrefix . $daoLister->getName();
        echo smarty_function_dk_include(array ('file' => 'js/dk.multiaction.js', 'inline' => false, 'compress' => true ), $smarty) . '
                <form name="' . $formName . '" action="' . basename($_SERVER ['PHP_SELF']) . '" method="get">
                    ' . $multiAction->htmlHiddenInputs();
    }
    $repeat = true;
    return $content;
}
################################################################################