<h1><a href="{MededelingenContent::mededelingenRoot}">Mededelingen</a></h1>
{foreach from=$mededelingen item=mededeling}
	<div class="item">
		{$mededeling->getDatum()|date_format:"%d-%m"}
		<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}"
			title="[{$mededeling->getTitel()|escape:'html'}] {$mededeling->getTekstVoorZijbalk()|escape:'html'}">{$mededeling->getTitelVoorZijbalk()|escape:'html'}</a>
	</div>
{/foreach}