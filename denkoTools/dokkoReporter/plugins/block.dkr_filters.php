<?php
/**
 * Funcion dkr_filters
 * @param Array $params
 * @param String $content
 * @param Smarty $smarty
 * @param Boolean $repeat
 * @return String
 */
function smarty_block_dkr_filters($params, $content, &$smarty, &$repeat) {

    # Se fija si están seteados los nombres de las variables a utilizar, de
    # ser así las declara, sino deja los nombres por defecto.
    if (isset($params['declare'])) {
    	$declare = explode(',',$params['declare']);
        $name  = $declare[0];
        $key   = $declare[1];
        $value = $declare[2];
    } else {
    	$name  = 'name';
        $key   = 'key';
        $value = 'value';
    }

    # Obtengo el próximo filtro y seteo el repeat.
    $report = &DK_QueryReporter::getDaoLister($smarty);
    if (!$filter = $report->fetchFilter()) {
        $repeat = false;
    } else {
        $smarty->assign($name,$filter['value']['nombre']);
        $smarty->assign($key,$filter['key']);
        $smarty->assign($value,$report->getFilterValue($filter['key']));
        $repeat = true;
    }

    # Devuelvo el contenido.
    return $content;
}