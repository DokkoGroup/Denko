<?php
// +----------------------------------------------------------------------+
// |              Denko DAO Action Performer version 0.1                  |
// +----------------------------------------------------------------------+
// |                          2007 Dokko Group                            |
// +----------------------------------------------------------------------+
// |  Copyright (c) 2007 Dokko Group                                      |
// |  Tandil, Buenos Aires, 7000, Argentina                               |
// |  All Rights Reserved.                                                |
// |                                                                      |
// | This software is the confidential and proprietary information of     |
// | Dokko Group. You shall not disclose such Confidential Information    |
// | and shall use it only in accordance with the terms of the license    |
// | agreement you entered into with Dokko Group.                         |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Dokko Group.                                                 |
// +----------------------------------------------------------------------+

/**
* Documentación Denko DAOLister ActionPerformer 0.1
*
* ActionPerformer para los DAOListers
*
* @author Denko Developers Group <info@dokkogroup.com.ar>
* @version 0.1
* @package DAOLister
* @copyright Copyright (c) 2007 Dokko Group.
*/

/**
* Directorio donde se encuentra el framework.
* @ignore
*/
if (!defined('DENKO_DIR')){
    define('DENKO_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
}

/**
* Se incluyen los archivos.
* @ignore
*/
require_once(DENKO_DIR.'dk.daolister.php');

/**
* Prefijo para el MultiAction
*/
define('DAOListerMultiActionPrefix','dkma_');

/**
* @package DAOLister
*/
class DK_MultiAction{

    /**
    * Elemento HTML que corresponde al select de acciones.
    *
    * @var DK_HTMLSelect
    * @access protected
    */
    var $_select = null;

    /**
    * Objeto que corresponde al DAOLister al que pertenece el MultiAction
    *
    * @var DK_DAOLister
    * @access protected
    */
    var $_daolister = null;

    /**
    * Texto correspondiente al alerta "no hay opciones elegidas"
    *
    * @var string
    * @access protected
    */
    var $_noSelectedAlert = 'Debe seleccionar elementos a los cuales aplicar la acción.';

    /**
    * Texto correspondiente a la opcion "-- acciones --"
    *
    * @var string
    * @access protected
    */
    var $_actionLabel = '-- acciones --';

    /**
    * Constructora de la clase.
    *
    * @param array $params seteos para el MultiAction
    * @param DK_DAOLister $daoLister DAOLister que contiene al MultiAction
    * @access public
    */
    function DK_MultiAction($params,&$daoLister){
        $this->_daolister = $daoLister;
        if(!empty($params['noSelectedAlert'])){
            $this->_noSelectedAlert = $params['noSelectedAlert'];
        }
        if(!empty($params['actionLabel'])){
            $this->_actionLabel = $params['actionLabel'];
        }
        $url = isset($params['url'])?$params['url']:basename($_SERVER['PHP_SELF']);
        $this->_select = new DK_HTMLSelect();
        $this->_select->addOption('null',$this->_actionLabel,true);
        $this->_select->addJsEvent('onchange','executeMultiAction(this,\''.DAOListerMultiActionPrefix.$daoLister->getName().'\',\''.$daoLister->getName().'\',\''.$this->_noSelectedAlert.'\',\''.$url.'\');');

        foreach($params as $action => $setting){
            if($action == 'actionLabel') continue;
            if($action == 'noSelectedAlert') continue;
            if($action == 'url') continue;
            $setting = explode('|',$setting);
            $this->_select->addOption($action,trim($setting[0]));
            $option = &$this->_select->getOption($action);
            $option instanceof DK_HTMLOption;
            if(isset($setting[1]) && strlen(trim($setting[1])) > 0){
                $option->addProperty('message',trim($setting[1]));
            }
        }
    }

    /**
    * Retorna el HTML correspondiente al select que contiene las acciones.
    *
    * @param array $params arreglo de parámetros
    * @return string
    * @access public
    */
    function htmlSelect($params){
        foreach($params as $param => $value){
            $this->_select->addProperty($param,($param == 'name')? Denko::toValidTagName($value) : $value);
        }
        return $this->_select->html();
    }

    /**
    * Retorna los hiddens (falta documentar!!)
    *
    * @return string
    * @access public
    */
    function htmlHiddenInputs(){
        $result = '';
        foreach($_GET as $param => $value){
            $input = new DK_HTMLElement('input',false);
            $input->addProperty('type','hidden');
            $input->addProperty('name',$param);
            $input->addProperty('value',$value);
            $result.= '
                    '.$input->html();
        }
        return $result;
    }

}

/**
* Contenedor de acciones para el DAOLister
*
* @package DAOLister
*/
class DAOActionPerformer{

    /**
    * Muestra una imagen de DB redimensionada. Parámetros:
    * <ul>
    *   <li>Requeridos:
    *     <ul>
    *       <li>dao: [STRING] nombre del dao que contiene la imagen</li>
    *       <li>id_dao: [STRING] PK del dao. Acá se describe como "id_dao", pero
    *       en realidad es "id_"+nombre del DAO (por ej, si el DAO se llama
    *       "foto", el parámetro debe ser "id_foto")</li>
    *       <li>colimg: [STRING] columna que contiene el DAO.</li>
    *     </ul>
    *   </li>
    *   <li>Opcionales:
    *     <ul>
    *       <li>width: [STRING] ancho al que se desea redimensionar.</li>
    *       <li>height: [STRING] alto al que se desea redimensionar.</li>
    *       <li>resize: [BOOLEAN] indica se se quiere redimensionar la imagen.</li>
    *       <li>quality: [INTEGER] porcentaje de calidad en la redimensión.</li>
    *     </ul>
    *   </li>
    * </ul>
    *
    * @param array $options arreglo de seteos
    * @static
    */
    function onImage($options){
        require_once 'HTTP/Download.php';
        $dao = Denko::daoFactory($options['dao']);
        $id_table = 'id_'.$options['dao'];
        $dao->selectAdd();
        $dao->selectAdd($id_table.','.$options['colimg']);
        $dao->$id_table = $options[$id_table];
        $dao->find(true);

        $blob = $dao->$options['colimg'];
        if(!isset($options['resize']) || $options['resize']=='true'){
            $quality = isset($options['quality'])?$options['quality']:100;
            $height = $options['height'];
            $width  = $options['width'];
            $blob   = Denko::createImage($blob,$width,$height,$quality);
        }
        // Seguridad: Si lo que estoy por entregar no es un JPG o un GIF, corto todo.
        if(substr($blob,6,4)=='JFIF'){
            $mimeType='jpeg';
        }elseif(substr($blob,0,3)=='GIF'){
            $mimeType='gif';
        }else{
            exit;
        }
        $dl = &new HTTP_Download();
        $dl->setData($blob);
        $imgTmpName = 'tempimage-'.$dao->__table.'-'.$options['colimg'].$dao->$id_table;
        $dl->setContentDisposition(HTTP_DOWNLOAD_INLINE,$imgTmpName);
        $dl->setBufferSize(1024*80); // 100 K
        $dl->setThrottleDelay(1); // 1 sec
        $dl->setContentType('image/'.$mimeType);
        $dl->send();
        exit(0);
    }

    /**
    * Ejecuta el ActionPerformer.
    *
    * Busca la accion en el GET y la ejecuta. La accion la identificará en el
    * GET con el parámetro "action".
    *
    * @access public
    */
    function execute(){
        if(isset($_GET['action'])){
            $method = 'on'.ucfirst(strtolower($_GET['action']));
            $this->$method($_GET);
            Denko::redirect($_SERVER['HTTP_REFERER']);
            exit;
        }
    }

}
