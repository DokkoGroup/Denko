<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_htmlwrap} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_htmlwrap
 * <br>
 * Purpose: Similar al wordwrap de PHP, pero en texto HTML
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_htmlwrap {dk_htmlwrap} (Denko wiki)
 * @param string $string código html
 * @param integer $length tamaño máximo de linea
 * @param string $break caracter de corte
 * @return string
 */
################################################################################
function smarty_modifier_dk_htmlwrap($string, $length = 80, $break = "\n"){
	$splited = preg_split("/([<>])/", $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $count = count($splited);
    $purge = array();
    for($i = 0; $i < $count; $i++){
        if(isset($splited[$i-1]) && $splited[$i-1] == '>' && isset($splited[$i+1]) && $splited[$i+1] == '<'){
            $splited[$i] = wordwrap($splited[$i],$length,$break,1);
        }
    }
    if($splited[0] != '<'){
        $splited[0] = wordwrap($splited[0],$length,$break,1);
    }
    if($count > 1 && $splited[$count-1] != '<'){
        $splited[$count-1] = wordwrap($splited[$count-1],$length,$break,1);
    }
    return implode('',$splited);
}
################################################################################
?>