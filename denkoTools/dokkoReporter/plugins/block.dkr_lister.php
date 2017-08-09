<?php
/**
 * Funcion dkr_lister
 * @param Array $params
 * @param String $content
 * @param Smarty $smarty
 * @param Boolean $repeat
 * @return String
 */
function smarty_block_dkr_lister($params, $content, &$smarty, &$repeat) {

    $report = &DK_QueryReporter::getDaoLister($smarty);
    if (!$report->fetch()) {
        $repeat = false;
    } else {
    	$arreglo = array();
        foreach ($report->getProperties() as $column) {
            $arreglo[] = $report->$column;
        }
        $smarty->assign('result',$arreglo);
        $repeat = true;
    }

    return $content;
}