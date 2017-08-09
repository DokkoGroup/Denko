<?php
/**
 * Función que se encarga de reemplazar el tag "<!-- @@DENKO_INCLUDES@@ -->"
 * por las inclusiones de archivos previamente invocadas. 
 */
function smarty_outputfilter_dk_include($source, &$smarty) {

    # En caso que deban incluirse archivos en el header.
    if (isset($GLOBALS['DENKO_INCLUDES'])) {
        $cssFiles   = (isset($GLOBALS['DENKO_INCLUDES']['cssfilepath']) && !empty($GLOBALS['DENKO_INCLUDES']['cssfilepath'])) ? $GLOBALS['DENKO_INCLUDES']['cssfilepath'] : null;
        $otherFiles = (isset($GLOBALS['DENKO_INCLUDES']['html']) && !empty($GLOBALS['DENKO_INCLUDES']['html'])) ? $GLOBALS['DENKO_INCLUDES']['html'] : null;

		# Si existen inclusiones ya realizadas se agregan comunmente.
        $html = '';
        if ($otherFiles) {
	        foreach ($otherFiles as $include) { $html .= $include."\n"; }
        }

		# Si se definieron archivos css sin ignorar la versión se unifican los mismos.
		if ($cssFiles) {
			$fileName = md5(implode('', $cssFiles));
			$filePath = 'templates_c/compressed.'.$fileName.'.css';
			
			# Si el archivo principal no existe lo genero.
			if (!file_exists($filePath)) {
				smarty_outputfilter_generate_main_file($cssFiles, $filePath);

			# Si el archivo principal existe pero posee una fecha de modificación más
			# antigua que los que lo componen, se regenera el mismo.
			} else {
				$oneTime = false;
				foreach ($cssFiles as $css) {
					if (!$oneTime && filemtime($filePath) < filemtime('templates_c/compressed.'.$css)) {
						$oneTime = true;
						smarty_outputfilter_generate_main_file($cssFiles, $filePath);
					}
				}
			}
			$version = (!empty($GLOBALS['DENKO_INCLUDES']['version'])) ? $GLOBALS['DENKO_INCLUDES']['version'] : (date('ymdHis', file_exists($filePath) ? filemtime($filePath) : time()));
			$html .= '<link rel="stylesheet" type="text/css" href="styles-'.$version.'-c/'.$fileName.'.css" media="screen" />';
		}
        return str_replace('<!-- @@DENKO_INCLUDES@@ -->', $html, trim($source));
    }

    # En caso que no deban incluirse archivos en el header.
    return str_replace('<!-- @@DENKO_INCLUDES@@ -->', '', trim($source));
}
################################################################################
function smarty_outputfilter_generate_main_file($cssFiles, $filePath) {
	if (file_exists($filePath)) {
		unlink($filePath);
	}
	foreach ($cssFiles as $css) {
		file_put_contents($filePath, file_get_contents('templates_c/compressed.'.$css), FILE_APPEND);
	}
}
################################################################################
?>
