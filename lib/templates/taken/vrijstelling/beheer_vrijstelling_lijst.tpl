{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->getLidId()}">
	<td>
		<a href="{$GLOBALS.taken_module}/bewerk/{$vrijstelling->getLidId()}" title="Vrijstelling wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</td>
	<td>{$vrijstelling->getLid()->getNaamLink($GLOBALS.corvee.weergave_ledennamen_beheer, $GLOBALS.corvee.weergave_ledennamen)}</td>
	<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getPercentage()}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{$GLOBALS.taken_module}/verwijder/{$vrijstelling->getLidId()}" title="Vrijstelling definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
	</td>
</tr>