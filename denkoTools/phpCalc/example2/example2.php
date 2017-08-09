<?php
require_once '../phpCalc.php';
require_once '../importCSV.php';

$hoja1=getJsonSheet('hojaD.csv');
//print_r($hoja1); echo '<br/>';
//print_r(file_get_contents('style.css'));
if (!empty($_POST)) {
    $hoja = new PHPCalc($_POST['sheet']);
    $hoja->setValues($_POST['celdas']);
    $hoja->calculateSheet ();
} else {
    $hoja = new PHPCalc($hoja1);
    $hoja->calculateSheet ();
   //$hoja->debug();
}
include 'example2.tpl.php';