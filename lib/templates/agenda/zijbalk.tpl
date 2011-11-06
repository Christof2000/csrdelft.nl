<div id="zijbalk_agenda">
	<h1><a href="/actueel/agenda/" title="Agenda">Agenda</a></h1>
	{foreach from=$items item=item}
		{if $item instanceof Lid}
			{* geen verjaardagen hier. *}
		{else}
			<div class="item">
			{if date('d-m', $item->getBeginMoment())==date('d-m')}
				{$item->getBeginMoment()|date_format:"%H:%M"}
			{else}
				{$item->getBeginMoment()|date_format:"%d-%m"}
			{/if}
			<a href="/actueel/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/#dag-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}" title="{$item->getTitel()|ubb|strip_tags}">
				{$item->getTitel()|ubb|strip_tags|truncate:25:"…":true}
			</a>
		</div>
		{/if}
	{/foreach}
</div>
