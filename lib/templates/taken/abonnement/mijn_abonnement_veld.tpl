{*
	mijn_abonnement_veld.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<td id="abonnement-cell-{$mrid}" {if isset($uid)}class="abonnement-ingeschakeld">
	<a href="{Instellingen::instance()->('taken', 'url')}/uitschakelen/{$mrid}" class="knop post abonnement-ingeschakeld"><input type="checkbox" checked="checked" /> Aan</a>
{else}class="abonnement-uitgeschakeld">
	<a href="{Instellingen::instance()->('taken', 'url')}/inschakelen/{$mrid}" class="knop post abonnement-uitgeschakeld"><input type="checkbox" /> Uit</a>	
{/if}
</td>