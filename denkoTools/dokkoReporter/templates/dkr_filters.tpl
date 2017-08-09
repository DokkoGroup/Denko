
{if $dkr_filter_results > 0}
    <fieldset>
        <legend>{#filter_legend#}</legend>
		<form action="{$smarty.server.PHP_SELF}?id_report={$dkr_idReport}" method="post">
	        <div class="filters">
				{dkr_filters}
	                	<div><b>{$name|capitalize}: </b><input type="text" name="dkr_filter_{$key}" value="{$value}" /></div>
				{/dkr_filters}
				<div><input type="submit" class="button" value="{#filter_submit#}" /></div>
			</div>
		</form>
    </fieldset>
{/if}