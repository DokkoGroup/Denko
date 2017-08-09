<?php
/**
 *
 */
################################################################################
function smarty_function_dk_url($params,&$smarty){
    if(!isset($params['url'])){
        $params['url'] = basename($_SERVER['PHP_SELF']);
    }
    $html = '';
    foreach($params as $param => $value){
        if($param == 'url') continue;
        $html.= ($html==''?'?':'&').$param.'='.$value;
    }
    return $params['url'].$html;
}
################################################################################
?>