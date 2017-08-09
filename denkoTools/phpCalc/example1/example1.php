<?php
require_once '../phpCalc.php';
require_once '../importCSV.php';

$hoja1=getJsonSheet('hojaC.csv');
if (!empty($_POST)) {
    $hoja = new PHPCalc($_POST['sheet']);
    $hoja->setValues($_POST['celdas']);
    $hoja->calculateSheet ();
} else {
    $hoja = new PHPCalc($hoja1);
    $hoja->calculateSheet ();
}
include 'example1.tpl.php';