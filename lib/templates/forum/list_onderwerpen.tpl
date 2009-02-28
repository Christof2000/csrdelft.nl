<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a>
		<h1>{$categorie->getNaam()}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

<table id="forumtabel">
	<tr>
		<th>Titel</th>
		<th>Reacties</th>
		<th>Auteur</th>
		<th>verandering</th>
	</tr>
	{if !is_array($categorie->getOnderwerpen())}
		<tr>
			<td colspan="3">Deze categorie bevat nog geen berichten of deze categorie bestaat niet.</td>
		</tr>
	{else}
		{foreach from=$categorie->getOnderwerpen() item=onderwerp}
			<tr class="kleur{cycle values="0,1"}">
				<td class="titel">
					{if $onderwerp->getZichtbaarheid()=='wacht_goedkeuring'}[ter goedkeuring...]{/if}
					<a href="/communicatie/forum/onderwerp/{$onderwerp->getID()}">
						{if $onderwerp->isPlakkerig()}
							<img src="{$csr_pics}forum/plakkerig.gif" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;
						{/if}	
						{if !$onderwerp->isOpen()}
							<img src="{$csr_pics}forum/slotje.png" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;
						{/if}
						{$onderwerp->getTitel()|wordwrap:60:"\n":true|escape:'html'}
					</a>
				</td>
				<td class="reacties">{$onderwerp->getReacties()}</td>
				<td class="reacties">{$onderwerp->getUid()|csrnaam:'user'}</td>
				<td class="reactiemoment">
					{$onderwerp->getLastpost()|reldate}<br />
					<a href="/communicatie/forum/onderwerp/{$bericht.id}#post{$bericht.lastpostID}">bericht</a> door 
					{$onderwerp->getLastuser()|csrnaam:'user'}
				</td>
			</tr>
		{/foreach}
	{/if}
	<tr>
		<th colspan="2">&nbsp;</th>
		<th colspan="2">
			{sliding_pager baseurl="/communicatie/forum/categorie/`$categorie->getID()`/" 
				pagecount=$categorie->getPaginaCount() curpage=$categorie->getPagina()
				txt_first="&laquo;" txt_prev="&lt;" separator=" " txt_next="&gt;" txt_last="&raquo;"}
		</th>
	</tr>
	{if $categorie->magPosten()}
		<tr>
			<td colspan="4" class="tekst">
				<form method="post" action="/communicatie/forum/onderwerp-toevoegen/{$categorie->getID()}">
					<p>
						{if $lid->hasPermission('P_LOGGED_IN')}
							{if $lid->hasPermission('P_FORUM_MOD')}
								<a href="/communicatie/forum/maak-stemming/{$categorie}" class="knop" style="float: right; margin: 8px;">Peiling toevoegen</a>
							{/if}
							Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het 
							onderwerp waarover u post hier wel thuishoort.<br /><br />
						{else}
							{*	melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat 
								wil zeggen, de topics zijn nog niet direct zichtbaar. *}
							Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
							&eacute;&eacute;rst door de PubCie worden goedgekeurd. <br />
							<span style="text-decoration: underline;">Het is hierbij verplicht om uw naam en een email-adres 
							onder het bericht te plaatsen. Dan kan de PubCie eventueel contact met u opnemen. Doet u dat niet, 
							dan wordt uw bericht waarschijnlijk niet geplaatst!</span><br /><br /><br />
						{/if}
						<a class="forumpostlink" name="laatste"><strong>Titel</strong></a><br />
						<input type="text" name="titel" value="" class="tekst" style="width: 100%" tabindex="1" /><br />
						<strong>Bericht</strong>&nbsp;&nbsp;
						<a onclick="vergrootTextarea('forumBericht', 10);" title="Vergroot het invoerveld" class="handje">Invoerveld vergroten</a><br />
						
						<div id="berichtPreviewContainer" class="previewContainer"><h3>Voorbeeld van uw bericht:</h3><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" rows="10" cols="80" style="width: 100%" class="tekst" tabindex="2"></textarea><br />
						<input type="submit" name="submit" value="verzenden" /> <input type="button" value="voorbeeld" style="color: #777;" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
					</p>
				</form>
			</td>
		</tr>
	{/if}
</table>
{$smarty.capture.navlinks}