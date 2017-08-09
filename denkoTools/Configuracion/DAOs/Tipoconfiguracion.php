<?php
/**
 * Table Definition for configuracion
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Tipoconfiguracion extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tipoconfiguracion';                       // table name
    public $id_tipoconfiguracion;                      // int(10)  not_null primary_key unsigned auto_increment
    public $nombre;                             // string(255)  not_null
    public $descripcion;                             // string(255)  not_null
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary
    
    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Configuracion',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

}
