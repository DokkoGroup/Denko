<html>
    <head>
        <title>DokkoLogger{if $title} - {$title}{/if}</title>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        {dk_include file="styles/main.css"}
    </head>
    <body>

		{dk_include file="styles/logreport.css"}
		{dk_include file="js/dk.common.js"}
		{dk_include file="js/ajax.js"}
		{dk_include file="js/listarEquipos.js"}
		{dk_include file="js/overlib/overlib.js"}
		
		<table class="filtros" align="right" width="300px" cellpadding="4" cellspacing="0" border="0">
			<tr>
			    <td align="right">Actualizar:</td>	    
			    <td align="left" width="130px">{include file="autorefresh.tpl"}</td>
		    </tr>
		</table>
		
		<table class="listado" align="center" width="600px" cellpadding="4" cellspacing="0" border="0">	
			<tr class="header">
				<td style="text-align:left;">Tipo de Log</td>
				<td style="text-align:left;">Descripción</td>
				<td>Datos</td>
				<td>Fecha</td>
				<td>Indice 1</td>
				<td>Indice 2</td>
				<td>Indice 3</td>
			</tr>
			{assign var="cont" value="0"}
			{dk_daolister table="log" name="log" resultsPerPage="30"}
			    {dk_lister export="id_tipolog,descripcion,datos,fecha,indice1,indice2,indice3"}
			    {dkc_callback function="getTipoLog" var="daoTipoLog"}
			    <tr class="content">
			        <td style="text-align:left;">{$daoTipoLog->nombre}</td>
			        <td style="text-align:left;">{$descripcion|default:"-"}</td>
			        <td><img src="./images/mas_info.png" onmouseover="javascript:overlib('{$datos|dokkoMonitor_replace|default:"-"}',FGCOLOR,'#FFFFFF',WIDTH,'330');" onmouseout="javascript:return nd();" /></td>
			        <td width="20%">{$fecha}</td>
			        <td align="center">{$indice1|default:"-"}</td>
			        <td align="center">{$indice2|default:"-"}</td>
			        <td align="center">{$indice3|default:"-"}</td>
			    </tr>
			    {assign var="cont" value=$cont+1}
			    {/dk_lister}
			    {if $cont != 0}
			    <tr>
			        <td colspan="8">
			            <br />
			            {include file="paginador.tpl"}
			        </td>
			    </tr>
			    {/if}
			{/dk_daolister}
		</table>


        <div class="footer" align="center">
        	&copy;&nbsp;Copyright 2007 DokkoLogger
        	<br>
        	Versión 0.1
        </div>
    </body>
</html>