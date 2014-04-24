<li id="item-{$item->item_id}" parentid="items-{$item->getBeginMoment()|date_format:"%Y-%m-%d"}">
	{if $magBeheren}
		<a href="/agenda/verwijderen/{$item->item_id}" class="beheren post confirm" title="Dit agenda-item definitief verwijderen">
			{icon get="verwijderen"}
		</a>
		<a href="/agenda/bewerken/{$item->item_id}" class="beheren post popup" title="Dit agenda-item bewerken">
			{icon get="bewerken"}
		</a>
	{/if}
	{if !$item->isHeledag()}
		<div class="tijd">
			{$item->getBeginMoment()|date_format:"%R"}-{$item->getEindMoment()|date_format:"%R"}
		</div>
	{/if}
	<div class="hoverIntent">
		{if $item->getLink()}
			<a href="{$item->getLink()}" title="{$item->getBeschrijving()}">{$item->getTitel()}</a>
		{else}
			<span title="{$item->getBeschrijving()}">{$item->getTitel()}</span>
		{/if}
		{if $item->getLocatie()}
			{icon get=map title=Kaart}
			<div class="hoverIntentContent">
				{"[kaart]"|cat:$item->getLocatie()|cat:"[/kaart]"|ubb}
			</div>
		{/if}
	</div>
</li>