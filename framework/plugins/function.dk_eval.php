<?php
/**
 * Smarty {dk_eval} function plugin
 *
 * Type:     function<br>
 * Name:     dk_eval<br>
 * Date:     July 31, 2008<br>
 * Purpose:  Retorna la evaluación de una variable en template
 * @author   Dokko Group <info at dokkogroup dot com dot ar>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */
require_once '../denko/dk.denko.php';

/**
 * Función dk_eval
 * @param Array $params
 * @param Smarty $smarty
 * @return String
 */
function smarty_function_dk_eval($params,&$smarty){

    /**
     * Verifico que exista el parámetro 'var'
     */
    if(empty($params['var'])){
        Denko::plugin_fatal_error('El parámetro <b>var</b> es requerido','dk_eval');
    }

    /**
     * Evalúo la variable, usando el plugin eval. Notar que la variable no se
     * puede evaluar directamente con el plugin 'eval'. Por ello se envuelve
     * en '{$'.VARIABLE.'}'
     */
    require_once $smarty->_get_plugin_filepath('function','eval');
    $options = array('var' => '{$'.$params['var'].'}');
    $evalcode = smarty_function_eval($options,$smarty);

    /**
     * En caso que exista el parámetro 'assign', lo asigno a esa variable en vez
     * de retornar el resultado
     */
    if(!empty($params['assign'])){
        $smarty->assign($params['assign'],$evalcode);
        return '';
    }

    /**
     * En caso que no exista el parametro 'assign', retorno el resultado
     */
    return $evalcode;
}
