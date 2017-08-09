<?php
// +----------------------------------------------------------------------+
// |                    Denko HTML Element version 0.1                    |
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
 * Documentación Denko Elemento HTML 0.1
 *
 * Elemento HTML
 *
 * @link http://www.dokkogroup.com.ar/
 * @copyright Copyright (c) 2007 Dokko Group.
 * @author Denko Developers Group <info@dokkogroup.com.ar>
 * @package Denko
 * @subpackage HTMLElement
 * @version 0.1
 */

/**
 * Directorio donde se encuentra el framework.
 * @ignore
 */
if (! defined('DENKO_DIR')){
    define('DENKO_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

/**
 * Luego se incluyen los archivos.
 * @ignore
 */
require_once (DENKO_DIR . 'dk.denko.php');

/**
 * @package Denko
 * @subpackage HTMLElement
 */
class DK_HTMLElement {
    
    /**
     * Arreglo que contiene las propiedades del HTMLElement
     *
     * @var array
     * @access protected
     */
    var $_properties = array ();
    
    /**
     * Arreglo que contiene las propiedades que no necesitan valor (por ej, disabled)
     *
     * @var array
     * @access protected
     */
    var $_noValueProperties = array ();
    
    /**
     * Arreglo que contiene los eventos javascript
     *
     * @var array
     * @access protected
     */
    var $_jsEvents = array ();
    
    /**
     * Etiqueta del elemento
     *
     * @var string
     * @access protected
     */
    var $_token = '';
    
    /**
     * Indica si es un bloque
     *
     * @var boolean
     * @access protected
     */
    var $_isBlock = true;
    
    /**
     * Código HTML interno en el HTMLElement (solo si el elemento es bloque)
     *
     * @var string
     * @access protected
     */
    var $_innerHtml = '';

    /**
     * Constructora de la clase
     *
     * @param string $token etiqueta del elemento
     * @param boolean $isBlock indica si el elemento será un bloque
     * @access public
     */
    function DK_HTMLElement($token, $isBlock = true) {
        $this->_token = $token;
        $this->_isBlock = $isBlock;
    }

    /**
     * Agrega un evento javascript al elemento
     *
     * @param string $jsEvent nombre del evento
     * @param string $jsCode código javascript que ejecutará
     * @access public
     */
    function addJsEvent($jsEvent, $jsCode) {
        $jsEvent = strtolower($jsEvent);
        
        /**
         * Si el evento no está registrado, creo la entrada en el arreglo
         *
         * @ignore
         */
        if (! isset($this->_jsEvents [$jsEvent])){
            $this->_jsEvents [$jsEvent] = array ();
        }
        /**
         * Adjunto el código javascript para el evento
         *
         * @ignore
         */
        $this->_jsEvents [$jsEvent] [] = $jsCode;
    }

    /**
     * Agrega un propiedad al elemento.
     *
     * @param string $property
     * @param string $value
     * @access public
     */
    function addProperty($property, $value = null) {
        $property = strtolower($property);
        
        /**
         * Si la propiedad no tiene valor, lo agrego al arreglo de propiedades
         * sin valor.
         *
         * @ignore
         */
        if ($value === null){
            if (! in_array($property, $this->_noValueProperties)){
                $this->_noValueProperties [] = $property;
            }
        }

        /**
         * En caso contrario, lo agrego al arreglo de propiedades con valor
         *
         * @ignore
         */
        else{
            $this->_properties [$property] = $value;
        }
    }

    /**
     * Remueve una propiedad al elemento.
     *
     * @param string $property nombre de la propiedad
     * @access public
     */
    function removeProperty($property) {
        $property = strtolower($property);
        if (array_key_exists($property, $this->_properties)){
            unset($this->_properties [$property]);
        }elseif (in_array($property, $this->_noValueProperties)){
            //Denko::array_remval($property,&$this->_noValueProperties);
            Denko::array_remval($property, $this->_noValueProperties);
        }
    }

    /**
     * Remueve todas las propiedades del elemento
     *
     * @param array $ignoreProperties propiedades que no se eliminarán.
     * @access public
     */
    function removeAllProperties($ignoreProperties = array()) {
        foreach ( $this->_properties as $property => $value ){
            if (in_array($property, $ignoreProperties)) continue;
            unset($this->_properties [$property]);
        }
    }

    /**
     * Retorna el arreglo con las propiedades del elemento
     *
     * @return array
     * @access public
     */
    function getProperties() {
        return $this->_properties;
    }

    /**
     * Retorna el valor de una propiedad.
     *
     * @param string $property nombere de la propiedad
     * @return string
     * @access public
     */
    function getProperty($property) {
        $property = strtolower($property);
        return array_key_exists($property, $this->_properties) ? $this->_properties [$property] : null;
    }

    /**
     * Setea el código HTML interno en el objeto.
     *
     * Solo funciona si el objeto es un bloque.
     *
     * @param string $html código HTML
     * @access public
     */
    function setInnerHtml($html) {
        $this->_innerHtml = $html;
    }

    /**
     * Retorna el código HTML correspondiente al elemento
     *
     * @return string
     * @access public
     */
    function html() {
        $html = '<' . $this->_token;
        foreach ( $this->_properties as $property => $value ){
            $html .= ' ' . $property . '="' . $value . '"';
        }
        foreach ( $this->_noValueProperties as $noValueProperty ){
            $html .= ' ' . $noValueProperty;
        }
        foreach ( $this->_jsEvents as $jsEvents => $jsCode ){
            $html .= ' ' . $jsEvents . '="javascript:';
            foreach ( $jsCode as $code ){
                $html .= $code;
            }
            $html .= '"';
        }
        if ($this->_isBlock == false){
            $html .= ' />';
        }else{
            $html .= '>' . $this->_innerHtml . '</' . $this->_token . '>';
        }
        return $html;
    }

}

/**
 * @package Denko
 * @subpackage HTMLElement
 */
class DK_HTMLOption extends DK_HTMLElement {

    /**
     * Constructora
     *
     * @param string $value valor del option
     * @param string $text texto del option
     * @param boolean $selected indica si tendrá la propiedad "selected"
     * @access public
     */
    function DK_HTMLOption($value, $text, $selected = false) {
        parent::DK_HTMLElement('option');
        $this->addProperty('value', $value);
        $this->setInnerHtml($text);
        if ($selected === true){
            $this->addProperty('selected');
        }
    }
}

/**
 * @package Denko
 * @subpackage HTMLElement
 */
class DK_HTMLSelect extends DK_HTMLElement {
    
    /**
     * Arreglo que contendrá los elementos correspondientes a los options
     *
     * @var array
     * @access protected
     */
    var $_options = array ();

    /**
     * Constructora
     *
     * @access public
     */
    function DK_HTMLSelect() {
        parent::DK_HTMLElement('select');
    }

    /**
     * Agrega un Option
     *
     * Crea un option y lo agrega al Select.
     *
     * @param string $value valor del Option
     * @param string $text texto del Option
     * @param boolean $selected indica si tendrá la propiedad "selected"
     * @access public
     */
    function addOption($value, $text, $selected = false) {
        $this->_options [] = new DK_HTMLOption($value, $text, $selected);
    }

    /**
     * Setea un elemento Option como seleccionado
     *
     * @param string $value valor del elemento Option
     */
    function setSelected($value) {
        foreach ( $this->_options as $k => $v ){
            $option = &$this->_options [$k];
            $option instanceof DK_HTMLOption;
            if ($option->getProperty('value') == $value){
                $option->addProperty('selected');
            }
        }
    }

    /**
     * Retorna una referencia un elemento Option.
     *
     * @param string $value valor (value) del elemento Option
     * return &object
     * @access public
     */
    function getOption($value) {
        $options = array ();
        foreach ( $this->_options as $key => $option ){
            $option instanceof DK_HTMLOption;
            if ($option->getProperty('value') == $value){
                return $this->_options [$key];
            }
        }
    }

    /**
     * Retorna el HTML correspondiente al Select
     *
     * @return string
     * @access public
     */
    function html() {
        $innerHtml = '';
        foreach ( $this->_options as $option ){
            $option instanceof DK_HTMLOption;
            $innerHtml .= '
            ' . $option->html();
        }
        $this->setInnerHtml($innerHtml);
        return parent::html();
    }

}
