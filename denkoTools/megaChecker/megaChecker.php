<?php
echo '<html> <br />' . "\n";
echo "\n" . 'Realizando checkeo de configuraciones del servidor: <br /><br />';
echo "\n" . '--------------------------------------------------------<br />';

$span_close = '</strong></span>';
$span_ok = '<span style="color:green;"><strong>';
$span_fatal = '<span style="color:red;"><strong>';
$span_warn = '<span style="color:#ffd200;"><strong>';
$span_msg = '<span style="color:grey;"><strong>';
$br = '<br />';
$strong_open = '<strong>';
$strong_close = '</strong>';
$checkeo = "\n" . '* CHECKEO ';

$w_tempc = false; //Variable que controla la escritura en templates_c.
$w_cache = 0; //Variable que controla la escritura en la carpeta cache. 0=>inicializada, 
//true=>escritura permitida, false=>escritura denegada, NULL=>no existe la carpeta
$w_images = 0; //Variable que controla la escritura en la carpeta image.
$w_thumb = 0; //Variable que controla la escritura en la carpeta thumb.
$w_download = 0; //Variable que controla la escritura en la carpeta download.
$en_mod_rew; //Variable que controla el mod_rewrite de apache.
$en_mod_deflate; //Variable que controla el mod_deflate de apache.
$en_mod_expires; //Variable que controla el mod_expires de apache.
$en_gd2_ext; //Variable que controla la extension php de GD2.
$en_eaccel_ext; //Variable que controla la extension php de eAccelerator.
$desired_host; //Variable que controla el desired HOST.
$db_local; //Variable que controla si existe o no el DB.ini.local.


//Permiso de escritura en templates_c.
$w_tempc = is_dir("templates_c");
$tempc_existe= false;
if ($w_tempc == false){
    $thisdir = getcwd();
    $w_tempc = mkdir($thisdir . "/templates_c");
    if ($w_tempc){
        $w_tempc = rmdir($thisdir . "/templates_c");
    }
}else{
    $tempc_existe=true;
    $w_tempc = fopen("templates_c/write.allowed", "w+");
    fclose($w_tempc);
    if ($w_tempc != false){
        $w_tempc = unlink("templates_c/write.allowed");
    }
}
echo $checkeo . 'Permiso de escritura en templates_c: ';
if ($w_tempc == true){
    echo $span_ok . 'OK';
}elseif ($w_tempc == false){
    echo $span_fatal . 'DENEGADO';
}
if (!$tempc_existe){
    echo $span_close.' -> '.$span_msg . 'LA CARPETA NO EXISTE';
}
echo $span_close . $br;
//------------------------------------


//Permiso de escritura en carpeta cache.
$w_cache = is_dir("cache");
if ($w_cache == false){
    $w_cache = NULL;
}else{
    $w_cache_aux = fopen("cache/write.allowed", "w+");
    fclose($w_cache_aux);
    if ($w_cache_aux != false){
        $w_cache = unlink("cache/write.allowed");
    }
}
echo $checkeo . 'Permiso de escritura en cache: ';
if ($w_cache === true){
    echo $span_ok . 'OK';
}elseif ($w_cache === false){
    echo $span_warn . 'DENEGADO';
}elseif ($w_cache === NULL){
    echo $span_warn . 'DENEGADO' . $span_close . ' -> ';
    echo $span_msg . 'LA CARPETA NO EXISTE';
}
echo $span_close . $br;
//----------------------------------------


//Permiso de escritura en carpeta image.
$w_image = is_dir("image");
$image_existe = false;
if ($w_image == false){
    $thisdir = getcwd();
    $w_image = mkdir($thisdir . "/image");
    if ($w_image){
        $w_image = rmdir($thisdir . "/image");
    }
}else{
    $image_existe = true;
    $w_image_aux = fopen("image/write.allowed", "w+");
    fclose($w_image_aux);
    if ($w_image_aux != false){
        $w_image = unlink("image/write.allowed");
    }
}
echo $checkeo . 'Permiso de escritura en image: ';
if ($w_image){
    echo $span_ok . 'OK';
}else{
    echo $span_warn . 'DENEGADO';
}
if (!$image_existe){
    echo $span_close.' -> '.$span_msg . 'LA CARPETA NO EXISTE';
}
echo $span_close . $br;
//---------------------------------------


//Permiso de escritura en carpeta thumb.
$w_thumb = is_dir("thumb");
$thumb_existe = false;
if ($w_thumb == false){
    $thisdir = getcwd();
    $w_thumb = mkdir($thisdir . "/thumb");
    if ($w_thumb){
        $w_thumb = rmdir($thisdir . "/thumb");
    }
}else{
    $thumb_existe = true;
    $w_thumb_aux = fopen("thumb/write.allowed", "w+");
    fclose($w_thumb_aux);
    if ($w_thumb_aux != false){
        $w_thumb = unlink("thumb/write.allowed");
    }
}
echo $checkeo . 'Permiso de escritura en thumb: ';
if ($w_thumb){
    echo $span_ok . 'OK';
}else{
    echo $span_warn . 'DENEGADO';
}
if (!$thumb_existe){
    echo $span_close.' -> '.$span_msg . 'LA CARPETA NO EXISTE';
}
echo $span_close . $br;
//---------------------------------------


//Permiso de escritura en carpeta download.
$w_download = is_dir("download");
$down_existe = false;
if ($w_download == false){
    $thisdir = getcwd();
    $w_download = mkdir($thisdir . "/download");
    if ($w_download){
        $w_download = rmdir($thisdir . "/download");
    }
}else{
    $down_existe = true;
    $w_download_aux = fopen("download/write.allowed", "w+");
    fclose($w_download_aux);
    if ($w_download_aux != false){
        $w_download = unlink("download/write.allowed");
    }
}
echo $checkeo . 'Permiso de escritura en download: ';
if ($w_download){
    echo $span_ok . 'OK';
}else{
    echo $span_warn . 'DENEGADO';
}
if (!$down_existe){
    echo $span_close.' -> '.$span_msg . 'LA CARPETA NO EXISTE';
}
echo $span_close . $br;
//------------------------------------------
$en_mods = apache_get_modules();

//Verifico que el apache tenga mod_rewrite.
$aux = 0;
$found = false;
while ( (! $found) && ($aux < count($en_mods)) ){
    if ($en_mods [$aux] == 'mod_rewrite'){
        $found = true;
    }else{
        $aux ++;
    }
}
echo $checkeo . ' El estado de mod_rewrite: ';
if ($found){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_fatal . 'DESACTIVADO';
}
echo $span_close . $br;
//-----------------------------------------


//Verifico que el apache tenga mod_deflate.
$aux = 0;
$found = false;
while ( (! $found) && ($aux < count($en_mods)) ){
    if ($en_mods [$aux] == 'mod_deflate'){
        $found = true;
    }else{
        $aux ++;
    }
}
echo $checkeo . ' El estado de mod_deflate: ';
if ($found){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_warn . 'DESACTIVADO';
}
echo $span_close . $br;
//-----------------------------------------


//Verifico que el apache tenga mod_expires.
$aux = 0;
$found = false;
while ( (! $found) && ($aux < count($en_mods)) ){
    if ($en_mods [$aux] == 'mod_expires'){
        $found = true;
    }else{
        $aux ++;
    }
}
echo $checkeo . ' El estado de mod_expires: ';
if ($found){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_warn . 'DESACTIVADO';
}
echo $span_close . $br;
//-----------------------------------------


//Verifico que la extensión GD2 esté activada.
$en_gd2_ext = extension_loaded('gd');
echo $checkeo . ' El estado de la extensión GD2: ';
if ($en_gd2_ext){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_warn . 'DESACTIVADO';
}
echo $span_close . $br;
//--------------------------------------------


//Verifico que la extension eAccelerator esté activada.
$en_eaccel_ext = extension_loaded('eAccelerator');
echo $checkeo . ' El estado de la extensión eAccelerator: ';
if ($en_eaccel_ext){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_warn . 'DESACTIVADO';
}
echo $span_close . $br;
//-----------------------------------------------------


//Verifico que estén instaladas las PEAR


$incl_pth = get_include_path();
if ((substr($incl_pth, 0, 2)) == '.;'){
    $n_pth = substr($incl_pth, 2);
}else{
    $n_pth = $incl_pth;
}
echo $checkeo . ' El estado de las PEAR: ';
if (file_exists($n_pth . '\DB\DataObject.php')){
    echo $span_ok . 'ACTIVADO';
}else{
    echo $span_fatal . 'DESACTIVADO';
}
echo $span_close . $br;
//--------------------------------------


//Verificar el desire HOST
if (!file_exists('../HOST.ini')){
    echo $span_fatal."\n".'* El archivo HOST.ini no existe.'.$span_close.$br;
}else{
    $host_ini = parse_ini_file('../HOST.ini');
    $geth = (! empty($_SERVER ['HTTP_X_FORWARDED_HOST']) ? $_SERVER ['HTTP_X_FORWARDED_HOST'] : $_SERVER ['HTTP_HOST']);
    if (('www.' . $geth) != $host_ini ['desired_host']){
        $desired_host = false;
    }else{
        $desired_host = true;
    }
    echo $br . "\n".'* DESIRED_HOST: ' . $span_msg . $host_ini ['desired_host'] . $span_close . $br;
    if ($desired_host){
        echo $span_ok . 'OK';
    }else{
        echo $checkeo . 'El desired_host: ' . $span_fatal . 'ERROR FATAL' . $span_close . ' -> ';
        echo $span_msg . 'LOS HOSTS NO CONCUERDAN';
    }
    echo $span_close . $br . $br;
}
//------------------------


//Verificar que exista el DB.ini.local
$db_local = file_exists('../DB.ini.local');
echo $checkeo . ' La existencia del DB.ini.local: ';
if ($db_local){
    echo $span_ok . 'EXISTE';
}else{
    echo $span_warn . 'NO EXISTE';
}
echo $span_close . $br;
//------------------------------------


//Verificar que se pueda conectar a la base de datos
$seguir = false;
if ($db_local){
    $iniPath = '../DB.ini.local';
    $seguir = true;
}else{
    $iniPath = '../DB.ini';
    if (!file_exists($iniPath)){
        echo $span_fatal."\n".'* TAMPOCO EXISTE EL ARCHIVO DB.ini'.$span_close.$br;
    }else{
        $seguir = true;
    }
}
if ($seguir){
    $config = parse_ini_file($iniPath, TRUE);
    require_once 'DB.php';
    foreach ( $config as $class => $values ){
        $options = &PEAR::getStaticProperty($class, 'options');
        $options = $values;
    }
    $dsn = $options ['database'];
    $db = & DB::connect($dsn);
    echo $checkeo . ' La conexión a la base de datos: ';
    if (! (PEAR::isError($db))){
        echo $span_ok . 'CONEXIÓN EXITOSA';
    }else{
        echo $span_fatal . 'NO SE PUDO CONECTAR LA BASE';
    }
    echo $span_close . $br;
}
//--------------------------------------------------

//Verifico que si está habilitada la función sem_get()
echo "\n".$checkeo.' Si está habilitada la función SEM_GET(): ';
if (function_exists('sem_get')) {
    echo "\n".$span_ok.'HABILITADA';
} else {
    echo "\n".$span_warn.'DESHABILITADA';
}
echo $span_close.$br;
//----------------------------------------------------

//Mostrar la versión de php
echo '* Versión actual de PHP: ' . $span_msg . phpversion() . $span_close;

//-------------------------


//Mostrar la versión de apache
function apacheversion() {
    $ver = preg_split('/[/ ]/', $_SERVER ['SERVER_SOFTWARE']);
    $apver = "$ver[1] $ver[2]";
    return $apver;
}
echo $br . "\n" . '* Versión actual de APACHE: ' . $span_msg . apacheversion() . $span_close;
//----------------------------


//Mostrar la versión del SO
echo $br . "\n" . '* Sistema Operativo:' . $span_msg . php_uname() . $span_close;
echo $br . "\n" . '* Sistema Operativo:' . $span_msg . PHP_OS . $span_close;
//-------------------------


//Mostrar la hora del servidor
echo $br . "\n" . '* Hora y fecha del servidor: ' . $span_msg . date('Y-m-d') . ' ';
echo date('H:i:s') . $span_close;
//----------------------------


echo "\n" . '</html>';
