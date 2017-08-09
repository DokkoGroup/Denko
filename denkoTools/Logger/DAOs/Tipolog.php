<?php
/**
 * Table Definition for tipoLog
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Tipolog extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tipolog';                         // table name
    public $id_tipolog;                      // int(10)  not_null primary_key unsigned auto_increment
    public $id_padre;                        // int(10)  multiple_key unsigned
    public $nombre;                          // string(255)
    public $descripcion;                     // string(255)
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Tipolog',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
