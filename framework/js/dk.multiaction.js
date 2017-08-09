/**
 * Funcionalidades para el Denko DAOLister MultiAction
 *
 * @autor Dokko group
 * @version 0.1
 */

/**
 * Retorna el arreglo de elementos del formulario que contiene el DAOLister.
 *
 * @param (String) dkmaFormName nombre del formulario
 * @return arreglo de elementos del formulario que contiene el DAOLister
 * @type Array
 */
function dkma_getFormInputs(dkmaFormName){
    eval("var input = document." + dkmaFormName + ".elements;");
    return input;
}

/**
 * Selecciona masivamente los elementos del DAOLister
 *
 * @param (String) dkmaFormName nombre del formulario de Multi Action
 * @param (String) type modo de seleccion ('all'|'none'|'invert')
 */
function dkma_select(dkmaFormName,type){
    var inputs = dkma_getFormInputs(dkmaFormName);
    for(i = 0; i < inputs.length; i++){
        if(inputs[i].type == 'checkbox' && inputs[i].getAttribute('dkma') == 'true'){
            switch(type){
                case 'all': inputs[i].checked = true; break;
                case 'none': inputs[i].checked = false; break;
                case 'invert': inputs[i].checked = !inputs[i].checked; break;

            }
        }
    }
}

/**
 * Ejecuta la accion
 *
 * @param (Object) dkmaSelect select que contiene las acciones
 * @param (String) dkmaFormName nombre del formulario que contiene al DAOLister
 * @param (String) daolister nombre del DAOLister
 * @param (String) noSelectedAlert mensaje que muestra cuando no hay elementos seleccionados
 * @param (String) url URL a donde se dirige para aplicar las acciones
 */
function executeMultiAction(dkmaSelect,dkmaFormName,daolister,noSelectedAlert,url){

    if(dkmaSelect.selectedIndex < 1){
        return;
    }
    var o = dkmaSelect.options[dkmaSelect.selectedIndex];
    var exec_action = dkma_exec(o.value,o.getAttribute('message'),dkmaFormName,daolister,noSelectedAlert,url);
    if(!exec_action){
        dkmaSelect.selectedIndex = 0;
    }
}

/**
 *
 *
 */
function dkma_exec(action,message,dkmaFormName,daolister,noSelectedAlert,url){

    var queryString = '';
    var inputs = dkma_getFormInputs(dkmaFormName);

    for(i = 0; i < inputs.length; i++){
        if(inputs[i].type == 'checkbox' && inputs[i].getAttribute('dkma') == 'true' && inputs[i].checked == true){
            queryString+='&id[]='+inputs[i].id;
        }
    }
    if(queryString.length == 0){
        alert(noSelectedAlert);
        return false;
    }
    else if(confirm(message)){
        for(i = 0; i < inputs.length; i++){
            if(inputs[i].type == 'hidden'){
                queryString += ('&' + inputs[i].name + '=' + inputs[i].value);
            }
        }
        window.location.href = url + '?' + 'action=' + action + queryString + '&referer=' + escape(location.href);
        return true;
    }
    return false;
}