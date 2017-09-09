#!/usr/bin/php -d phar.readonly=\"off\" 
<?php


function getFiles($folder,&$res){
	$files = glob($folder.'/{,.}[!.,!..]*', GLOB_BRACE);
	foreach($files as $file) {
		if(is_dir($file)){
			getFiles($file, $res);
		}else{
			$res[str_replace('/opt/pear/', '', $file)] = $file;
		}
	}	
}

$files = [];
getFiles('/opt/pear', $files);

$phar = new Phar('denko-pear.phar', 0, 'denko-pear.phar');
$phar->buildFromIterator(new ArrayIterator($files));

$p1 = $phar->compress(Phar::GZ);
