<?php
/**
 * obligatorio: name
 * opcionales:
 *   - hiddens: inputs que se incluirán en los hiddens. O sea, Los nombres de los
 *   - inputs que estan dentro del bloque y que no son 'dkf_input'.
 */
function smarty_block_dk_filters($params, $content, &$smarty, &$repeat){
    if($repeat == false){
        if(empty($params['name'])){
            Denko::plugin_fatal_error('el parámetro <b>name</b> es requerido','dk_filters');
        }
        $daoLister = &DK_DAOLister::getDaoLister($smarty);
        $daoLister->addForm($params['name']);

        $params['action'] = isset($params['action'])?$params['action']: basename($_SERVER['PHP_SELF']);
        $params['method'] = isset($params['method'])?$params['method']:'get';

        $formElement = new DK_HTMLElement('form');
        foreach($params as $param => $value){
            if($param == 'hiddens') continue;
            if($param == 'ignore')  continue; // alias de hiddens
            $formElement->addProperty($param,$value);
        }

        $formElement->setInnerHtml($daoLister->htmlHiddenInputs($params).$content);
        return $formElement->html();
    }
}
###############################################################################