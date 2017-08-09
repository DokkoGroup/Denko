<?php
// +----------------------------------------------------------------------+
// |              Denko GET Parameters Manager version 0.1                |
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
 * Documentación Denko GET Parameters Manager 0.1
 *
 * Manager para los parámetros GET
 *
 * @link http://www.dokkogroup.com.ar/
 * @copyright Copyright (c) 2007 Dokko Group.
 * @author Denko Developers Group <info@dokkogroup.com.ar>
 * @package Denko
 * @version 0.1
 */

/**
 * Directorio donde se encuentra el framework.
 * @ignore
 */
if (!defined('DENKO_DIR')){
    define('DENKO_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
}

/**
 * Luego se incluyen los archivos.
 * @ignore
 */
require_once(DENKO_DIR.'dk.denko.php');

/**
 * @package Denko
 */
class DK_GetParamsManager{

    /**
     * Obtiene el Query String del GET.
     *
     * Ignora las claves que se pasan por parámetro.
     *
     * @param array $ignoreKeys claves en el GET que serán ignoradas
     * @return string
     * @access public
     * @static
     */
    public static function getQueryString($ignoreKeys=array()){
        $queryString = '';
        foreach($_GET as $param => $value){
            if (count($ignoreKeys) > 0 && in_array($param,$ignoreKeys)) continue;
            $queryString.= (strlen($queryString)==0?'':'&').$param.'='.$value;
        }
        return $queryString;
    }

    /**
     * Obtengo el PHP_SELF con Query String.
     *
     * Ignora las claves que se pasan por parámetro.
     *
     * @param array $ignoreKeys claves en el GET que serán ignoradas
     * @return string
     * @access public
     * @static
     */
    public static function getPHPSELF($ignoreKeys=array()){
        $queryString = DK_GetParamsManager::getQueryString($ignoreKeys);
        return basename($_SERVER['PHP_SELF']).'?'.$queryString;
    }

    /**
     * Retorna la url con el agregado de un parámetro en el GET
     *
     * @param string $url url
     * @param string $param_name nombre del parámetro GET
     * @param string $param_value valor del parámetro GET
     * @static
     * @access public
     * @return string url con el parámetro GET agregado
     */
    public static function addParam($url,$param_name,$param_value){
        $explodeUrl = explode('?',$url);
        if(($count = count($explodeUrl)) == 1){
            return $url.'?'.$param_name.'='.$param_value;
        }
        $explodeUrl[1] = $param_name.'='.$param_value.'&'.$explodeUrl[1];
        return implode('?',$explodeUrl);
    }

    /**
     * Remueve un parámetros de la URL
     *
     * @param string $url url
     * @param string $param parámetros que remover
     * @static
     * @return string url sin el parámetro
     */
    public static function removeParam($url,$param){

        $ampPos = strpos($url,'?');
        if($ampPos === false){
            return $url;
        }

        $get_params = explode('&',substr($url,$ampPos+1));
        $params = array();
        foreach($get_params as $get_param){
            $param_value = explode('=',$get_param);
            if($param_value[0] == $param){
                continue;
            }
            $params[] = $get_param;
        }

        # Retorno la url sin el parámetro
        return substr($url,0,$ampPos).'?'.implode('&',$params) ;
    }
}
################################################################################
?>