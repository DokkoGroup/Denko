<?php

/**
 *
 *
 */
class DokkoLogger {

    /**
     *
     *
     */
    public static function parseDatos($datos) {
        if (is_array($datos)){
            $cadena = "";
            foreach ( $datos as $dato ){
                $cadena .= $dato . "\1";
            }
            return $cadena;
        }else{
            return $datos;
        }
    }

    /**
     *
     *
     */
    public static function log($idTipoLog, $descripcion = null, $datos = null, $fecha = null, $indice1 = null, $indice2 = null, $indice3 = null) {
        $daoLog = Denko::daoFactory('log');
        $daoLog instanceof DataObjects_Log;
        $daoLog->id_tipolog = $idTipoLog;
        $daoLog->descripcion = (empty($descripcion)) ? "" : $descripcion;
        $daoLog->datos = (empty($datos)) ? "" : self::parseDatos($datos);
        $daoLog->fecha = (empty($fecha)) ? "" : $fecha;
        $daoLog->indice1 = (empty($indice1)) ? "" : $indice1;
        $daoLog->indice2 = (empty($indice2)) ? "" : $indice2;
        $daoLog->indice3 = (empty($indice3)) ? "" : $indice3;
        //$daoLog->aud_ins_date =
        if ($daoLog->insert()) return true;
        return false;
    }

    /**
     *
     *
     */
    public static function show() {
        $smarty = new Smarty();
        $smarty->display('logreport.tpl');
    }

    /**
     *
     *
     */
    public static function purge($fecha = null, $idTipoLog = null, $indice1 = null, $indice2 = null, $indice3 = null) {
        $daoLog = Denko::daoFactory('log');
        $daoLog instanceof DataObjects_Log;
        $value = true;
        if (! empty($idTipoLog)) $daoLog->id_tipolog = $idTipoLog;
        if (! empty($fecha)) $daoLog->fecha = $fecha;
        if (! empty($indice1)) $daoLog->indice1 = $indice1;
        if (! empty($indice2)) $daoLog->indice2 = $indice2;
        if (! empty($indice3)) $daoLog->indice3 = $indice3;
        $daoLog->find();
        while ( $daoLog->fetch() ){
            if (! $daoLog->delete()) $value = false;
        }
        return $value;
    }

    /**
     *
     *
     */
    public static function purgeAll() {
        $daoLog = Denko::daoFactory('log');
        $daoLog instanceof DataObjects_Log;
        $daoLog->find();
        $value = true;
        while ( $daoLog->fetch() ){
            if (! $daoLog->delete()) $value = false;
        }
        return $value;
    }
}