<?php
/**
 * Table Definition for temporal
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Temporal extends AudDataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'temporal';                        // table name
    public $id_temporal;                     // int(10)  not_null primary_key unsigned auto_increment
    public $id_temporal_parent;              // int(10)  multiple_key unsigned
    public $data;                            // blob(-1)  not_null blob binary
    public $flag;                            // int(10)  not_null multiple_key unsigned
    public $aud_ins_date;                    // datetime(19)  not_null multiple_key binary
    public $aud_upd_date;                    // datetime(19)  not_null binary
    public $index1;
    public $index2;
    public $index3;
    public $size;
    public $name;
    public $metadata;

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Temporal',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
