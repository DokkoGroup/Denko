<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_gdate} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_gdate
 * <br>
 * Purpose: Retorna una fecha formateada tipo gmail
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_gdate {dk_gdate} (Denko wiki)
 * @param string $string cadena de texto con formato DATETIME (AAAA-MM-DD HH:MM:SS)
 * @return string
 */
################################################################################
function smarty_modifier_dk_gdate($string){

    $dateExploded = explode(' ',$string);
    $dateArray = explode('-',$dateExploded[0]);
    $timeArray = explode(':',$dateExploded[1]);

    # En caso que la fecha sea hoy, retorno sólo la hora y minutos (HH:MM)
    if($dateExploded[0] == date('Y-m-d') && !empty($dateExploded[1])){
        return $timeArray[0].':'.$timeArray[1];
    }

    $actualYear = date('Y');

    # En caso de estar en el mismo año, retorno el mes y el día (NOM DD)
    if($actualYear == $dateArray[0]){
        if(!isset($GLOBALS['DK_GDATE'])){
            $GLOBALS['DK_GDATE'] = array('Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
        }
        return $GLOBALS['DK_GDATE'][intval($dateArray[1])-1].' '.$dateArray[2];
    }

    # En caso de estar en distinto año, retorno el año, mes y día (YYYY-MM-DD)
    return $dateExploded[0];
}
################################################################################
?>