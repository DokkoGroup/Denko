{assign var="anterior"  value=$dkr_pageNumber-1}
{assign var="siguiente" value=$dkr_pageNumber+1}

{assign var="buttonsTolerance" value=5}
{assign var="ini" value=$dkr_pageNumber-$buttonsTolerance}
{assign var="top" value=$dkr_pageNumber+$buttonsTolerance}
{if $ini < 1}
	{assign var="ini" value=1}
{/if}
{if $top > $dkr_totalPages}
	{assign var="top" value=$dkr_totalPages}
{/if}

<div class="botonera">
	<center>
	    <b>P&aacute;ginas:</b>
	    &nbsp;
		{if $dkr_pageNumber > 1}
			<a href="{$smarty.server.PHP_SELF}?id_report={$dkr_idReport}&dkr_page={$anterior}">&laquo; ant</a>
		{else}
			<span class="disabled">&laquo; ant</span>
		{/if}
		{section name="pages" start=$ini loop=$top+1}
			{if $smarty.section.pages.index == $dkr_pageNumber}
				<span class="actual">{$dkr_pageNumber}</span>
			{else}
				<a href="{$smarty.server.PHP_SELF}?id_report={$dkr_idReport}&dkr_page={$smarty.section.pages.index}">{$smarty.section.pages.index}</a>
			{/if}
		{/section}
		{if $dkr_pageNumber < $dkr_totalPages}
			<a href="{$smarty.server.PHP_SELF}?id_report={$dkr_idReport}&dkr_page={$siguiente}">sig &raquo;</a>
		{else}
			<span class="disabled">sig &raquo;</span>
		{/if}
	</center>
</div>