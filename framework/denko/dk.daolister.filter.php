<?php
/**
 * Denko DAOLister Filter 0.1
 *
 * Filtros para los DAOLister
 *
 * @author Denko Developers Group <info at dokkogroup dot com dot ar>
 * @copyright Copyright (c) 2007 Dokko Group.
 * @link http://www.dokkogroup.com.ar/
 *
 * @package DAOLister
 * @version 0.1
 */

/**
 * Directorio donde se encuentra el framework.
 *
 * @ignore
 */
if (!defined('DENKO_DIR')){
    define('DENKO_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
}

/**
 * @ignore
 */
require_once(DENKO_DIR.'dk.html.element.php');

/**
 * @ignore
 */
require_once(DENKO_DIR.'dk.getparamsmanager.php');

/**
 * @package DAOLister
 * @subpackage DAOLister_Filter
 * @abstract
 */
abstract class DK_DAOListerFilter{

    /**
     * @var object Referenca al DAOLister al que pertenece
     * @access protected
     */
    protected $_daolister = null;

    /**
     * @var string Nombre del filtro
     * @access protected
     */
    protected $_name = '';

    /**
     * @var string nombre del filtro en el GET
     * @access protected
     */
    protected $_getname = '';

    /**
     * @var string Valor que tiene el filtro
     * @access protected
     */
    protected $_value = '';

    /**
     * @var array seteos del filtro
     * @access protected
     */
    protected $_settings = array();

    /**
     * @var object Elemento HTML del filtro
     * @access protected
     */
    protected $_inputElement = null;

    /**
     * @var string Prefijo del DAOLister Filter
     * @access protected
     * @static
     */
    protected static $prefix = 'dkf_';

    /**
     * Constructora de la clase
     *
     * @param string $name nombre del filtro
     * @param array settings seteos para el filtro
     * @param object &$daolister daolister al que pertenece el filtro
     * @access public
     */
    public function __construct($name,$settings,&$daolister){
        if(empty($settings['type'])){
            Denko::plugin_fatal_error('el parámetro <b>type</b> es requerido','DK_DAOListerFilter');
        }
        $this->_name      = Denko::toValidTagName($name);
        $this->_daolister = $daolister;
        $this->_settings  = $settings;
        $this->_getname   = DK_DAOListerFilter::getPrefix().($this->_daolister->getName()).'_'.$this->_name;
        $this->_settings['type'] = strtolower($this->_settings['type']);
    }

    /**
     * Genera el HTMLElement correspondiente al filtro y retorna su código HTML.
     *
     * @param array $htmlProperties propiedades del objeto.
     * @abstract
     * @access public
     * @return string
     */
    public abstract function getHtmlInput($htmlProperties);

    /**
     * Aplica el filtro al DAOLister
     *
     * @param DataObject &$dao DAO al que se aplica el filtro
     * @abstract
     * @access public
     * @return void
     */
    public abstract function apply(&$dao);

    /**
     * Retorna el nombre del filtro
     *
     * @access public
     * @return string nombre del filtro
     */
    public function getName(){
        return $this->_name;
    }

    /**
     * Retorna el prefijo de las variables correspondientes a este filtro.
     *
     * @access public
     * @return string prefijo de las variables correspondientes a este filtro
     */
    public function getGETParamName(){
        return $this->_getname;
    }

    /**
     * Retorna el valor correspondiente al filtro. Lo obtiene del GET.
     *
     * @access public
     * @return string valor correspondiente al filtro. Lo obtiene del GET.
     */
    public function getGETParamValue(){
        return isset($_GET[$this->getGETParamName()])?$_GET[$this->getGETParamName()]:null;
    }

    /**
     * Retorna el nombre del formulario que contiene al filtro
     *
     * @access public
     * @return string nombre del formulario que contiene al filtro
     */
    public function getFormName(){
        return $this->_daolister->getFormName($this->_name);
    }

    /**
     * Retorna el prefijo para los DAOLister Filter
     *
     * @static
     * @access public
     * @return string prefijo para los DAOLister Filter
     */
    public static function getPrefix(){
        return self::$prefix;
    }
}

/**
 * @package DAOLister
 * @subpackage DAOLister_Filter
 */
class DK_DAOListerFilterSelect extends DK_DAOListerFilter{

    /**
     * @var string Texto en la opción "mostrar todos"
     * @access protected
     * @static
     */
    protected static $_showAllText = '-- show all --';

    /**
     * @var array Arreglo que contiene los options del select
     * @access protected
     */
    protected $_options = array();

    /**
     * Constructora de la clase. Recibe un arreglo con los siguientes seteos:
     * <ul>
     *   <li>Requeridos:
     *     <ul>
     *       <li>table: nombre de la tabla (FK en el DAO)</li>
     *       <li>display: columna de la tabla que se mostrará en el input.</li>
     *       <li>o</li>
     *       <li>values: valores que adoptará el select. Está compuesto de la
     *       forma "CLAVE:VALOR|CLAVE:VALOR"</li>
     *       <li>queryToApply: query que se aplicará.</li>
     *     </ul>
     *   </li>
     *   <li>Opcionales:
     *     <ul>
     *       <li>queryOnInput: query para los datos del input (solo se aplica
     *       cuando se haya declarado el parámetro "table").</li>
     *       <li>showAllText: texto en la opción "-- show all --" (se aplica
     *       al dkf_input).</li>
     *       <li>autoSubmit: se usa en caso que el filtro se aplique ni bien
     *       cambie de estado (se aplica al dkf_input).</li>
     *       <li>queryToApply: query que se aplicará (solo es opcional en caso que NO se
     *       haya declarado el parámetro "table").</li>
     *       <li>orderBy: aplica el orderBy</li>
     *     </ul>
     *   </li>
     * </ul>
     *
     * @param string $name nombre del filtro
     * @param array settings seteos para el filtro
     * @param object &$daolister daolister al que pertenece el filtro
     * @access public
     */
    public function __construct($name,$settings,&$daolister){
        parent::__construct($name,$settings,$daolister);
        $this->_loadOptions();
    }

    /**
     * Carga los Options que pertenecen al select.
     *
     * @access protected
     */
    protected function _loadOptions(){
        // En caso que me pasen los values:
        if(!empty($this->_settings['values'])){
            if(empty($this->_settings['queryToApply'])){
                Denko::plugin_fatal_error('Si se setea el param <b>values</b>, debe setearse el param <b>queryToApply</b>','dkf_declare');
            }
            $keyValues = explode('|',$this->_settings['values']);
            $values = '';
            foreach($keyValues as $keyValue){
                $explodedKeyValue = explode(':',$keyValue);
                $values.= (strlen($values)==0?'':',').'"'.$explodedKeyValue[0].'"=>"'.$explodedKeyValue[1].'"';

            }
            eval('$this->_options = array('.$values.');');
        }
        // Sinó, busco las opciones en la DB. para ello deben setear el parámetro 'table' y 'display':
        else{
            if(empty($this->_settings['table'])){
                Denko::plugin_fatal_error('Si no se setea el param <b>values</b>, debe setearse el param <b>table</b>','dkf_declare');
            }
            if(empty($this->_settings['display'])){
                Denko::plugin_fatal_error('Si se setea el param <b>table</b>, debe setearse el param <b>display</b>','dkf_declare');
            }
            $daoTable = Denko::daoFactory($this->_settings['table']);
            $display  = $this->_settings['display'];
            $daoKeys  = $daoTable->keys();
            $id_table = $daoKeys[0];
            $daoTable->selectAdd();
            $daoTable->selectAdd($id_table.','.$display);
            if(isset($this->_settings['queryOnInput'])){
                $daoTable->whereAdd($this->_settings['queryOnInput']);
            }
            if(isset($this->_settings['orderBy'])){
                $daoTable->orderBy($this->_settings['orderBy']);
            }
            $daoTable->find();
            while($daoTable->fetch()){
                $this->_options[$daoTable->$id_table] = $daoTable->$display;
            }
        }
    }

    /**
     * Genera el HTMLElement correspondiente al filtro y retorna su código HTML.
     *
     * @param array $htmlProperties propiedades del objeto.
     * @return string
     * @access public
     */
    public function getHtmlInput($htmlProperties){
        $this->_inputElement = new DK_HTMLSelect();

        // Seteo el nombre del filtro:
        $this->_inputElement->addProperty('name',$this->getGETParamName());
        foreach($htmlProperties as $property => $value){
            if($property == 'name') continue;
            if($property == 'showAllText') continue;
            if($property == 'disableShowAllOption') continue;
            if($property == 'autoSubmit' && $this->getFormName() != null){
                $this->_inputElement->addJsEvent('onchange','document.'.$this->getFormName().'.submit();');
                continue;
            }
            $this->_inputElement->addProperty($property,$value);
        }

        // Agrego la opción 'mostrar todos':
        if(empty($htmlProperties['disableShowAllOption'])){
			$this->_inputElement->addOption('all',isset($htmlProperties['showAllText'])?$htmlProperties['showAllText']:self::$_showAllText,$this->getGETParamValue()==null||$this->getGETParamValue()=='all'?true:false);
        }

        // Agrego los options:
        foreach($this->_options as $key => $value){
            $this->_inputElement->addOption($key,$value);
        }

        // Seteo el valor que debe estar seleccionado:
        if($this->getGETParamValue() !== null && $this->getGETParamValue() !== 'all'){
            $this->_inputElement->setSelected($this->getGETParamValue());
        }

        return $this->_inputElement->html();
    }

    /**
     * Aplica el filtro al DAOLister
     *
     * @param DB_DataObject $dao referencia al DAO al cual se aplicará el filtro
     * @access public
     */
    public function apply(&$dao){
        if($this->getGETParamValue() != null && $this->getGETParamValue() != 'all'){
            if(isset($this->_settings['queryToApply'])){
                $query = str_replace('@VALUE@',$this->getGETParamValue(),$this->_settings['queryToApply']);
                eval('$dao->whereAdd("'.$query.'");');
            }else{
                $whereAdd = 'id_'.$this->_settings['table'].' = '.$this->getGETParamValue();
                $dao->whereAdd($whereAdd);
            }
        }
    }
}

/**
 * Este filtro se usa cuando se necesite filtrar por entradas del tipo texto.
 *
 * Crea un input del tipo "text"
 *
 * @package DAOLister
 * @subpackage DAOLister_Filter
 */
class DK_DAOListerFilterText extends DK_DAOListerFilter{

    /**
     * Constructora de la clase.
     *
     * Recibe un arreglo con los siguientes seteos:
     * <ul>
     *   <li>Requeridos:
     *     <ul>
     *       <li>queryToApply: query (whereAdd) que se aplicará al DAO</li>
     *     </ul>
     *   </li>
     * </ul>
     *
     * @param string $name nombre del filtro
     * @param array $settings seteos para el filtro
     * @param object &$daolister referencia al DAOLister al que pertenecerá el filtro
     * @access public
     */
    public function __construct($name,$settings,&$daolister){
        parent::__construct($name,$settings,$daolister);
    }

    /**
     * Genera el HTMLElement correspondiente al filtro y retorna su código HTML.
     *
     * @param array $htmlProperties propiedades del objeto.
     * @return string
     * @access public
     */
    public function getHtmlInput($htmlProperties){
        $this->_inputElement = new DK_HTMLElement('input',false);
        $this->_inputElement->addProperty('type','text');
        $this->_inputElement->addProperty('name',$this->getGETParamName());
        $value = isset($htmlProperties['value'])?$htmlProperties['value']:($this->getGETParamValue()!=null?$this->getGETParamValue():'');
        $this->_inputElement->addProperty('value',htmlspecialchars($value));
        foreach($htmlProperties as $property => $value){
            if($property == 'name') continue;
            if($property == 'type') continue;
            if($property == 'value') continue;
            if($property == 'formName') continue;
            $this->_inputElement->addProperty($property,$value);
        }
        return $this->_inputElement->html();
    }

    /**
     * Aplica el filtro al DAOLister
     *
     * @param object &$dao referencia al DAO al cual se aplicará el filtro
     * @access public
     */
    public function apply(&$dao){
        if($this->getGETParamValue() != null && $this->getGETParamValue() != ''){
            $query = str_replace('@VALUE@',$this->getGETParamValue(),$this->_settings['queryToApply']);
            $evalcode = '$dao->whereAdd("'.str_replace('"','\"',$query).'");';
            eval($evalcode);
        }
    }

}

/**
 * Proveé de links para ordenar los resultados de un DAOLister
 *
 * @package DAOLister
 * @subpackage DK_DAOListerOrder
 */
class DK_DAOListerOrder{

    /**
     * @var object DAOLister al que pertenece este DAOLister Order
     * @access protected
     */
    protected $_daolister = null;

    /**
     * @var array Arreglo de seteos
     * @access protected
     */
    protected $_settings = array();

    /**
     * @var string Nombre del DK_DAOListerOrder
     * @access protected
     */
    protected $_name = null;

    /**
     * @var string Prefijo de los DAOLister Order
     * @access protected
     * @static
     */
    protected static $prefix = 'dko_';

    /**
     * Constructora de la clase.
     *
     * El arreglo $settings recibe los siguientes seteos:
     * <ul>
     *   <li>Requeridos:
     *     <ul>
     *       <li>column: Columna a la que se tomará en la condición de ordenamiento.</li>
     *       <li>ó</li>
     *       <li>onAsc:  Condición que se aplicará en el ordenamiento ascendente.</li>
     *       <li>onDesc: Condición que se aplicará en el ordenamiento descendente.</li>
     *     </ul>
     *   </li>
     * </ul>
     *
     * @param array $settings
     * @param object &$daolister
     * @access public
     */
    public function __construct($settings,&$daolister){
        $this->_name = Denko::toValidTagName($settings['name']);
        $this->_settings = $settings;
        $this->_daolister = $daolister;
    }

    /**
     * Retorna el nombre del DAOLister Order.
     *
     * @return string
     * @access public
     */
    public function getName(){
        return $this->_name;
    }

    /**
     * Retorna el nombre que tendrá el DAOLister Order en el GET.
     *
     * @return string
     * @access public
     */
    public function getGETParamName(){
        return DK_DAOListerOrder::getPrefix().$this->_daolister->getName().'_'.$this->getName();
    }

    /**
     * Retorna el valor del DAOLister Order en el GET
     *
     * @access public
     * @return string valor del DAOLister Order en el GET
     */
    public function getGETParamValue(){
        $paramName = $this->getGETParamName();
        return !empty($_GET[$paramName])? $_GET[$paramName] : null;
    }

    /**
     * Retorna la URL que hace referencia al documento que mostrará al DAOLister ordenado
     *
     * @param string $order indica si el orden es ascendente o descendente
     * @access public
     * @return string URL que hace referencia al documento que mostrará al DAOLister ordenado
     */
    public function getUrl($order){

        $url = basename($_SERVER['PHP_SELF']).'?'.$this->getGETParamName().'='.($order=='asc'?'a':'d');
        $queryString = DK_GetParamsManager::getQueryString(array($this->getGETParamName()));
        return $url.(strlen($queryString)>0?('&'.$queryString):'');
    }

    /**
     * Retorna el fragmento de query MySQL correspondiente al ordenamiento
     *
     * @access public
     * @return string fragmento de query MySQL correspondiente al ordenamiento
     */
    public function getOrderCondition(){

        # Obtengo el ordenamiento del GET
        $paramValue = $this->getGETParamValue();

        # Obtengo el método de ordenamiento
        $orderMethod = empty($paramValue) ?
            (!empty($this->_settings['default']) ? $this->_settings['default'] : null) :
            ($paramValue == 'a' ? 'asc' : 'desc');

        # En caso que no esté seteado, retorno NULL
        if($orderMethod == null){
            return null;
        }

        # Retorno la string para la condición "order by"
        return isset($this->_settings['column']) ?
            ($this->_settings['column'].' '.$orderMethod) :
            $this->_settings[$orderMethod == 'asc' ? 'onAsc' : 'onDesc'];
    }

    /**
     * Retorna el prefijo de los DAOLister Order
     *
     * @static
     * @access public
     * @return string prefijo de los DAOLister Order
     */
    public static function getPrefix(){
        return self::$prefix;
    }

}
