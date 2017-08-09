<?php
/**
 * AudDataObject abstraction for DB_DataObject
 */
require_once 'DB/DataObject.php';
require_once '../commons/fecha.php';

class AudDataObject extends DB_DataObject{
    ///////////////////////////////////////////////////////////////////////////
    public $aud_ins_date;
    public $aud_upd_date;    
    public $selfDAO=null;
    
    ///////////////////////////////////////////////////////////////////////////

    function insert(){
        $this->aud_ins_date=Fecha::fechaActual();
        return parent::insert();
    }

    ///////////////////////////////////////////////////////////////////////////

    function update(){
        $this->aud_upd_date=Fecha::fechaActual();
        return parent::update();
    }

    ///////////////////////////////////////////////////////////////////////////
    
    function AudDataObject(){
        $this->selfDAO=$this;
    }

    ///////////////////////////////////////////////////////////////////////////

}
