<?php
/**
 * Denko DAOLister 0.1
 *
 * Listador de clases DAO para Smarty
 *
 * @author Denko Developers Group <info at dokkogroup dot com dot ar>
 * @copyright Copyright (c) 2007 Dokko Group.
 * @link http://www.dokkogroup.com.ar/
 *
 * @package DAOLister
 * @version 0.1
 */

/**
 * Directorio donde se encuentra el framework. Luego se incluyen los archivos.
 *
 * @ignore
 */
if (!defined('DENKO_DIR')){
    define('DENKO_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
}

/**
 * @ignore
 */
require_once(DENKO_DIR.'dk.daolister.filter.php');

/**
 * @package DAOLister
 */
class DK_DAOLister{

    /**
     * Objeto que referencia al DAO.
     *
     * @access protected
     * @var object
     */
    var $_dao = null;

    /**
     * Arreglo que contiene los DAOLister Filter.
     *
     * @access protected
     * @var array
     */
    var $_filters = null;

    /**
     * Arreglo que contiene los DAOLister Order.
     *
     * @access protected
     * @var array
     */
    var $_orders = null;

    /**
     * Arreglo que contiene los seteos.
     *
     * @access protected
     * @var array
     */
    var $_settings = array();

    /**
     * Arreglo que contiene los formularios de los DAOLister Filter.
     *
     * @access protected
     * @var array
     */
    var $_forms = array();

    /**
     * Resultados por página.
     *
     * @access protected
     * @var integer
     */
    var $_resultsPerPage = null;

    /**
     * Número de última página.
     *
     * @access protected
     * @var integer
     */
    var $_lastPage = 0;
    
    /**
     * Número de página actual.
     *
     * @access protected
     * @var integer
     */
    var $_actualPage = 0;

    /**
     * Número de resultados (DAOs) de la consulta
     *
     * @access protected
     * @var integer
     */
    var $_results = 0;

    /**
     * Indica el nro del primer resultado en el listado.
     *
     * @access protected
     * @var integer
     */
    var $_beginResult = 0;

    /**
     * Indica el nro del último resultado en el listado.
     *
     * @access protected
     * @var integer
     */
    var $_endResult = 0;

    /**
     * Objeto que referencia al DAOLister Multiaction
     *
     * @access protected
     * @var object
     */
    var $_multiaction  = null;

    /**
     * Path del archivo de configuraciones
     *
     * @access protected
     * @var string
     */
    var $_configFolder = '../config/';

    /**
     * La constructora de la clase. Los seteos que debe tener este arreglo son
     *
     * <ul>
     *   <li>Requeridos:
     *     <ul>
     *       <li>name: nombre del daolister</li>
     *     </ul>
     *   </li>
     *   <li>Opcionales:
     *     <ul>
     *       <li>table: nombre de la tabla [default: hará referencia a la tabla
     *         que se especificó en el parámetro 'name']
     *       </li>
     *       <li>config: nombre del archivo de configuración</li>
     *       <li>configFolder: carpeta donde se aloja el archivo de configuraciones</li>
     *       <li>query: query que se aplicará al daolister.</li>
     *       <li>groupBy: se le aplica un groupBy al daolister</li>
     *       <li>resultsPerPage: resultados por página</li>
     *       <li>orderBy: se le aplica un orderBy al daolister</li>
     *       <li>dao: DAO que usará el DAO Lister.</li>
     *     </ul>
     *   </li>
     *  </ul>
     *
     * @param array $settings arreglo de seteos
     * @access public
     */
    function DK_DAOLister($settings){
        $this->_settings = $settings;

        // Arreglo de seteos para los filtros:
        $this->_settings['filter_settings'] = array();

        // Si ESTÁ seteado el archivo de configuraciones lo cargo
        // Si NO está seteado el archivo de configuraciones, asumo los seteos en el tpl
        if(isset($settings['config'])){
            // cargo el archivo
            $settingsFromIni = parse_ini_file($this->_configFolder.$settings['config'],true);
            foreach($settingsFromIni as $setting => $value){
                if(!is_array($value)){
                    // Es un seteo de daolister, y doy prioridad a los seteos en el template
                    $this->_settings[$setting] = strtolower(!empty($settings[$setting])?$settings[$setting]:$value);
                }else{
                    // En caso que sea multiaction, cargo los seteos en el índice 'dkma_settings'
                    if($setting == 'dkma'){
                        $this->_settings['dkma_settings'] = $value;
                    }
                    // En caso que sea filtro, cargo los seteos en el índice 'filter_settings'
                    else{
                        $this->_settings['filter_settings'][strtolower($setting)] = $value;
                    }
                }
            }
        }

        // Seteo un nombre válido al DAOLister
        $this->_settings['name'] = Denko::toValidTagName($this->_settings['name']);

        // Seteo los resultados por página
        if(isset($this->_settings['resultsPerPage'])){
            $this->_resultsPerPage = $this->_settings['resultsPerPage'];
        }

        // Compruebo que este seteado el nombre de la tabla
        // En caso contrario, el nombre de la tabla adquiere el nombre del daolister
        if(!isset($this->_settings['table'])){
            $this->_settings['table'] = $this->getName();
        }else{
            $this->_settings['table'] = strtolower($this->_settings['table']);
        }

        $GLOBALS['DK_LISTER'][$this->getName()] = $this;
    }

    /**
     * Declara la creación de un filtro. Agrega seteos para la creación de un
     * DAOLister Filter. Esta funcion es llamada únicamente desde el plugin
     * Smarty dkf_declare, que es para declarar un DAOLister Filter.
     *
     * @param array $settings seteos para la declaración del filtro
     * @access public
     */
    function filterDeclare($settings){
        $name = Denko::toValidTagName($settings['name']);

        if(empty($this->_settings['filter_settings'][$name])){
            $this->_settings['filter_settings'][$name] = array();
        }
        foreach($settings as $setting => $value){
            if($setting == 'name') continue;
            $this->_settings['filter_settings'][$name][$setting] = $value;
        }
    }

    /**
     * Agrega seteos para la creación de un DAOLister Order. Esta funcion es
     * llamada únicamente desde el plugin Smarty dko_declare, que es para declarar
     * un DAOLister Order.
     *
     * @param array $settings seteos para la creación del DAOLister Order
     * @access public
     */
    function orderDeclare($settings){
        // Arreglo de seteos para los orders:
        if(!isset($this->_settings['order_settings'])){
            $this->_settings['order_settings'] = array();
        }
        $settings['name'] = Denko::toValidTagName($settings['name']);
        $this->_settings['order_settings'][$settings['name']] = $settings;
    }

    /**
     * Crea los DAOLister Filter en base a las configuraciones declaradas. Esta
     * función es invocada cuando piden el html de un input de un
     * DAOLister Filter o cuando comienza a ciclar el DAOLister.
     *
     * @access protected
     */
    function _createFilters(){
        if($this->_filters == null){
            $this->_filters = array();
            if(count($this->_settings['filter_settings']) == 0) return;
            foreach($this->_settings['filter_settings'] as $filterName => $filterSetting){
                if(!isset($filterSetting['type'])){
                    trigger_error('No puede crearse el filtro '.$filterName.' sin haber seteado el <b>type</b>',E_USER_ERROR);
                }
                eval('$this->_filters[$filterName] = new DK_DAOListerFilter'.ucfirst(strtolower($filterSetting['type'])).'($filterName,$filterSetting,$this);');
            }
        }
    }

    /**
     * Crea los DAOLister Orderen base a las configuraciones declaradas.
     *
     * @access protected
     */
    function _createOrders(){
        if($this->_orders == null && isset($this->_settings['order_settings']) && count($this->_settings['order_settings']) > 0){
            $this->_orders = array();
            foreach($this->_settings['order_settings'] as $orderName => $orderSetting){
                $this->_orders[$orderName] = new DK_DAOListerOrder($orderSetting,$this);
            }
            $this->_setOrderPriority();
        }
    }

    /**
     * Obtiene el DAOLister más cercano en el stack de Smarty. Sirve en los
     * plugins Smarty para obtener el DAOLister con el que se está operando
     * actualmente.
     *
     * @param object &$smarty instancia Smarty actual.
     * @return DK_DAOLister
     * @static
     */
    static function getDaoLister(&$smarty){
        return $GLOBALS['DK_LISTER'][Denko::getSmartyParentTag($smarty,'dk_daolister')];
    }

    /**
     * Retorna el nombre del DAOLister.
     *
     * @return string
     * @access public
     */
    function getName(){
        return $this->_settings['name'];
    }

    /**
     * Asigna un formulario al DAOLister. Estos formularios son los que contienen
     * a los DAOLister Filter.
     *
     * @param string $formName nombre del formulario
     * @access public
     */
    function addForm($formName){
        $formName = Denko::toValidTagName($formName);
        if(!isset($this->_forms[$formName])){
            $this->_forms[$formName] = array();
        }
    }

    /**
     * Asigna el input de un DAOLister Filter a un formulario.
     *
     * @param string $formName nombre del formulario
     * @param string $inputName nombre del input
     * @access public
     */
    function assignInputToForm($formName,$inputName){
        if(!isset($this->_forms[$formName])){
            $this->_forms[$formName] = array();
        }
        $this->_forms[$formName][] = Denko::toValidTagName($inputName);
    }

    /**
     * Obtiene el nombre del formulario de que contiene un DAOLister Filter.
     *
     * @param string $filterName nombre del filtro que pertenece al Form
     * @return string
     * @access public
     */
    function getFormName($filterName){
        foreach($this->_forms as $formName => $array){
            if(in_array($filterName,$array)){
                return $formName;
            }
        }
        return null;
    }

    /**
     * Retorna el código HTML de un DAOLister Filter. Los valores en el arreglo
     * de parámetros serán agregados como propiedades del input.
     *
     * @param array $params arreglo de parámetros
     * @return string
     * @access public
     */
    function getFilterInput($params){
        $this->_createFilters();
        $filter = &$this->_filters[Denko::toValidTagName($params['name'])];
        $filter instanceof DK_DAOListerFilter;        
        return $filter->getHtmlInput($params);
    }

    /**
     * Retorna la URL que genera el DAOLister Order. Los valores en el arreglo
     * de parámetros serán agregados como parámetros GET en la URL generada.
     *
     * @param array $params arreglo de parámetros
     * @return string
     * @access public
     */
    function getOrderUrl($params){
        $name = Denko::toValidTagName($params['name']);
        $this->_createOrders();
        if(!isset($this->_orders[$name])){
            trigger_error('<b>Denko Error:</b> el ordenamiento <b>'.$params['name'].'</b> no existe',E_USER_ERROR);
        }

        $order = &$this->_orders[$name];
        $order instanceof DK_DAOListerOrder;
        return $order->getUrl($params['order']);
    }

    /**
     * Retorna el código HTML correspondiente a los inputs hiddens de los
     * DAOLister Filter que van dentro de los form.
     *
     * @param array $params arreglo de parámetros
     * @return string
     * @access public
     */
    function htmlHiddenInputs($params){

        $formName = Denko::toValidTagName($params['name']);
        $ignoreInputs = !empty($params['hiddens'])?$params['hiddens']:(!empty($params['ignore'])?$params['ignore']:null);
        $ignoreInputs = $ignoreInputs ? explode(',',$ignoreInputs) : array();

        $filterPrefix = DK_DAOListerFilter::getPrefix().$this->getName().'_';
        $prefixLength = strlen($filterPrefix);
        $htmlHiddenInputs = '';
        foreach($_GET as $key => $value){
            if(strlen($value) > 0){
                $filterName = substr($key,$prefixLength);
                if(!in_array($filterName,$this->_forms[$formName]) && !in_array($key,$ignoreInputs)){
                    $hiddenElement = new DK_HTMLElement('input',false);
                    $hiddenElement->addProperty('type','hidden');
                    $hiddenElement->addProperty('name',$key);
                    $hiddenElement->addProperty('value',htmlspecialchars($value));
                    $htmlHiddenInputs.= '
                        '.$hiddenElement->html();
                }
            }
        }
        return $htmlHiddenInputs;
    }

    /**
     * Función principal del DAOLister. Aplica todos los seteos al DAO (whereAdds,
     * orderBy, etc) provenientes de configuraciones directas en el DAOLister y
     * los filtros, y setea las variables del paginador. Además, crea los
     * DAOLister Filter en caso que no se hayan creado previamente.
     *
     * @access protected
     */
    function _createLister(){
        if($this->_dao == null){
            $this->_createFilters();
            if(isset($this->_settings['dao'])){
                $this->setDao($this->_settings['dao']);
            }else{
                $this->_dao = DB_DataObject::factory(isset($this->_settings['table'])?$this->_settings['table']:$this->getName());
            }
            if(!empty($this->_settings['query'])){
                $this->_dao->whereAdd($this->_settings['query']);
            }           
            foreach($this->_filters as $filterName => $filter){
                $this->_filters[$filterName]->apply($this->_dao);
            }
            
            if($this->_resultsPerPage != null && count($this->_dao->keys())==0 ){
                echo "DENKO - DAOLISTER WARNING: No se puede utilizar resultsPerPage en tablas sin clave principal";
                $this->_resultsPerPage = null;
            } 
            
            if($this->_resultsPerPage != null){
                $this->_results = $this->_dao->count();
            }
            $this->_actualPage = (isset($_GET[$this->getParamPage()]) && $_GET[$this->getParamPage()] > 0)?$_GET[$this->getParamPage()]:1;
			
			// Se aplica el selectFields 
			if(!empty($this->_settings['selectFields'])){
				$this->_dao->selectAdd();
				$this->_dao->selectAdd($this->_settings['selectFields']);
			}
			
			// Se aplica el selectFields 
			if(!empty($this->_settings['selectAdd'])){
				$this->_dao->selectAdd($this->_settings['selectAdd']);
			}

            // Se aplica el "groupBy"
            if(isset($this->_settings['groupBy'])){
                $this->_dao->groupBy($this->_settings['groupBy']);
            }

            $iniLimit = 0;
			// Se limitan los resultados por página
            if($this->_resultsPerPage != null){
                $this->_setResultsPerPage();
                $iniLimit = ($this->_actualPage-1)*$this->_resultsPerPage;
                $this->_lastPage = ceil($this->_results/$this->_resultsPerPage);
                if($iniLimit > $this->_results){
                    $this->_actualPage = $this->_lastPage;
                    $iniLimit = ($this->_actualPage-1)*$this->_resultsPerPage;
                }
            }

            /**
             * @todo usar stripos para saber si existe la palabra "limit" en el
             * query. A diferencia de strpos, stripos no es case sensitive.
             * stripos aparece recién en PHP 5.
             */
            $this->_applyOrderBy();
            if($this->_resultsPerPage != null && (!isset($this->_settings['orderBy']) || (isset($this->_settings['orderBy']) && strpos($this->_settings['orderBy'],' limit ') === false))){
                $this->_dao->limit($iniLimit,$this->_resultsPerPage);
            }

            $count = $this->_dao->find();
            if(empty($this->_results)){
                $this->_results=$count;
            }
            $this->_beginResult = $count?($iniLimit+1):0;
            $this->_endResult = $count?($this->_beginResult+$count-1):0;

            // Renombro el GET del paginador.
            $_GET['dkp_'.$this->getName()] = $this->_actualPage;
        }
    }

    /**
     * Ejecuta el fetch para el DAO. Es pública porque es llamado en el
     * plugin Smarty dk_lister.
     *
     * @return object
     * @access public
     */
    function fetch(){
        $this->_createLister();
        return $this->_dao->fetch();
    }

    /**
     * Retorna el prefijo del DAOLister actual. Es útil para reconocer los
     * parámetros en el GET que corresponden al DAOLister.
     *
     * @return string
     * @access public
     */
    function getParamPage(){
        return DK_DAOLister::getPaginatorPrefix().$this->getName();
    }

    /**
     * Retorna los nombres de las variables que corresponden al DAOLister Paginator.
     *
     * @return array
     * @static
     */
    function getPageVars(){
        return array('dkp_actual','dkp_last','dkp_begin','dkp_end','dkp_results');
    }

    /**
     * Asigna las variables del paginador al template.
     *
     * @param Smarty $smarty instancia Smarty
     * @access public
     */
    function setPageVars(&$smarty){

        // Primero creamos el DAOLister. Esto se debe ha que existe el caso de
        // querer mostrar las variables del paginador antes mostrar el listado.
        $this->_createLister();
        $dkp_vars = DK_DAOLister::getPageVars();

        // En caso que existan variables asignadas al template con estos nombres,
        // se guardan para usarlos después.
        foreach($dkp_vars as $dkp_var){
            $GLOBALS['DK_LISTER']['CACHED'][$dkp_var] = $smarty->get_template_vars($dkp_var);
        }
        $smarty->assign('dkp_actual',$this->_actualPage);
        $smarty->assign('dkp_last',$this->_lastPage);
        $smarty->assign('dkp_begin',$this->_beginResult);
        $smarty->assign('dkp_end',$this->_endResult);
        $smarty->assign('dkp_results',$this->_results);
    }

    /**
     * Restaura las variables del paginador al template. En caso que hayan
     * existido variables asignadas al template con estos nombres (antes de
     * asignar las variables), se restauran para usarlos después.
     *
     * @param Smarty $smarty instancia Smarty
     * @access public
     */
    function restorePageVars(&$smarty){
        $dkp_vars = DK_DAOLister::getPageVars();
        foreach($dkp_vars as $dkp_var){
            $smarty->assign($dkp_var,isset($GLOBALS['DK_LISTER']['CACHED'][$dkp_var])?$GLOBALS['DK_LISTER']['CACHED'][$dkp_var]:null);
        }
    }

    /**
     * Crea el DAOLister Multiaction
     *
     * @param array $params arreglo de parámetros
     * @access public
     */
    function createMultiAction($params){
        foreach($params as $key => $value){
            $this->_settings['dkma_settings'][$key] = ($key == 'name') ? Denko::toValidTagName($value) : $value;
        }
        require_once(DENKO_DIR.'dk.actionperformer.php');
        $this->_multiaction = new DK_MultiAction($this->_settings['dkma_settings'],$this);
    }

    /**
     * Retorna el DAOLister Multiaction
     *
     * @return DK_MultiAction
     * @access public
     */
    function getMultiAction(){
        return $this->_multiaction;
    }

    /**
     * Indica si fué seteado el Multiaction
     *
     * @return boolean
     * @access public
     */
    function isSetMultiAction(){
        return ($this->_multiaction != null);
    }

    /**
     * Retorna el valor de un seteo
     *
     * @param string $setting nombre del seteo
     * @return object|string|integer|boolean
     */
    function getSetting($setting){
        return $this->_settings[$setting];
    }

    /**
     * Asigna al DAO del DAOLister un DAO externo.
     *
     * @param object &$dao objeto DAO
     * @access public
     */
    function setDao(&$dao){
        $this->_dao = $dao;
    }

    /**
     * Retorna la referencia al DAO.
     *
     * @return DB_DataObject
     * @access public
     */
    function getDAO(){
        return $this->_dao;
    }

    /**
     * Aplica los orderBy al DAO. Los obtiene del tag Smarty "dk_daolister" y
     * de los DAOLister Order
     *
     * @access protected
     */
    function _applyOrderBy(){
        $this->_createOrders();
        if($this->_orders != null){
            $orderCondition = isset($this->_settings['orderBy'])?$this->_settings['orderBy']:'';
            foreach($this->_orders as $orderName => $order){
                $actualOrderCondition = $this->_orders[$orderName]->getOrderCondition();
                if($actualOrderCondition != null){
                    $orderCondition.= (strlen($orderCondition)==0?'':', ').$this->_orders[$orderName]->getOrderCondition();
                }
            }
            if($orderCondition != ''){
                $this->_dao->orderBy($orderCondition);
            }
        }
        elseif(isset($this->_settings['orderBy'])){
            $this->_dao->orderBy($this->_settings['orderBy']);
        }
    }

    /**
     * Organiza el orden de prioridad de los ordenamientos.
     *
     * Las prioridad de condiciones se determina en base al orden de aparición
     * de los filtros en el GET. Notar que la URL que retorna el método 'getUrl()'
     * de la clase 'DK_DAOListerOrder' siempre agrega su nombre como 1er parámetro.
     * Tendrá mayor prioridad el último order que se haya invocado.
     *
     * @access protected
     */
    function _setOrderPriority(){
        $prefix        = DK_DAOListerOrder::getPrefix().$this->getName().'_';
        $prefixLength  = strlen($prefix);
        $orderPriority = array();
        foreach($_GET as $key => $value){
            if(substr($key,0,$prefixLength) == $prefix){
                $orderName = substr($key,$prefixLength);
                if(isset($this->_orders[$orderName])){
                    $orderPriority[$orderName] = &$this->_orders[$orderName];
                }
            }
        }
        if(count($orderPriority) > 0){
            foreach($this->_orders as $key => $filter){
                if(!array_key_exists($key,$orderPriority)){
                    $orderPriority[$key] = &$this->_orders[$key];
                }
            }
            $this->_orders = $orderPriority;
        }
    }

    /**
     * Setea la cantidad de resultados por página que debe mostrar el listado.
     *
     * @access protected
     */
    function _setResultsPerPage(){
        $rpp = null;
        $rppPrefix = $this->_resultsPerPageGETParam();
        if(isset($_GET[$rppPrefix])){
            $rpp = trim($_GET[$rppPrefix]);
            if(!Denko::isInt($rpp)){
                $rpp = $this->_resultsPerPage;
            }
        }elseif(isset($this->_settings['resultsPerPage'])){
            $rpp = $this->_settings['resultsPerPage'];
        }else{
            $rpp = $this->_resultsPerPage;
        }
        $this->_resultsPerPage = $rpp;
    }

    /**
     * Obtiene el nombre del parámetro GET correspondiente a la cantidad de
     * resultados por página que mostrará el listado.
     *
     * @return string
     * @access protected
     */
    function _resultsPerPageGETParam(){
        return DK_DAOLister::getPaginatorPrefix().$this->getName().'_rpp';
    }

    /**
     * Retorna el prefijo para el paginador.
     *
     * @return string
     * @access public
     * @static
     */
    function getPaginatorPrefix(){
        return 'dkp_';
    }

    /**
     * Retorna el prefijo para el DAOLister
     *
     * @return string
     * @access public
     * @static
     */
    function getPrefix(){
        return 'dkl_';
    }
}
