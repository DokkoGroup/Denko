<?php
global $debug,$bgcolor,$prefijo,$title;
$seccion=empty($_GET['section'])?'home':$_GET['section'];
$debug=false;
$bgcolor="#ffffff";
$prefijo='';
$title='';

function show($img,$divs){
	global $debug,$prefijo;
    $border=($debug)?'solid red 1px':'none';
    
    
    echo "\n".'<div style="position:relative;margin: 0px auto;width:1100px;"><img src="'.$prefijo.$img.'.jpg" border="0" />';
	foreach($divs as $div){
		echo "\n".'<a href="index.php?section='.$div['section'].'"><div style="border:'.$border.';position:absolute;background-color:#fff;filter:alpha(opacity=0);opacity:0;width:'.$div['width'].'px;height:'.$div['height'].'px;top:'.$div['top'].'px;left:'.$div['left'].'px;" onclick="javascript:window.location=\'index.php?section='.$div['section'].'\';" onmouseover="javascript:this.style.cursor=\'pointer\';" ></div></a>';
	}
	echo "\n".'</div>';
}

function createDiv($values,$section){
	return array(
		'width'=>$values[2],
		'height'=>$values[3],
		'top'=>$values[1],
		'left'=>$values[0],
		'section'=>$section
	);
}

function getDivs($data,$seccion){
    foreach($data as $secciones => $valores){
        $nombres=explode(',',$secciones);
        foreach($nombres as $nombre){
            if($nombre==$seccion){
                $res=array();
                foreach($valores as $k=>$v){
                    $res[]=createDiv(explode(',',$v),$k);
                }
                return $res;
            }
        }
    }
    return array();
}

$data=parse_ini_file('botones.ini',true);

if(isset($data['debug'])){
    if($data['debug']==1){
        $debug=true;
    }
    unset($data['debug']);
}
if(isset($data['prefijo'])){
    $prefijo=$data['prefijo'];
    unset($data['prefijo']);
}
if(isset($data['bgcolor'])){
    $bgcolor=$data['bgcolor'];
    unset($data['bgcolor']);
}
if(isset($data['title'])){
    $title=$data['title'];
    unset($data['title']);
}

$divs=getDivs($data,$seccion);

echo ' <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.$title.'</title>
</head>
<body style="margin:0px;background-color:'.$bgcolor.';">';
show($seccion,$divs);
echo "\n".'</body>';
