<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Denko Smarty {dk_urlformat} modifier plugin
 *
 * Type: modifier
 * <br>
 * Name: dk_urlformat
 * <br>
 * Purpose: Formatea una cadena en URL
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dojo/index.php/Denko%20Plugin%3A%20modificador%20dk_urlformat {dk_urlformat} (Denko wiki)
 * @param string $string cadena de texto
 * @return string
 */
################################################################################
function smarty_modifier_dk_urlformat($string){
	if(strpos(trim($string),'https://')===0){
		return trim(html_entity_decode(strip_tags($string)));
	}
	return 'http://'.str_replace('http://','',trim(html_entity_decode(strip_tags($string))));
}
################################################################################
