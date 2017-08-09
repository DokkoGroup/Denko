<?php
/**
 * Este DAO es un ejemplo de como dar soporte AudDataObject a un objeto dao,
 * simplemente cambiando la herencia.
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_EjemploDAO extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tarjeta';                         // table name
    public $id_tarjeta;                      // int(10)  not_null primary_key unsigned auto_increment
    public $id_persona;                      // int(10)  multiple_key unsigned
    public $codigo;                          // string(255)  not_null
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Tarjeta',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

}
