<?php
/**
 * Denko Smarty plugin
 * @package Denko
 * @subpackage plugins
 */

/**
 * Inny Smarty {dk_include} function plugin
 *
 * Type: function
 * <br>
 * Name: dk_include
 * <br>
 * Purpose: Incluye un archivo JS o CSS al template
 * <br>
 * Input:
 * <br>
 * - Requeridos:
 *   - file = ruta del archivo
 * - Opcionales:
 *   - ignoreVersion = Ignora la versión del archivo. Por default, se agrega una versión del archivo para evitar el cacheo del archivo.
 *   - version = versión del archivo. Por defecto, le agrega fecha y hora de update del archivo (ymdHis)
 *   - inline = indica si el archivo será agregado dentro del header del HTML (FALSE), o en la linea donde se pidió (TRUE).
 *   - compress = indica si el archivo se comprimirá (si se minifica el archivo)
 *
 * @author Dokko Group Developers Team <info at dokkogroup dot com>
 * @link http://wiki.dokkogroup.com.ar/index.php/http://wiki.dojo/index.php/Denko%20Plugin%3A%20funci%F3n%20dk_include {dk_include} (Denko wiki)
 * @param array $params parámetros
 * @param Smarty &$smarty instancia de Smarty
 * @return string
 */
################################################################################
function smarty_function_dk_include($params, &$smarty) {
    if (empty($params['file'])) {
        Denko::plugin_fatal_error('el parámetro <b>file</b> es requerido','dk_include');
    }

    # Obtengo la información del path
    $pathinfo = pathinfo($params['file']);
    $file_basename  = $pathinfo['basename'];
    $file_extension = strtolower($pathinfo['extension']);
    $file_directory = $pathinfo['dirname'];

    # Verifico si los archivos deben estar versionados.
    $version  = '';
    $filepath = $params['file'];
    if (empty($params['ignoreVersion'])) {

        # Verifico si el archivo debe ser comprimido.
        $compress = (!isset($params['compress']) || ($params['compress'] === true) || (strtolower($params['compress']) === 'true'));

        # La versión puede estar seteada entre los parámetros.
        # En caso contrario, genero la versión tomando en cuenta la fecha de actualización del archivo.
        $version  = !empty($params['version']) ? $params['version'] : (date('ymdHis',file_exists($params['file']) ? filemtime($params['file']) : time()));
        $filepath = $file_directory.'-'.$version;

        # La compresión de archivos por ahora está habilitada para los javascript
        if ($compress == true) {
            smarty_function_dk_include_create_compressed_file($params['file']);
            $filepath .= '-c';
        }

        # Completo de armar el path del archivo
        $filepath .= '/'.$file_basename;
    }

    # Obtengo los parámetros extra que se setearon en el plugin
    $extraParams = '';
    foreach ($params as $param => $value) {
        if ($param == 'file' || $param == 'ignoreVersion' || $param == 'inline' || $param == 'compress') {
            continue;
        }
        $extraParams.= ' '.$param.'="'.$value.'"';
    }

    # Genero el HTML correspondiente al include
    switch ($file_extension) {

        # Cascading Style Sheets
        case 'css':
            # En caso de no existir el parámetro 'media', asumo media="screen"
            # http://www.w3.org/TR/CSS2/media.html
            if (empty($params['media'])) {
                $extraParams .= ' media="screen"';
            }
            $html = '<link rel="stylesheet" type="text/css" href="'.$filepath.'"'.$extraParams.' />';
            break;

        # Javascript
        case 'js':
            $html = '<script type="text/javascript" src="'.$filepath.'"'.$extraParams.'></script>';
            break;

        # Default
        default: break;
    }

    $inline = isset($params['inline']) ? (is_string($params['inline']) ? strtolower($params['inline']) : $params['inline']) : 'auto';

    # En caso que el plugin deba comportarse como siempre.
    if ($inline == 'auto') {
        return smarty_function_dk_include_verify_included_file($params['file']) ? $html : '';
    }

    # En caso que el código HTML deba ir en el header.
    if ($inline == 'false' || $inline === false) {

        # Agrego el HTML en el arreglo de includes.
        if (smarty_function_dk_include_verify_included_file($params['file'])) {
            if (!isset($GLOBALS['DENKO_INCLUDES'])) {
                $GLOBALS['DENKO_INCLUDES'] = array();
            }

            # Si se trata de un archivo css donde no se especificó que se ignore
            # la versión del archivo, se pasa el $filepath en vez del html generado.
            if (empty($params['ignoreVersion']) && $file_extension == 'css' && file_exists('../minify/csstidy/class.csstidy.php')) {
            	if (!empty($params['version'])) { $GLOBALS['DENKO_INCLUDES']['version'] = $params['version']; }
            	$GLOBALS['DENKO_INCLUDES']['cssfilepath'][] = basename($filepath);
            } else {
            	$GLOBALS['DENKO_INCLUDES']['html'][] = $html;
            }
        }
        return '';
    }

    # En caso que el código HTML deba ser retornado.
    if ($inline == 'true' || $inline == true) {
        return $html;
    }

    # Improbable que pase, pero...
    return '';
}
################################################################################
function smarty_function_dk_include_verify_included_file($filepath) {
    if (!isset($GLOBALS['DK_INCLUDES'])) {
        $GLOBALS['DK_INCLUDES'] = array();
    }
    if (in_array($filepath,$GLOBALS['DK_INCLUDES'])) {
        return false;
    }
    $GLOBALS['DK_INCLUDES'][] = $filepath;
    return true;
}
################################################################################
function smarty_function_dk_include_minimizeCss($dirIn, $dirOut = null) {
	if (!$dirOut) { $dirOut = $dirIn; }
	if (file_exists('../minify/csstidy/class.csstidy.php')) {
		require_once '../minify/csstidy/class.csstidy.php';
	
		$cssCode = file_get_contents($dirIn);
		$cssTidy = new csstidy();
		$cssTidy instanceof csstidy;
		$cssTidy->set_cfg('remove_last_;', TRUE);
		$cssTidy->load_template('highest_compression');
		$cssTidy->parse($cssCode);
		file_put_contents($dirOut, $cssTidy->print->plain());
	} else {
		copy($dirIn, $dirOut);
	}
}
################################################################################
function smarty_function_dk_include_create_compressed_file($file_path) {

    # Se obtienen datos respecto al archivo.
	if (!file_exists($file_path) && strrchr($file_path, '.') == '.css' && file_exists(substr($file_path, 0, -3).'less')) {
		$originalFilePath = substr($file_path, 0, -3).'less';
		$originalFileExt  = 'less';
	} else {
    	$originalFilePath = $file_path;
    	$originalFileExt  = substr(strrchr($file_path, '.'), 1);
	}

    # Obtengo el path del archivo en su versión comprimida.
    $newFilePath = 'templates_c/compressed.'.basename($file_path);

    # En caso que el archivo no exista, lo genero. Si se trata de un archivo less
    # se realiza la conversión al css. Por último se comprime el archivo.
    if (!file_exists($newFilePath) || filemtime($originalFilePath) > filemtime($newFilePath)) {

  		if ($originalFileExt == 'js') {
	        require_once '../minify/JSMin.php';
	        $js_minified = trim(JSMin::minify(file_get_contents($file_path)));
	        file_put_contents($newFilePath, $js_minified);

		} elseif ($originalFileExt == 'css') {
			smarty_function_dk_include_minimizeCss($originalFilePath, $newFilePath);

		} elseif ($originalFileExt == 'less' && file_exists('../commons/lessc.inc.php')) {
			require_once '../commons/lessc.inc.php';
			lessc::ccompile($originalFilePath, $newFilePath);			
			smarty_function_dk_include_minimizeCss($newFilePath);
		}
    }
}
################################################################################
?>