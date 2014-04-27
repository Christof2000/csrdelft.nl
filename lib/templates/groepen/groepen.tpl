{SimpleHtml::getMelding()}
<ul class="horizontal">
	{foreach from=$groeptypes item=groeptype}
		<li{if $groeptype.id==$groepen->getId()} class="active"{/if}>
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		</li>
	{/foreach}
</ul>
<hr />
{if !$groepen->getToonHistorie()}
	<div id="groepLijst">
		<ul>
			{foreach from=$groepen->getGroepen() item=groep name=g}
				<li><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
				{/foreach}	
		</ul>
	</div>
{/if}
{if $action=='edit'}
	<h1>{$groepen->getNaam()}</h1>
	<form action="/actueel/groepen/{$groepen->getNaam()}/?bewerken=true" method="post">
		<div id="groepenFormulier" class="groepFormulier">
			<div id="bewerkPreviewContainer" class="previewContainer"><div id="bewerkPreview" class="preview"></div></div>
			<label for="beschrijving"><strong>Beschrijving:</strong><br /><br />UBB staat aan.</label>
			<textarea id="typeBeschrijving" name="beschrijving" style="width:444px;" rows="15">{$groepen->getBeschrijving()|escape:'html'}</textarea><br />
			<label for="submit"></label><input type="submit" id="submit" value="Opslaan" /> <input type="button" value="Voorbeeld" onclick="return previewPost('typeBeschrijving', 'bewerkPreview')" /> <a href="/actueel/groepen/{$groepen->getNaam()}/" class="knop">terug</a>
			<a style="float: right;" class="knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
			<a style="float: right; margin-right: 3px;" class="knop" onclick="vergrootTextarea('typeBeschrijving', 10)" title="Vergroot het invoerveld"><div class="arrows">&uarr;&darr;</div></a>
			<hr />
		</div>
	</form>
{else}
	{$groepen->getBeschrijving()|ubb}
{/if}
<div class="clear">
	{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe {$groepen->getNaamEnkelvoud()}</a>
	{/if}	
	{if $groepen->isAdmin()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/?maakOt=true" class="knop" 
		   onclick="return confirm('Weet u zeker dat alle h.t. groepen in deze categorie o.t. moeten worden?')">
			Maak h.t. groepen o.t.
		</a>
	{/if}
	{if LoginLid::mag('P_ADMIN') AND $action!='edit'}
		<a class="knop" href="/actueel/groepen/{$groepen->getNaam()}/?bewerken=true">
			<img src="{$CSR_PICS}/knopjes/bewerken.png" title="Bewerk beschrijving" />
		</a>
	{/if}
</div>

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep clear" id="groep{$groep->getId()}">
		<div class="groepleden">
			{if $groep->toonPasfotos()}
				{assign var='actie' value='pasfotos'}
			{/if}
			{include file='groepen/groepleden.tpl'}
		</div>
		<h2><a href="/actueel/groepen/{$groepen->getNaam()}/{$groep->getId()}/">{$groep->getNaam()}</a></h2>
		{if $groep->getType()->getId()==11 }Ouderejaars: {$groep->getEigenaar()|perm2string}<br /><br />{/if} {* alleen bij Sjaarsacties *}
			{$groep->getSbeschrijving()|ubb}
		</div>
		{/foreach}
			<hr class="clear" />
			{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
				<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe {$groepen->getNaamEnkelvoud()}</a>
			{/if}

