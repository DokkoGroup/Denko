<?php
/**
 * Table Definition for log
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Log extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'log';                             // table name
    public $id_log;                          // int(10)  not_null primary_key unsigned auto_increment
    public $id_tipolog;                      // int(10)  multiple_key unsigned
    public $descripcion;                     // string(255)
    public $datos;                           // blob(65535)  blob binary
    public $fecha;                           // datetime(19)  multiple_key binary
    public $indice1;                         // int(10)  multiple_key unsigned
    public $indice2;                         // int(10)  multiple_key unsigned
    public $indice3;                         // int(10)  multiple_key unsigned
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Log',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    ###########################################################################
    function getTipoLog(){
        $daoTipoLog = Denko::daoFactory('tipolog');
        $daoTipoLog instanceof DataObjects_Tipolog;
        $daoTipoLog->id_tipolog = $this->id_tipolog;
        $daoTipoLog->find(true);
        return $daoTipoLog;
    }
    ###########################################################################
}
