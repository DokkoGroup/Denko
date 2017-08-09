<?php
/**
 * Table Definition for report
 */
require_once '../DAOs/AudDataObject.php';

class DataObjects_Report extends AudDataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'report';                          // table name
    public $id_report;                       // int(10)  not_null primary_key unsigned auto_increment
    public $query_report;                    // blob(65535)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Report',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    //----------------------------------------------------------------------------------
}