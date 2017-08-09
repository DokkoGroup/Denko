<?php
global $fname;

if(empty($_POST['col']) || $fname==''){
	echo "&nbsp;";
}else{
	$arr=explode('#',$_SESSION['archivo_'.$fname]);
	
	$colores=array();
	$duplicados=array();
	
	foreach($arr as $col){
		$auxC=explode(';',substr($col,0,7));
		if(!isset($duplicados[$auxC[0]]) && isset($auxC[1]) && (strlen($auxC[0])==3 || strlen($auxC[0])==6)){ 
			$colores[]=$auxC[0];
		$duplicados[$auxC[0]]=1;
		}
	}
	$nuevos=colorizeArray($colores,$_POST['col']);
	$nombreRes='result_'.$fname.'_'.$_POST['col'];
	$texto=$_SESSION['archivo_'.$fname];
	
	echo '<table cellpadding="0" cellspacing="0" border="0" class="step3Table"><tr><td style="text-align:center;padding:5px;" align="center">';
	echo '<a class="download" href="'.$_SERVER['PHP_SELF'].'?file='.$nombreRes.'">Descargar&nbsp;'.$fname.'</a>';
	echo '<table class="colorsList" cellspacing="2" cellpadding="0" border="0">';
	echo '<tr style="color:#666666;background-color:#E0E0E0;"><td align="center">Colores Originales</td><td align="center">Nuevos Colores</td></tr>';
	for($i=0; isset($colores[$i]); $i++){
		$brillo=brillo(desarmarColor($colores[$i]));
					if($brillo>128) $txcol='#000';
					else $txcol='#fff';
				
					$nameCheck='col_'.$colores[$i];
					
					$checked=' ';
					if(!empty($_POST[$nameCheck])){
						$checked='checked';
					$nuevos[$i]=$colores[$i];
					}
				
					echo '<tr>
					 <td align="left" valign="middle">
							 <div style="color:'.$txcol.';width:110px;height:25px;background-color:#'.$colores[$i].';padding-left:40px;line-height:25px;">
							 <input type=checkbox value="hola" name="'.$nameCheck.'" '.$checked.' />#'.$colores[$i].'
						 </div>
						 </td>
					 <td align="left" valign="middle">
						  <div style="color:'.$txcol.';width:100px;height:25px;background-color:#'.$nuevos[$i].';padding-left:50px;text-transform:uppercase;line-height:25px;">#'.$nuevos[$i].'</div>
						 </td>
					 </tr>';
				   $texto=str_replace('#'.$colores[$i].';','#'.$nuevos[$i].';',$texto);
				}
				echo "</table>";
				echo '<div class="remember">Se puede checkear cualquiera de los colores originales para mantenerlos sin cambios utilizando el <span class="checkbox">&nbsp;</span> que tienen a la izquierda de los mismos.</div><a class="download" href="'.$_SERVER['PHP_SELF'].'?file='.$nombreRes.'">Descargar&nbsp;'.$fname.'</a>';
				echo '</td><td align=center style="border:solid 1px #E0E0E0;padding:5px;;background-color:#'.$_POST['col'].';vertical-align:top;width:80px;">
				<div style="padding:2px;background-color:#fff;font:bold 12px Arial, Helvetica, sans-serif;">Gama de colores<br>#<span style="text-transform:uppercase;">'.$_POST['col'].'</span></div>
				</td></table>';
				
				$_SESSION[$nombreRes]=$texto;
			}
			
