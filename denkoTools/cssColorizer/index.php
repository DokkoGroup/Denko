<?php 
require_once("funciones.php"); 
global $RGB,$fname;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
	<title>Dokko´s CSS Colorizer</title>
	<link rel="stylesheet" href="styles/main.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="styles/slider.css" type="text/css" media="screen" />
	
	<script type="text/javascript" src="js/mootools.js"></script>
	<script type="text/javascript" src="js/fxslide.js"></script>
	<script type="text/javascript">
		var iniColors = [<?php echo $RGB['r'];?>,<?php echo $RGB['g'];?>,<?php echo $RGB['b'];?>];	
	</script>
	<script type="text/javascript" src="js/slider.js"></script>
</head>
<body>
<table class="mainTable" cellpadding="0" cellspacing="10" border="0" <?php echo 'align="center"'?>>
	<tr>
		<td align="center" colspan="2">
			<img src="images/logo.gif" style="border:none;" alt="Dokko´s CSS Colorizer" title="Dokko´s CSS Colorizer"/>
		</td>
	</tr>
	<tr>
    	<td valign="top">
			<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post"  enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="4" border="0" class="colorizerSection">
				<tr>
					<td class="step1">
							<span style="font:bold 14px Arial, Helvetica, sans-serif;color:#000000;padding-left:32px;">Seleccione la gama de color:</span>
							<div id="red" class="slider advanced">
								<div class="knob"></div>
							</div>
							<div id="green" class="slider advanced">
								<div class="knob"></div>
							</div>
							<div id="blue" class="slider advanced">
								<div class="knob"></div>
							</div>
							<div id="colorBox" class="colorBox"></div>&nbsp;
							<span class="b">Gama de color seleccionada:&nbsp;</span>
							<span id="setColor" style="text-transform:uppercase;"></span>
					</td>
				</tr>
					
				<tr>
					<td class="step2">
					<span style="font:bold 14px Arial, Helvetica, sans-serif;color:#000000;padding-left:32px;">Cargue el archivo a modificar:</span><br/>
						<table cellpadding="0" cellspacing="0" border="0" class="step2Table" <?php echo 'align="center"'?>>
							<tr>
								<td align="right" class="label">Color seleccionado:</td>
								<td align="left" class="input"><input id="colorInput" type="text" name="col" value="<?php echo isset($_POST['col'])?$_POST['col']:"888800";?>" style="text-transform:uppercase;"/></td>
							</tr>
							<tr>
								<td align="right" class="label">Archivo a modificar:</td>
								<td align="left" class="input"><input type="file" name="arch"  /><input type="hidden" name="fname" value="<?php echo $fname; ?>"/></td>
							</tr>
							<tr>
								<td colspan="2" align="right" class="submitButton">
									<div class="submitButtonContainer">
										<div class="submitButton">
											<input type="submit" value="Generar Archivo" onmouseover="javascript:this.style.cursor='pointer';" />
										</div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="step3">
            			<input type="hidden" id="red" name="red" value="<?php echo isset($_POST['red'])? $_POST['red'] : 0; ?>" />
            			<input type="hidden" id="green" name="green" value="<?php echo isset($_POST['green'])? $_POST['green'] : 0; ?>" />
            			<input type="hidden" id="blue" name="blue" value="<?php echo isset($_POST['blue'])? $_POST['blue'] : 0; ?>" />
						<?php
							include_once("codigo.php");
						?>
					</td>
				</tr>
			</table>
			</form>			
	  	</td>
		<td style="width:200px;padding:0px;vertical-align:top;" valign="top" rowspan="3">
			<div class="helpButton">
				<a id="v_toggle" href="#"><span id="vertical_status">Ocultar Ayuda</span></a>
			</div>
			<div id="vertical_slide" class="help">
				<strong>Paso 1:</strong><br/>
				Seleccione la gama de color que desea aplicar a su archivo. Los colores existentes en dicho archivo serán ajustados a la nueva gama seleccionada.<br/>
				<strong>Paso 2:</strong><br/>
				Cargue el archivo donde se encuentran los colores que desee modificar.<br/>
				<strong>Paso 3:</strong><br/>
				Listo! Aquí podrá visualizar los colores originales del archivo junto a los nuevos colores modificados para ajustarse a la nueva gama de color seleccionada. Si está conforme, podrá descargarse el nuevo archivo con los colores modificados.<br/>
				Se puede checkear cualquiera de los colores originales para mantenerlos sin cambios utilizando el <span class="checkbox">&nbsp;</span> que tienen a la izquierda de los mismos.<br/><br/>
				<span class="disk">&nbsp;</span> Para descargar el nuevo archivo generado, puede optar entre dos opciones:<br/>
				<strong>a:</strong>&nbsp;Click derecho sobre el link de descarga, seleccionar "Guardar destino como..." y guarde el archivo con el mismo nombre y extensión que el original.<br/>
				<strong>b:</strong>&nbsp;Click izquierdo sobre el link de descarga para visualizar el archivo en la ventana. A continuación vaya a "Archivo" del menú de su navegador, seleccione "Guardar como..." y guarde el archivo con el mismo nombre y extensión que el original.<br/><br/>
				<strong>Nota:</strong><br/>
				Este script acepta cualquier archivo como entrada y busca los patrones "#RRGGBB;" y "#RGB;".<br/>
			</div>		
		</td>
	</tr>
</table>
<div class="footer">CSS Colorizer © Copyright 2007-2008 <a href="http://www.dokkogroup.com.ar" <?php echo 'target="_blank"'?> >DokkoGroup</a></div>
</body>
