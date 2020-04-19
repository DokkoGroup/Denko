<?php

/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_showmessages} block plugin
 *
 * Type: block
 * <br>
 * Name: dk_showmessages
 * <br>
 * Purpose: Muestra mensajes de error, ok y alerta
 * <br>
 * Input:
 * <br>
 * - Opcionales
 * - type = Tipo de mensaje que se desea mostrar ('ERROR', 'OK' o 'WARNING').
 * Si no está seteado este parámetro mostrará todos los mensajes, sin importar el tipo.
 * <br>
 * Examples:
 * <pre>
 * {dk_showmessages}
 * {if $dkm_type == "OK"}
 * <span class="message_ok">{$dkm_message}</span>
 * {elseif $dkm_type == "ERROR"}
 * <span class="message_error">{$dkm_message}</span>
 * {else}
 * <span class="message_warning">{$dkm_message}</span>
 * {/if}
 * {/dk_showmessages}
 *
 * {dk_showmessages type="ERROR"}
 * <span class="message_error">{$dkm_message}</span>
 * {/dk_showmessages}
 * </pre>
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20bloque%20dk_showmessages {dk_showmessages} (Denko wiki)
 * @param array $params parámetros
 * @param string $content En caso de que la etiqueta sea de apertura, este será null, si la etiqueta es de cierre el valor será del contenido del bloque del template
 * @param Smarty &$smarty instancia de Smarty
 * @param boolean &$repeat es true en la primera llamada de la block-function (etiqueta de apertura del bloque) y false en todas las llamadas subsecuentes
 * @return string
 */
################################################################################
/**
 * Bloque que muestra los mensajes de OK, WARNING y ERROR que se hayan reportado.
 * @param Array $params
 * @param String $content
 * @param Smarty $smarty
 * @param Boolean $repeat
 * @return String
 */
function smarty_block_dk_showmessages($params, $content, &$smarty, &$repeat) {
    $msgType = null;
    
    # En caso que pasen el parámetro 'type'
    if (! empty($params ['type'])){
        # Convierto en mayúsculas el tipo de error
        $type = strtoupper($params ['type']);
        
        # Verifico que si pasan el parámetro 'type' sea el correcto
        if (! in_array($params ['type'], array ('OK', 'WARNING', 'ERROR' ))){
            Denko::plugin_fatal_error('el parámetro <b>type</b> es incorrecto', 'dk_showmessages');
        }else{ # Guardo el tipo de mensaje que debo mostrar
            $msgType = Denko::hasMessages($type) ? $type : null;
        }
    }else{ # Verifico que haya mensajes cargados para mostrar
        $msgType = Denko::hasMessages('ERROR') ? 'ERROR' : (Denko::hasMessages('OK') ? 'OK' : (Denko::hasMessages('WARNING') ? 'WARNING' : null));
    }
    
    # En caso que no queden mensajes por mostrar
    if ($msgType === null){
        $repeat = false;
        return $content;
    }
    
    $dk_msgPack = array_shift($GLOBALS ['DENKO_MSGS'] [$msgType]);
    
    # En caso que no queden más mensajes por mostrar en el próximo ciclo del loop
    if (count($GLOBALS ['DENKO_MSGS'] [$msgType]) == 0){
        unset($GLOBALS ['DENKO_MSGS'] [$msgType]);
    }
    
    # Seteo el tipo de Mensaje:
    $smarty->assign('dkm_type', $msgType);
    $dkm_message = smartyGetConfig($dk_msgPack ['key'], $smarty);
    
    # Reemplazo los valores en el arreglo 'replaces' [claves de archivo config]
    if (count($dk_msgPack ['replaces']) > 0){
        foreach ( $dk_msgPack ['replaces'] as $key => $replace ){
            $dkm_message = str_replace($key, smartyGetConfig($replace, $smarty), $dkm_message);
        }
    }
    
    # Reemplazo los valores en el arreglo 'constants' [constantes]
    if (count($dk_msgPack ['constants']) > 0){
        foreach ( $dk_msgPack ['constants'] as $key => $constant ){
            $dkm_message = str_replace($key, $constant, $dkm_message);
        }
    }
    
    # Asigno al template la cadena resultado
    $smarty->assign('dkm_message', $dkm_message);
    $repeat = true;
    return $content;
}

################################################################################
/**
 * Obtiene el valor de una ocnfiguración de Smarty
 * @param String $key
 * @param Smarty $smarty
 * @return Mixed
 */
function smartyGetConfig($key, &$smarty) {
    $value = $smarty->get_config_vars($key);
    return ($value ? $value : $key);
}
################################################################################
