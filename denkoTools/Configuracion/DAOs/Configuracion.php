<?php
/**
 * Table Definition for configuracion
 */
require_once '../DAOs/AudDataObject.php';
require_once 'Validate.php';

define("configuracion_string",0);
define("configuracion_integer",1);
define("configuracion_image",2);
define("configuracion_sound",3);
define("configuracion_multiselect",4);
define("configuracion_time",5);
define("configuracion_date",6);

class DataObjects_Configuracion extends AudDataObject
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'configuracion';                         // table name
    public $id_configuracion;                      // int(10)  not_null primary_key unsigned auto_increment
    public $nombre;                             // string(150)  not_null
    public $valor;                             // blob
    public $estado;                      // int(10)  not_null
    public $tipo;                      // int(10)  not_null
    public $metadata;                      // blob
    public $descripcion;                      // string(150) not_null
    public $aud_ins_date;                    // datetime(19)  not_null binary
    public $aud_upd_date;                    // datetime(19)  not_null binary
    public $id_tipoconfiguracion;           // int(10)  not_null
    public $indice1;           // int(10)  not_null
    public $indice2;           // int(10)  not_null    
    public $filtro;                      // blob
    
    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Configuracion',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /**
     * @var DataObjects_Configuracion Copia del objeto para saber si hubo cambios al monento de hacer update.
     * @access protected
     */
    protected $clon;

    private function evaluateConfigurationExpression($expression, $indice1 = null, $indice2 = null) {
        $aux = ereg_replace("GET-(([a-zA-Z0-9_/-])*)-", ' (isset($_GET[\'\\1\'])?$_GET[\'\\1\']:\'\') ', $expression);
        $aux = ereg_replace("SES-(([a-zA-Z0-9_/-])*)-", ' ($_SESSION[\'\\1\']?$_SESSION[\'\\1\']:\'\') ', $aux);
    
        // se pasa false como tercer parametro para evitar que se creen configuraciones con indices solo por preguntar si se deben mostrar o no. OJO SI SE SACA
        $aux = ereg_replace("DBC-(([a-zA-Z0-9_/-])*)-", ' getConfigByHierarchy(\'\\1\',$indice1,$indice2,false) ', $aux);
        // se pasa false como tercer parametro para evitar que se creen configuraciones con indices solo por preguntar si se deben mostrar o no. OJO SI SE SACA
        // ..Fede..
        $aux = '$res = (' . $aux . ') == true;';
        $res = '-||-ERROR-||-';
        @ eval($aux . ";");
        if ($res === '-||-ERROR-||-'){
            return null;
        }
        return $res;
    }
    
    function hayQueMostrar(){
        if($this->filtro=='') {return true;}
        $res=self::evaluateConfigurationExpression($this->filtro,$this->indice1,$this->indice2);
        if($res===null){
            Denko::addErrorMessage('filter_error',null,array('%field'=>$this->descripcion." (".$this->nombre.")"));
            return false;
        }
        return $res;
    }

    function parseOptions(){
        $arr=explode('|',$this->metadata);
        $res=array();
        foreach($arr as $val){
            if($val=='') continue;
            $ax=explode('=',$val);
            if(count($ax)!=2) continue;
            $res[$ax[0]]=$ax[1];
        }
        return $res;
    }

    function validarTipo(){
        if($this->estado==0) return true;

        switch($this->tipo){
            case configuracion_string:
                return Validate::string($this->valor,$this->parseOptions());
            case configuracion_integer:
                return Validate::number($this->valor,$this->parseOptions());
            case configuracion_image: return true;
            case configuracion_sound: return false;
            case configuracion_multiselect:
                $arr=explode('|',$this->metadata);
                $keys=array();
                foreach($arr as $value){
                    $aux=explode(':',$value);
                    $keys[]=$aux[0];
                }
                return in_array($this->valor,$keys);
            case configuracion_time:
                return preg_match("/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]\$/",$this->valor);
            case configuracion_date: return true;
            default: return true;
        }
    }
    
    function getPossibleValues(){
        if($this->tipo!=configuracion_multiselect){
            return null;
        }
        $arr=explode('|',$this->metadata);
        $res=array();
        foreach($arr as $value){
            $aux=explode(':',$value,2);
            $res[]=array('valor'=>$aux[0] , 'descripcion' => ($aux[1]=='')?$aux[0]:$aux[1]);
        }
        return $res;
    }

    function insert(){
        if(!$this->validarTipo()){
            Denko::addErrorMessage('config_error_in_type',null,array('%field'=>$this->descripcion." (".$this->nombre.")"));
            return false;
        }
        $res=parent::insert();
        if($res){
            Denko::addOkMessage('config_saved',null,array('%field'=>$this->descripcion." (".$this->nombre.")"));
        }
        return $res;
    }

    function update(){
        if(!$this->validarTipo()){
            Denko::addErrorMessage('config_error_in_type',null,array('%field'=>$this->descripcion." (".$this->nombre.")"));
            return false;
        }
        $modificado=false;
        foreach($this->clon as $key => $value ){
            if($key=="clon") continue;
            if($key=="selfDAO") continue;
            if($this->$key == $value) continue;
            $modificado=true;
        }
        if(!$modificado){
            return 0;
        }
        $res=parent::update();
        if($res){
            Denko::addOkMessage('config_saved',null,array('%field'=>$this->descripcion." (".$this->nombre.")"));
        }
        return $res;
    }
    
    function fetch(){
        $res=parent::fetch();
        $this->clon=clone($this);
        return $res;
    }

}
