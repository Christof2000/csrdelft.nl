<ul class="horizontal nobullets">
	<li>
		<a href="/actueel/maaltijden/" title="Maaltijdketzer">Maaltijdketzer</a>
	</li>
	<li>
		<a href="/actueel/maaltijden/voorkeuren/" title="Instellingen">Instellingen</a>
	</li>
	<li>
		<a href="/actueel/maaltijden/corveepunten/" title="Corveepunten">Corveepunten</a>
	</li>
	{if $loginlid->hasPermission('P_MAAL_MOD')}
		<li class="active">
			<a href="/actueel/maaltijden/corveebeheer/" title="Corveebeheer">Corveebeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/beheer/" title="Beheer">Maaltijdbeheer</a>
		</li>
		<li>
			<a href="/actueel/maaltijden/saldi.php" title="Saldo's updaten">Saldo's updaten</a>
		</li>
	{/if}
</ul>
<hr />
<h1>Corveebeheer</h1>
{if $maal.zelf.error!=''}<span class="waarschuwing">N.B.: {$maal.zelf.error|escape:'htmlall'}</span><br /><br />{/if}
{if $maal.maaltijden|@count==0}
	<p>&#8226; Helaas, er is binnenkort geen maaltijd op Confide.</p>
{else}
	<table class="maaltijden">
		<tr>
			<th>&nbsp;</th>
			<th>Maaltijd begint om:</th>
			<th>Omschrijving</th>						
			<th>K</th>
			<th>A</th>
			<th>T</th>
			<th>sF</th>
			<th>sA</th>
			<th>sK</th>
			<th>Punten</th>
			<th># (Max)</th>
			<th>&nbsp;</th>
		</tr>
		{foreach from=$maal.maaltijden item=maaltijd}
			<tr {if $maaltijd.datum<=$smarty.now}class="old"{/if} style="background-color: {cycle values="#e9e9e9, #fff"};{if $maal.formulier.id==$maaltijd.id}background-color: #bfb{/if}">
			<td>
					<a href="/actueel/maaltijden/corveebeheer/bewerk/{$maaltijd.id}#corveemaaltijdFormulier"><img src="{$csr_pics}knopjes/bewerken.png" /></a>					
					<a href="/actueel/maaltijden/corveebeheer/takenbewerk/{$maaltijd.id}#corveetakenFormulier"><img src="{$csr_pics}knopjes/lijstbewerken.png" /></a>
				</td>
				<td>{$maaltijd.datum|date_format:$datumFormaat}</td>
				<td>{$maaltijd.tekst|escape:'html'}</td>
				{if $maaltijd.type == "normaal"}
					<td {if $maaltijd.koks - $maaltijd.koks_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.koks_aangemeld}/{$maaltijd.koks}
					</td>				
					<td {if $maaltijd.afwassers - $maaltijd.afwassers_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.afwassers_aangemeld}/{$maaltijd.afwassers}
					</td>	
					<td {if $maaltijd.theedoeken - $maaltijd.theedoeken_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.theedoeken_aangemeld}/{$maaltijd.theedoeken}
					</td>	
					<td />
					<td />
					<td />
					<td {if $maaltijd.is_toegekend}style="color: #0D0;"{/if}>({$maaltijd.punten_kok}/{$maaltijd.punten_afwas}/{$maaltijd.punten_theedoek})</td>
					<td>
						{$maaltijd.aantal} ({$maaltijd.max})
					</td>
				{else} {* Corveemaaltijd *}	
					<td />
					<td />
					<td />
					<td {if $maaltijd.schoonmaken_frituur - $maaltijd.frituur_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.frituur_aangemeld}/{$maaltijd.schoonmaken_frituur}
					</td>				
					<td {if $maaltijd.schoonmaken_afzuigkap - $maaltijd.afzuigkap_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.afzuigkap_aangemeld}/{$maaltijd.schoonmaken_afzuigkap}
					</td>	
					<td {if $maaltijd.schoonmaken_keuken - $maaltijd.keuken_aangemeld > 0}style="color: red;"{/if}>
						{$maaltijd.keuken_aangemeld}/{$maaltijd.schoonmaken_keuken}
					</td>
					<td {if $maaltijd.is_toegekend}style="color: #0D0;"{/if}>
						({$maaltijd.punten_schoonmaken_frituur}/{$maaltijd.punten_schoonmaken_afzuigkap}/{$maaltijd.punten_schoonmaken_keuken})
					</td>
					<td />
				{/if}
				<td>
					{if $maaltijd.corvee_gemaild == "1"}
						<img src="{$csr_pics}knopjes/email_open.png" alt="Gemaild" />
					{else}
						<img src="{$csr_pics}knopjes/email.png" alt="Niet gemaild" />
					{/if}
				</td>
			</tr>
		{/foreach}
	</table>
	<br />
{/if}
{if $maal.formulier.actie == "bewerk" || $maal.formulier.actie == "toevoegen"}
	{* maaltijd bewerken *}
	{include file='maaltijdketzer/corveeformulier.tpl'}
{elseif $maal.formulier.actie == "takenbewerk"}
	{* corvee-aanmeldingen bewerken *}
	{include file='maaltijdketzer/corveetakenformulier.tpl'}
{/if}