/**
 * Valida los tipos de archivos levantados en un input type file.
 * Chequea que la extension del archivo levantado en un input type file dado por $this, este entre los tipos
 * descriptos por el arreglo $supportedTypes.
 */
function validateSupportedType(inputFile,supportedTypes) {
	var fileExtension = inputFile.value.substring(inputFile.value.lastIndexOf(".") + 1,inputFile.value.length);
	for (var index = 0; index < supportedTypes.length; index++)
		if (fileExtension == supportedTypes[index])
			return;
	//Para borrarle el valor al input tengo que crear uno elemento nuevo, y reemplazarlo
	newInput = document.createElement('input');
	attribute = document.createAttribute('type');
	attribute.value = 'file';
	newInput.setAttributeNode(attribute);
	newInput.value = '';
	newInput.id = inputFile.id;
	newInput.name = inputFile.name;
	newInput.accept = "image/jpg,image/gif"
	container = inputFile.parentNode;
	//newInput.onchange = function() {validateSupportedType(this,supportedTypes);}
	newInput.onchange = inputFile.onchange;
	newInput.onblur = inputFile.onblur;
	newInput.onfocus = inputFile.onfocus;
	container.replaceChild(newInput,inputFile);
	alert('Esta propiedad solo acepta uno de los siguientes tipos de archivo: '+
		  supportedTypes +'.\n'+
		  'Usted ha elegido un archivo '+fileExtension);
}
//Remueve los bordes de un flash en IE al cargar la pagina.
function sacar_bordes_flash_IE() {
	n = navigator.userAgent;
	w = n.indexOf("MSIE");
	if((w > 0) && (parseInt(n.charAt(w+5)) > 5)){
		T = ["object","embed","applet"];
		for(j=0;j<2;j++){
			E = document.getElementsByTagName(T[j]);
			for(i=0;i<E.length;i++){
				P = E[i].parentNode;
				H = P.innerHTML;
				P.removeChild(E[i]);
				P.innerHTML=H;
				}
			}
		}
	}