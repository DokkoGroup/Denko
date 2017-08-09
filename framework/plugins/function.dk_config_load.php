<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_config_load} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_config_load
 * <br>
 * Purpose: Cargar archivo de speech.
 * Si recibe language_code, utiliza ese idioma, sino utiliza el de la variable global $speech_lang
 * Si no hay nada especificado, utiliza es_AR
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dokkogroup.com.ar/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_config_load
 * @param array $params parámetros
 * @param Smarty &$smarty instancia de Smarty
 */
function smarty_function_dk_config_load($params, &$smarty){
    global $speech_lang;
    $lang=empty($params['lang'])?(empty($speech_lang)?'es_AR':$speech_lang):$params['lang'];
    $section = !empty($params['section']) ? $params['section'] : null;
    # Obtengo el archivo de speechs que debo cargar
    $file = 'speech.'.$lang.'.conf';
    # En caso de cargar una sección en particular
    if(!empty($section)){
        $smarty->config_load($file,$section);
    } else {
        $smarty->config_load($file);
    }
}