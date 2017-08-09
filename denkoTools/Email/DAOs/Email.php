<?php
/**
 * Table Definition for email
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Email extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'email';                           // table name
    public $id_email;                        // int(10)  not_null primary_key unsigned auto_increment
    public $fromemail;                       // string(100)  not_null
    public $fromname;                        // string(100)  not_null
    public $destination;                     // string(100)  not_null
    public $emailsubject;                    // string(250)  not_null
    public $message;                         // blob(65535)  not_null blob binary
    public $sent;                          // string(1)  not_null
    public $sendtries;                       // int(10)  not_null unsigned
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Email',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
