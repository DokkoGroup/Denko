<?php

require_once '../denko/dk.queryreporter.php';
/**
 * Funcion dk_reporter
 * @param Array $params
 * @param String $content
 * @param Smarty $smarty
 * @param Boolean $repeat
 * @return String
 */
function smarty_block_dk_reporter($params, $content, &$smarty, &$repeat) {

    # Valido que el parámetro 'id_report'.
    if (isset($params['id_report']) && !empty($params['id_report'])) {
    	$id_report = $params['id_report'];
    } elseif (isset($_POST['id_report'])) {
        $id_report = $_POST['id_report'];
    } elseif (isset($_GET['id_report'])) {
        $id_report = $_GET['id_report'];
    } else {
        Denko::plugin_fatal_error('el \'id_report\' es necesario','dk_reporter');
    }
    $smarty->assign('dkr_idReport',$id_report);


	# Seteo parámetros necesarios para el funcionamiento del dkr.
    $resultsPerPage = (isset($params['resultsPerPage'])) ? $params['resultsPerPage'] : null;
	$pageNumber     = (isset($_GET['dkr_page']))         ? $_GET['dkr_page']         : 1;


    # Establezco los valores de los filtros.
    $filters = array();
    foreach ($_POST as $key => $value) {
    	if (substr($key,0,11) == 'dkr_filter_') {
            $filters[] = $value;
    	}
    }


    # Inicializo el query reporter y realizo el find del query indicado.
    $report  = new DK_QueryReporter($id_report,$resultsPerPage);
    $results = $report->find($pageNumber,$filters);
    if ($results !== null) {

        # Asigno los id's y nombres de los querys disponibles para armar el select, la 
        # cantidad de resultados encontrados en el find y la cantidad de filtros.
        $smarty->assign('dkr_results',$results);
        $smarty->assign('dkr_filter_results',$report->getCantFilters());


        # Valido el número de página.
        $totalPages = $report->getTotalPages();
        if (!Denko::isInt($pageNumber) || $pageNumber <= 0 || $pageNumber > $totalPages) {
            Denko::plugin_fatal_error('el número de página es erroneo','dk_reporter');
        } else {
            $smarty->assign('dkr_pageNumber',$pageNumber);
            $smarty->assign('dkr_totalPages',$totalPages);
        }


        # Obtengo los nombres de las columnas y se los asigno a una variable de smarty. 
        $arreglo = array();
        foreach ($report->getProperties() as $column) {
            $arreglo[] = $column;
        }
        $smarty->assign('column',$arreglo);
    } else {
    	Denko::plugin_fatal_error('no se encontró el reporte especificado','dk_reporter');
    }


    # Devuelvo el contenido.
    return $content;
}