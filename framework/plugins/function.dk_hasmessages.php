<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_hasmessages} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_hasmessages
 * <br>
 * Purpose: Averigua si existen mensajes que mostrar
 * <br>
 * Input:
 * <br>
 * - Opcionales:
 *   - type = Tipo de mensaje ('ERROR', 'OK' o 'WARNING').
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dokkogroup.com.ar/index.php/http://wiki.dojo/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_hasmessages {dk_hasmessages} (Denko wiki)
 * @param Array $params parámetros
 * @param Smarty $smarty instancia de Smarty
 * @return string
 */
################################################################################
function smarty_function_dk_hasmessages($params,&$smarty){

    # Verifico si existe el parámetro 'type'
    if(!empty($params['type'])){

        # Convierto el tipo de mensaje a mayúsculas
        $type = strtoupper($params['type']);

        # Verifico que el parámetro 'type' sea válido
        if(!in_array($type,array('OK','WARNING','ERROR'))){
            Denko::plugin_fatal_error('el parámetro <b>type</b> es incorrecto','dk_hasmessages');
        }

        # Si existe y es válido, solo retornará si encuentra mensajes de ese único tipo
        else{
            $hasMessages = Denko::hasMessages($type);
        }
    }

    # En caso contrario, retorno si existe mensaje de cualquier tipo
    else{
        $hasMessages = Denko::hasOkMessages() || Denko::hasErrorMessages() || Denko::hasWarningMessages();
    }

    # En caso que exista la variable 'assign', asigno el resultado a esa variable
    if(!empty($params['assign'])){
        $smarty->assign($params['assign'],$hasMessages);
        return '';
    }

    # Retorno el resultado
    return $hasMessages;
}
################################################################################
?>