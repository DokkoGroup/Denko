<?php
/* Mega Updater
 * Version 0.1.2
 * Propiedad de DokkoGrpup
 *
 * By. FBricker
 */

require_once 'updateFunctions.php';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Constantes

define("MU_VERSION","0.1.3");
define("MU_NAME","Dokko megaUpdater");

define("R_INFO",1);
define("R_ERROR",2);
define("R_OK",3);
define("R_ACTION",4);
define("R_ECHO",5);
define("R_IMPORTANT",6);

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Funciones globales

global $mysqlError,$mysqlErrno;
$mysqlError='';
$mysqlErrno=0;

function formatOutput($text,$html,$color){
    if(!$html){
        return $text;
    }
    return '<font color="'.$color.'"><b>'.$text.'</b></font>';
}

function reportSplit(){
    if(isset($_SERVER['HTTP_HOST'])){
        report("<hr>");
    }else{
        report("------------------------------------------------------------");
    }
}

function report($text,$tipo=R_ECHO){
    if(isset($_SERVER['HTTP_HOST'])){
        $eol=" <br>\n";
        $html=true;
    }else{
        $eol="\n";
        $html=false;
    }
    switch ($tipo) {
        case R_INFO:
            $sol=formatOutput('Info: ',$html,'black');
            break;
        case R_ERROR:
            $sol=formatOutput('Error: ',$html,'red');
            break;
        case R_OK:
            $sol=formatOutput('Ok: ',$html,'green');
            break;
        case R_ACTION:
            $sol=formatOutput(' -> ',$html,'black');
            break;
        case R_IMPORTANT:
            $sol='';
            $text=formatOutput($text,$html,'black').' *';
            break;
    	default:
            $sol='';
    		break;
    }
    echo $sol.$text.$eol;
}

function getConnectionInfo(){
    if(is_file('DB.ini')){
        $file='DB.ini';
    }elseif(is_file('../DB.ini.local')){
        $file='../DB.ini.local';
    }elseif(is_file('../DB.ini')){
        $file='../DB.ini';
    }else{
        report('No se encuentra el archivo DB.ini',R_ERROR);
        exit(6);
    }
    $arr=parse_ini_file($file);
    if(!isset($arr['database'])){
        report('Error en el archivo DB.ini',R_ERROR);
        exit(7);
    }
    $path=str_replace("mysqli://","",$arr['database']);
    $path=str_replace("mysql://","",$path);
    list($usuario,$db)=explode("@",$path);
    $arrayTemp=explode(":",$usuario);
    $res['user']=$arrayTemp[0];
    $res['pass']=isset($arrayTemp[1])?$arrayTemp[1]:'';
    list($res['host'],$res['db'])=explode("/",$db);
    return $res;
}

function getDbLink(){
    $cInfo = getConnectionInfo();
    $link = mysqli_connect($cInfo['host'],$cInfo['user'],$cInfo['pass']);
    if(!$link){
        report('No me pude conectar a la base de datos',R_ERROR);
        exit(2);
    }
    mysqli_select_db($link,$cInfo['db']);
    return $link;
}

function reportMysqlErrors(){
    global $mysqlError,$mysqlErrno;
    report("MysqlError #".$mysqlErrno." - ".$mysqlError,R_ERROR);
}

function execQuery($query,$quiet=false,$ignoreErrors=array()){
    global $mysqlError,$mysqlErrno;
    $link = getDbLink();
    $result = mysqli_query($link,$query);
    $mysqlError=mysqli_error($link);
    $mysqlErrno=mysqli_errno($link);
    mysqli_close($link);
    if(!$result){
        if(!in_array($mysqlErrno,$ignoreErrors)){
            if(!$quiet){
                reportMysqlErrors();
            }
            return false;
        }else{
            return true;
        }
    }
    return $result;
}

function getDbVersion(){
    $result=execQuery('select version from databaseversion;');
    if($result){
        $row = mysqli_fetch_row($result);
        if(!empty($row[0])) return $row[0];
    }
    // SI ESTA LA TABLA CON MINUSCULA, RECUPERO EL VALOR Y LA ELIMINO
    $result=execQuery('select version from databaseVersion;');
    if($result){
        $row = mysqli_fetch_row($result);
        report("Voy a renombrar la tabla databaseVersion a databaseversion (tiene que estar en minusculas!)",R_WARN);
        @execQuery('drop table databaseversion;');
        if(!execQuery('rename table databaseVersion to databaseversion;')){
            report("No puedo renombrar la tabla databaseVersion a databaseversion!",R_ERROR);
            report("Asegurese que el usuario en DB.ini tiene privilegios suficientes o renombra las tablas usted mismo.",R_ERROR);
            report("Para hacerlo a mano ejecute: RENAME TABLE databaseVersion to databaseversion;",R_ERROR);
            exit(3);
        }
        return $row[0];
    }
    if(!execQuery('create table databaseversion (version integer not null);')){
        report("No se puede crear la tabla databaseversion",R_ERROR);
        report("Asegurese que el usuario en DB.ini tiene privilegios suficientes o agrege la tabla usted mismo ejecutando el sig. query:",R_INFO);
        report("create table databaseversion (version integer not null);",R_INFO);
        exit(3);
    }
    if(!execQuery('insert into databaseversion values (0);')){
        report("No se puede crear la version 0",R_ERROR);
        exit(4);
    }
    return 0;
}

function setDbVersion($version){
    if(!execQuery('update databaseversion set version='.$version.';')){
        report("No se puede actualizar la version en la tabla databaseversion",R_ERROR);
        exit(3);
    }
}

function createTable($query,$tableName){
    global $mysqlErrno;
    report('Instalando "'.$tableName.'"',R_ACTION);
    if(!execQuery($query,true,array(1050))){
        reportMysqlErrors();
        report('No se pudo instalar "'.$tableName.'", verifique que el usuario en DB.ini tiene permisos para crear una tabla.',R_ERROR);
        return false;
    }
    if($mysqlErrno==1050){
        report('"'.$tableName.'" ya estaba instalado.',R_INFO);
    }
    return true;
}

function dropTable($tableName){
    global $mysqlErrno;
    report('Desinstalando "'.$tableName.'"',R_ACTION);
    if(!execQuery("DROP TABLE `".$tableName."`;",true,array(1051))){
        reportMysqlErrors();
        report('No se pudo desinstalar "'.$tableName.'", verifique que el usuario en DB.ini tiene permisos para eliminar una tabla.',R_ERROR);
        return false;
    }
    if($mysqlErrno==1051){
        report('"'.$tableName.'" no estaba instalado.',R_INFO);
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// AudDataObject

function installAudDataObject($table){
    global $mysqlErrno;
    $query="ALTER TABLE `".$table."` ADD COLUMN `aud_ins_date` DATETIME NOT NULL,
            ADD COLUMN `aud_upd_date` DATETIME NOT NULL, ROW_FORMAT = DYNAMIC;";
    report('Agregando soporte AudDataObject a la tabla "'.$table.'"',R_ACTION);
    if(!execQuery($query,true,array(1060))){
        reportMysqlErrors();
        report('No se pudo agregar el soporte, verifique que el usuario en DB.ini tiene permisos y que la tabla "'.$table.'" existe.',R_ERROR);
        return false;
    }
    if($mysqlErrno==1060){
        report('El soporte ya estaba instalado.',R_INFO);
    }
    return true;
}

function uninstallAudDataObject($table){
    global $mysqlErrno;
    $query="ALTER TABLE `".$table."` DROP COLUMN `aud_ins_date`,
            DROP COLUMN `aud_upd_date`;";
    report('Eliminando soporte AudDataObject a la tabla "'.$table.'"',R_ACTION);
    if(!execQuery($query,true,array(1091))){
        reportMysqlErrors();
        report('No se pudo eliminar el soporte, verifique que el usuario en DB.ini tiene permisos y que la tabla "'.$table.'" existe.',R_ERROR);
        return false;
    }
    if($mysqlErrno==1091){
        report('El soporte no estaba instalado.',R_INFO);
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Modulo de configuraciones

function installConfigurationModule(){
    $query="CREATE TABLE  `configuracion` (
              `id_configuracion` int(11) NOT NULL auto_increment,
              `nombre` varchar(50) NOT NULL,
              `valor` blob,
              `estado` int(11) NOT NULL,
              `tipo` int(11) NOT NULL default '0',
              `metadata` blob,
              `descripcion` varchar(150) NOT NULL,
              PRIMARY KEY  (`id_configuracion`),
              UNIQUE KEY `nombre` (`nombre`)
            ) ENGINE=InnoDB;";

    if(!createTable($query,"configuracion")) return false;
    installAudDataObject('configuracion');
    return upgradeConfigurationModule();
}

function uninstallConfigurationModule(){
    return dropTable("configuracion") && dropTable("tipoconfiguracion") ;
}

function upgradeConfigurationModule(){
    $query="alter table `configuracion` engine='InnoDB'";
    report('Modificando tipo de tabla a InnoDB',R_ACTION);
    if(!execQuery($query,true)){
        report('No se pudo modificar el tipo de tabla',R_INFO);
    }
    $query="CREATE TABLE  `tipoconfiguracion` (
              `id_tipoconfiguracion` int(10) unsigned NOT NULL auto_increment,
              `nombre` varchar(255) NOT NULL,
              `descripcion` varchar(255) NOT NULL,
              PRIMARY KEY  (`id_tipoconfiguracion`)
            ) ENGINE=InnoDB;";

    if(!createTable($query,"tipoconfiguracion")) return false;
    installAudDataObject('tipoconfiguracion');

    $query="ALTER TABLE `configuracion`
             ADD COLUMN `id_tipoconfiguracion` INTEGER UNSIGNED AFTER `descripcion`,
             ADD COLUMN `indice1` INTEGER UNSIGNED AFTER `id_tipoconfiguracion`,
             ADD COLUMN `indice2` INTEGER UNSIGNED AFTER `indice1`,
             ADD INDEX `Index_3`(`id_tipoconfiguracion`),
             ADD INDEX `Index_4`(`indice1`),
             ADD INDEX `Index_5`(`indice2`),
             ADD CONSTRAINT `FK_configuracion_1` FOREIGN KEY `FK_configuracion_1` (`id_tipoconfiguracion`)
                REFERENCES `tipoconfiguracion` (`id_tipoconfiguracion`)
                ON DELETE RESTRICT
                ON UPDATE RESTRICT;";

    report('Agregando id_tipoconfiguracion a la tabla configuracion',R_ACTION);
    if(!execQuery($query,true,array(1060))){
        report('No se pudo agregar id_tipoconfiguracion a la tabla configuracion',R_ERROR);
        return false;
    }
    report('Listo',R_ACTION);
    upgradeConfigurationModuleV2();
    upgradeConfigurationModuleV3();    
    return true;
}

function upgradeConfigurationModuleV2(){
    report('Modificando indice nombre (unique) - !!Recorda actualizar la funcion getConfig del common.php!!',R_ACTION);
	return execQuery('ALTER TABLE `configuracion` DROP INDEX `nombre`,
				  ADD UNIQUE `nombre`(`nombre`, `indice1`);');
}

function upgradeConfigurationModuleV3(){
    report('Agregando el cambio filtro al modulo de configuraciones  - !!Recorda actualizar la funcion getConfig y los objetos DAO!!',R_ACTION);
    return execQuery('ALTER TABLE `configuracion` ADD COLUMN `filtro` BLOB AFTER `indice2`;',true,array(1060));
}

function createTipoconfiguracion($id_tipoconfiguracion,$name,$description){
    global $mysqlErrno;
    $query="select id_tipoconfiguracion from tipoconfiguracion where id_tipoconfiguracion=$id_tipoconfiguracion;";
    $res=execQuery($query);
    $res=mysqli_fetch_row($res);

    if(empty($res)){
        $query="insert into tipoconfiguracion (id_tipoconfiguracion,nombre,descripcion,aud_ins_date,aud_upd_date)
                values($id_tipoconfiguracion,'".mysqli_real_escape_string(getDbLink(),$name)."','".mysqli_real_escape_string(getDbLink(),$description)."',now(),now());";
        report('Agregando tipoconfiguracion '.$name,R_ACTION);
    }else{
        $query="update tipoconfiguracion set aud_upd_date=now(), descripcion='".mysqli_real_escape_string(getDbLink(),$description)."', nombre='".mysqli_real_escape_string(getDbLink(),$name)."'
                where id_tipoconfiguracion = $id_tipoconfiguracion;";
        report('Actualizando tipoconfiguracion '.$name,R_ACTION);
    }

    if(!execQuery($query)){
        report('No se pudo agregar/actualizar el tipoconfiguracion '.$name,R_ERROR);
        return false;
    }
    return true;
}

function deleteTipoconfiguracion($id_tipoconfiguracion){
    global $mysqlErrno;
    $query="delete from tipoconfiguracion where id_tipoconfiguracion=$id_tipoconfiguracion;";
    report('Eliminando tipoconfiguracion '.$id_tipoconfiguracion,R_ACTION);
    if(!execQuery($query)){
        report('No se pudo eliminar el tipoconfiguracion '.$id_tipoconfiguracion,R_ERROR);
        return false;
    }
    return true;
}


function createConfiguration($name,$type,$defaultValue,$restrictions,$description,$rewriteValue=false,$id_tipoconfiguracion="null"){
    global $mysqlErrno;
    $query="select estado from configuracion where nombre='".mysqli_real_escape_string(getDbLink(),$name)."';";
    $res=execQuery($query);
    $res=mysqli_fetch_row($res);

    if($defaultValue==='---null---'){
        $estado=0;
        $defaultValue='';
    }else{
        $estado=1;
    }

    if(empty($res)){
        $query="insert into configuracion (nombre,valor,tipo,metadata,estado,aud_ins_date,aud_upd_date,descripcion,id_tipoconfiguracion)
                values('".mysqli_real_escape_string(getDbLink(),$name)."','".mysqli_real_escape_string(getDbLink(),$defaultValue)."',".$type.", '".mysqli_real_escape_string(getDbLink(),$restrictions)."',".$estado.",now(),now(),
                '".mysqli_real_escape_string(getDbLink(),$description)."',$id_tipoconfiguracion);";
        report('Agregando configuracion '.$name,R_ACTION);
    }else{
        $query="update configuracion set tipo=".$type.", metadata='".mysqli_real_escape_string(getDbLink(),$restrictions)."', aud_upd_date=now(), descripcion='".mysqli_real_escape_string(getDbLink(),$description)."', id_tipoconfiguracion = $id_tipoconfiguracion";
        if($res[0]==0 || $rewriteValue){
            $query.=", valor='".mysqli_real_escape_string(getDbLink(),$defaultValue)."', estado=".$estado;
        }
        $query.=" where nombre='".mysqli_real_escape_string(getDbLink(),$name)."';";
        report('Actualizando configuracion '.$name,R_ACTION);
    }

    if(!execQuery($query)){
        report('No se pudo agregar/actualizar la configuracion '.$name,R_ERROR);
        return false;
    }
    return true;
}

function setConfigurationFilter($name,$filter){
    global $mysqlErrno;
    report('Seteando filtros a la configuracion '.$name,R_ACTION);
    $query="update configuracion set filtro='".mysqli_real_escape_string(getDbLink(),$filter)."' where nombre='".mysqli_real_escape_string(getDbLink(),$name)."';";
    return execQuery($query);
}

function deleteConfiguration($name){
    global $mysqlErrno;
    $query="delete from configuracion where nombre='".mysqli_real_escape_string(getDbLink(),$name)."';";
    report('Eliminando configuracion '.$name,R_ACTION);
    if(!execQuery($query)){
        report('No se pudo eliminar la configuracion '.$name,R_ERROR);
        return false;
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Emails

function installEmailModule(){
    $query="CREATE TABLE  `email` (
              `id_email` int(10) unsigned NOT NULL auto_increment,
              `fromemail` varchar(100) NOT NULL,
              `fromname` varchar(100) NOT NULL,
              `destination` varchar(100) NOT NULL,
              `emailsubject` varchar(250) NOT NULL,
              `message` blob NOT NULL,
              `sent` char(1) NOT NULL,
              `sendtries` int(10) unsigned NOT NULL,
              PRIMARY KEY  (`id_email`)
            ) ENGINE=InnoDB;";
    if(!createTable($query,"email")) return false;
    installAudDataObject('email');
    return true;
}

function uninstallEmailModule(){
    return dropTable("email");
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Temporal

function installTemporalModule(){
    $query="CREATE TABLE  `temporal` (
              `id_temporal` int(10) unsigned NOT NULL auto_increment,
              `id_temporal_parent` int(10) unsigned default NULL,
              `data` longblob NOT NULL,
              `flag` int(10) unsigned NOT NULL default '0',
              PRIMARY KEY  (`id_temporal`),
              KEY `Index_2_parent` (`id_temporal_parent`),
              KEY `Index_3_flag` (`flag`)
            ) ENGINE=InnoDB;";
    if(!createTable($query,"temporal")) return false;
    installAudDataObject('temporal');
    return true;
}

function installDFMModule(){
    report('Instalando modulo DFM',R_ACTION);
    installTemporalModule();
    return execQuery("ALTER TABLE `temporal`
                        ADD COLUMN `index1` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                        ADD COLUMN `index2` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                        ADD COLUMN `index3` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                        ADD COLUMN `metadata` text,
                        ADD COLUMN `size` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                        ADD COLUMN `name` tinytext
                     ;",false,array(1060,1050));
}

function uninstallTemporalModule(){
    return dropTable("temporal");
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Looger

function installLogModule(){
    $query="CREATE TABLE `tipolog` (
              `id_tipolog` int(10) unsigned NOT NULL auto_increment,
              `id_padre` int(10) unsigned default NULL,
              `nombre` varchar(255) default NULL,
              `descripcion` varchar(255) default NULL,
              PRIMARY KEY  (`id_tipolog`),
              KEY `FK_tipoLog_1` (`id_padre`),
              CONSTRAINT `FK_tipoLog_1` FOREIGN KEY (`id_padre`) REFERENCES `tipolog` (`id_tipolog`)
            ) ENGINE=InnoDB;";
    if(!createTable($query,"tipolog")) return false;

    $query="CREATE TABLE `log` (
              `id_log` int(10) unsigned NOT NULL auto_increment,
              `id_tipolog` int(10) unsigned default NULL,
              `descripcion` varchar(255) default NULL,
              `datos` blob,
              `fecha` datetime default NULL,
              `indice1` int(10) unsigned default NULL,
              `indice2` int(10) unsigned default NULL,
              `indice3` int(10) unsigned default NULL,
              PRIMARY KEY  (`id_log`),
              KEY `FK_log_1` (`id_tipolog`),
              KEY `Index_log_1` (`indice1`),
              KEY `Index_log_2` (`indice2`),
              KEY `Index_log_3` (`indice3`),
              KEY `Index_log_fecha` (`fecha`),
              CONSTRAINT `FK_log_1` FOREIGN KEY (`id_tipolog`) REFERENCES `tipolog` (`id_tipolog`)
            ) ENGINE=InnoDB;";
    if(!createTable($query,"log")) return false;

    installAudDataObject('tipolog');
    installAudDataObject('log');
    return true;
}

function uninstallLogModule(){
    $l=dropTable("log");
    $tl=dropTable("tipolog");
    return ($l && $tl);
}

function createTipoLog($name,$description,$id_tipolog,$id_padre="null"){

    global $mysqlErrno;
    $query="select id_tipolog from tipolog where id_tipolog=".$id_tipolog.";";
    $res=execQuery($query);
    $res=mysqli_fetch_row($res);

    if(empty($res)){
        $query="insert into tipolog (id_tipolog,id_padre,nombre,descripcion,aud_ins_date)
                values(".$id_tipolog.",".$id_padre.",'".mysqli_real_escape_string(getDbLink(),$name)."','".mysqli_real_escape_string(getDbLink(),$description)."',now());";
        report('Agregando tipolog '.$name,R_ACTION);
    }else{
        $query="update tipolog set nombre='".mysqli_real_escape_string(getDbLink(),$name)."', aud_upd_date=now(),
                descripcion='".mysqli_real_escape_string(getDbLink(),$description)."', id_padre=".$id_padre."
                where id_tipolog=".$id_tipolog.";";
        report('Actualizando tipolog '.$name,R_ACTION);
    }

    if(!execQuery($query)){
        report('No se pudo agregar/actualizar el tipo de log "'.$name.'"',R_ERROR);
        return false;
    }
    return true;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// DokkoReporter

function installReportModule(){
    report('Instalando modulo de Reportes Dokko',R_ACTION);
    installConfigurationModule();
    $query="CREATE TABLE `report` (
              `id_report` int(10) unsigned NOT NULL auto_increment,
              `query_report` TEXT,
               PRIMARY KEY  (`id_report`)
            ) ENGINE=InnoDB;";
    if(!createTable($query,"report")) return false;
    installAudDataObject('report');
    return true;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// Ejecucion de actualizacion

report('Iniciando '.MU_NAME.' v'.MU_VERSION,R_IMPORTANT);
reportSplit();
$version=getDbVersion();
report(" - Iniciando actualizaciones desde version ".$version,R_IMPORTANT);

if($version=='') $version=0;
$version++;
$metodo='updateV'.$version;
if(!function_exists($metodo)){
    report("No hay nuevas versiones",R_INFO);
    exit(0);
}

while(function_exists($metodo)){
    reportSplit();
    report("Ejecutando actualizacion a version ".$version,R_IMPORTANT);
    execQuery('START TRANSACTION');
    if($metodo()){
        report("Actualizacion a version ".$version." finalizada existosamente",R_OK);
        setDbVersion($version);
        execQuery('COMMIT');
    }else{
        report("Se detectaron errores actualizando a version ".$version,R_ERROR);
        execQuery('ROLLBACK');
        exit(1);
    }
    $version++;
    $metodo='updateV'.$version;
}
exit(0);
