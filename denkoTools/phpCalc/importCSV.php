<?php
//setlocale(LC_ALL,'es_AR');
require_once 'dk.denko.php';

$default_metadata = array("valor" => "", "formula" => "", "type" => "0", "class" => "", "validate" => "");

function indice ($col) {
    return chr ($col + 65);
}

function setStyles (&$metadata) {
    $pattern = '/#([A-Z]+)([0-9]+)/';
    foreach ($metadata as $fil => $columnas) {
        foreach ($columnas as $col => $celda) {
            if (preg_match ($pattern, $metadata[$fil][$col]['style'], $matches)) {
                $metadata[$fil][$col]['style'] = !empty($metadata[$matches[2]][$matches[1]]['style']) ? $metadata[$matches[2]][$matches[1]]['style'] : '';
            }
        }

    }
}

function parseMetadata ($metadata) {
    $result = array();
    global $default_metadata;
    foreach ($metadata as $fil => $columnas) {
        foreach ($columnas as $col => $celda) {
            if ($celda != '') {
                $cell_value = json_decode (utf8_encode ($celda), true);
                $data = $default_metadata;
                if (function_exists ('json_last_error') && json_last_error () == 0) {
                    $data = array_merge ($data, $cell_value);
                } elseif (is_array ($cell_value)) {
                    $data = array_merge ($data, $cell_value);
                } else {
                    $data['valor'] = utf8_encode ($celda);
                }
                $result[$fil + 1][indice ($col)] = $data;
            }
        }
    }
    Denko::arrayUtf8Decode ($result);
    setStyles ($result);
    return $result;
}

function parseCSV ($filename, $delimiter = ';') {
    if (!$handle = fopen ($filename, 'r')) {
        echo 'Error: No se pudo abrir el archivo ' . $filename;
        return null;
    }
    $datos = array();
    while (($data = fgetcsv ($handle, 10000, $delimiter)) !== false) {
        $datos[] = $data;
    }
    fclose ($handle);
    return $datos;
}

function getJsonSheet ($csvFileName) {
    $datos = parseCSV ($csvFileName);
    $datos = parseMetadata ($datos);
    Denko::arrayUtf8Encode ($datos);
    return json_encode ($datos);
}
