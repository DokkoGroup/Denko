<?php
session_start();
function desarmarColor($col){
    $largo=strlen($col)/3;
    $res=array();
    $res['r']=substr($col,0*$largo,1*$largo).($largo==1?'0':'');
    $res['g']=substr($col,1*$largo,1*$largo).($largo==1?'0':'');
    $res['b']=substr($col,2*$largo,1*$largo).($largo==1?'0':'');

    $res['r']=hexdec($res['r']);
    $res['g']=hexdec($res['g']);
    $res['b']=hexdec($res['b']);

    return $res;
}

function brillo($col){
    return (int)(sqrt(
                    $col['r']*$col['r']*0.241 +
                    $col['g']*$col['g']*0.691 +
                    $col['b']*$col['b']*0.068 
		    ));
}

function byn($col){
    $c=desarmarColor($col);
    $c['g']=$c['b']=$c['r']=brillo($c);
    return armarColor($c);
}

function contraste($col,$umbral){
    $aux=$col-$umbral;
    $aux*=1.04;
    $aux+=$umbral;
    if($aux>255) return 255;
    if($aux<0) return 0;
    return $aux;
}

function cambiarContraste($arr,$umbral){
    $arr['r']=contraste($arr['r'],$umbral);
    $arr['g']=contraste($arr['g'],$umbral);
    $arr['b']=contraste($arr['b'],$umbral);
    return $arr;
}

function armarColor($arr){
    $arr['r']=($arr['r']<16?'0':'').dechex($arr['r']);
    $arr['g']=($arr['g']<16?'0':'').dechex($arr['g']);
    $arr['b']=($arr['b']<16?'0':'').dechex($arr['b']);

    return $arr['r'].$arr['g'].$arr['b'];
}

function colorize($col,$base){
    $c=desarmarColor($col);
    $b=desarmarColor($base);
    $brillo=brillo($c);
    $b['r']=($b['r']+$brillo)/2;
    $b['g']=($b['g']+$brillo)/2;
    $b['b']=($b['b']+$brillo)/2;
    $iteracion=20;
    if(brillo($b)<$brillo){
       while(brillo($b)<$brillo){
            $b=cambiarContraste($b,128-$iteracion++);
       }
    }else{
       while(brillo($b)>$brillo)
            $b=cambiarContraste($b,128+$iteracion++);
    }
    return armarColor($b);
}

function bynArray($arr){
   $res=array();
   foreach($arr as $aux){
       $res[]=byn($aux);
   }
   return $res;
}

function colorizeArray($arr,$col){
   $res=array();
   foreach($arr as $aux){
       $res[]=colorize($aux,$col);
   }
   return $res;
}

if(isset($_GET['file'])){
    header('content-type: text/css');
    echo $_SESSION[$_GET['file']];
    exit;
}

$fname="";

if(isset($_FILES['arch']) && ! empty($_FILES['arch']['tmp_name'])){
    $fname=$_FILES['arch']['name'];
    $_SESSION['archivo_'.$fname]=file_get_contents($_FILES['arch']['tmp_name']);
}else{
    if(isset($_POST['fname']) && isset($_SESSION['archivo_'.$_POST['fname']])){
        $fname=$_POST['fname'];
    }
}

if(!empty($_POST['col'])){
	$_POST['col']=str_replace("#","",$_POST['col']);
    $RGB=desarmarColor($_POST['col']);
}else{
    $RGB=desarmarColor("000000");
}