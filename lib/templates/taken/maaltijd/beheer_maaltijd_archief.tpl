{*
	beheer_maaltijd_archief.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<tr id="maaltijd-row-{$maaltijd->getMaaltijdId()}">
	<td>{$maaltijd->getDatum()|date_format:"%d-%m-%Y"} {$maaltijd->getTijd()|date_format:"%H:%M"}</td>
	<td>{$maaltijd->getTitel()}</td>
	<td>&euro; {$maaltijd->getPrijs()|string_format:"%.2f"}</td>
	<td>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()}" style="display: inline-block;">
			<a title="Toon aanmeldingen" class="knop" onclick="$('div.aanmeldingen-{$maaltijd->getMaaltijdId()}').toggle();">{icon get="eye"} Toon <strong>{$maaltijd->getAantalAanmeldingen()}</strong></a>
		</div>
		<div class="aanmeldingen-{$maaltijd->getMaaltijdId()}" style="display: none;">
			<a title="Toon aanmeldingen" class="knop" onclick="$('div.aanmeldingen-{$maaltijd->getMaaltijdId()}').toggle();">{icon get="eye"} Verberg <strong>{$maaltijd->getAantalAanmeldingen()}</strong></a>
		{foreach from=$maaltijd->getAanmeldingenArray() item=aanmelding}
			<li>
				{if $aanmelding[0] === 'gast'}Gast van
				{else}{$this->getLidLink($aanmelding[0])}
				{/if}
				{if $aanmelding[1] === 'abo'} (abo)
				{elseif $aanmelding[0] !== $aanmelding[1]}
					{if $aanmelding[0] !== 'gast'} door
					{/if}
					&nbsp;{$this->getLidLink($aanmelding[1])}
				{/if}
			</li>
		{/foreach}
		</div>
	</td>
</tr>
{/strip}