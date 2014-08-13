{* 
 *	Boek weergeven
 *}

<div class="foutje">{SimpleHtml::getMelding()}</div>
<ul class="horizontal">
	<li>
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li>
		<a href="/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>
	</li>
	{if LoginModel::mag('P_BIEB_READ')}
		<li>
			<a href="/bibliotheek/rubrieken" title="Rubriekenoverzicht">Rubrieken</a>
		</li>
	{/if}
</ul>

{if $boek->magBekijken()}
	{* blok rechts met knopjes *}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
		{if $boek->getId()!=0}
			<br /><br /><br />
			{if $boek->magVerwijderen()}
				<a class="knop verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getId()}" title="Boek verwijderen" onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a><br />
			{/if}
			<a class="knop" href="/communicatie/bibliotheek/addexemplaar/{$boek->getId()}" title="Ik bezit dit boek ook" onclick="return confirm('U bezit zelf een exemplaar van dit boek? Door het toevoegen aan de catalogus geef je aan dat anderen dit boek kunnen lenen.')">{icon get="user_add"} Ik bezit dit boek</a>
			{if $boek->isBASFCie()}
				<a class="knop" href="/communicatie/bibliotheek/addexemplaar/{$boek->getId()}/x222" title="C.S.R.-bieb bezit dit boek ook" onclick="return confirm('Bezit de C.S.R.-bieb een exemplaar van dit boek?')">{icon get="user_add"} Is een biebboek</a>
			{/if}
		{/if}
	</div>
{/if}



{* nieuw boek formulier *}
{if $boek->getId()==0}
	<h1>Nieuw boek toevoegen</h1>
	<p>Zoek via het Google Books-zoekveld je boek en kies een van de suggesties om de boekgegevens hieronder in te vullen.</p>
	<div class="boekzoeker" title="Geef titel, auteur, isbn of een ander kenmerk van het boek. Minstens 7 tekens, na 1 seconde verschijnen suggesties.">
		<label for="boekzoeker"><img src="{$CSR_PICS}/knopjes/google.ico" />Google Books:</label><input type="text" placeholder="Zoek en kies een suggestie om de velden te vullen" id="boekzoeker">
	</div>

	{$boek->getFormulier()->view()}


{else}
	{* weergave bestaand boek, soms met bewerkbare velden *}
	<div class="boek" id="{$boek->getId()}">

		{if $boek->isEigenaar()}
			
			<div class="blok header boekgegevens">
				{$boek->ajaxformuliervelden->findByName('titel')->view()}
			</div>
			<div class="blok gegevens boekgegevens">
				{assign var='fields' value=','|explode:"auteur,paginas,taal,isbn,uitgeverij,uitgavejaar"}
				{foreach from=$fields item=field}
					{$boek->ajaxformuliervelden->findByName($field)->view()}
				{/foreach}
			</div>
			<div class="blok gegevens boekgegevens">
				{$boek->ajaxformuliervelden->findByName('rubriek')->view()}
				{if $boek->isBiebboek()}
					{if $boek->isBASFCie()}
						{$boek->ajaxformuliervelden->findByName('code')->view()}
					{else}
						<div class="regel"><label>Code</label>{$boek->getCode()}</div>
					{/if}
				{/if}
			</div>

		{else}

			<div class="blok header boekgegevens">
				<div class="regel"><label>Boek</label><span>{$boek->getTitel()}</span></div>
			</div>
			<div class="blok gegevens boekgegevens">
				{if $boek->getAuteur()!=''}<div class="regel"><label>Auteur</label>{$boek->getAuteur()}</div>{/if}
				{if $boek->getPaginas()!=0}<div class="regel"><label>Pagina's</label>{$boek->getPaginas()}</div>{/if}
				{if $boek->getTaal()!=''}<div class="regel"><label>Taal</label>{$boek->getTaal()}</div>{/if}
				{if $boek->getISBN()!=''}<div class="regel"><label>ISBN</label>{$boek->getISBN()}</div>{/if}
				{if $boek->getUitgeverij()!=''}<div class="regel"><label>Uitgeverij</label>{$boek->getUitgeverij()}</div>{/if}
				{if $boek->getUitgavejaar()!=0}<div class="regel"><label>Uitgavejaar</label>{$boek->getUitgavejaar()}</div>{/if}
			</div>
			<div class="blok gegevens boekgegevens">
				<div class="regel"><label>Rubriek</label>{$boek->getRubriek()}</div>
				{if $boek->getCode()!='' AND $boek->isBiebboek()}<div class="regel"><label>Code</label>{$boek->getCode()}</div>{/if}
			</div>
		{/if}

		<div style="clear: left;"></div>



		{* Exemplaren *}

		{assign var=total_exemplaren_bibliotheek value=0} {* teller nodig om in compacte weergave slechts 1 biebboek te laten zien. *}
		<div class="blok gegevens exemplaren" id="exemplaren">
			<h2>Exemplaren</h2>
			{foreach from=$boek->getExemplaren() item=exemplaar name=exemplaren}
				<div class="exemplaar uitgebreid" {if $smarty.foreach.exemplaren.total>4 AND !$boek->isEigenaar($exemplaar.id) AND ($exemplaar.eigenaar_uid!='x222' OR $total_exemplaren_bibliotheek>0 )}style="display: none;"{/if}>
					<div class="fotolabel">{$exemplaar.eigenaar_uid|pasfoto}</div>
					<div class="gegevensexemplaar" id="ex{$exemplaar.id}">
					{* eigenaar *}
						<div class="regel">
							<label>Eigenaar</label>
							{if $exemplaar.eigenaar_uid=='x222'}{assign var=total_exemplaren_bibliotheek value=$total_exemplaren_bibliotheek+1}
								C.S.R.-bibliotheek
							{else}
								{$exemplaar.eigenaar_uid|csrnaam:'civitas':'visitekaartje'}
							{/if}
						</div>
					{* opmerking *}
						{if $boek->isEigenaar($exemplaar.id)}
							{$boek->ajaxformuliervelden->findByName("opmerking_`$exemplaar.id`")->view()}
						{else}
							{if $exemplaar.opmerking != ''}
								<div class="regel">
									<label>Opmerking</label><span class="opmerking">{$exemplaar.opmerking|escape:'html'}</span>
								</div>
							{/if}
						{/if}
					{* status *}
						<div class="regel">
							<label>Status</label>
							{if $exemplaar.status=='uitgeleend'}
								<span title="Sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Uitgeleend aan {$exemplaar.uitgeleend_uid|csrnaam:'civitas':'visitekaartje'}</span>
							{elseif $exemplaar.status=='teruggegeven'}
								<span title="Was uitgeleend sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Teruggegeven door {$exemplaar.uitgeleend_uid|csrnaam:'civitas':'visitekaartje'}</span>
							{elseif $exemplaar.status=='vermist'}
								<span class="waarschuwing" title="Sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Vermist</span>
							{elseif $exemplaar.status=='beschikbaar' }
								Beschikbaar
							{/if}
						</div>
						{if $exemplaar.status=='beschikbaar' AND $boek->isEigenaar($exemplaar.id)}
							{$boek->ajaxformuliervelden->findByName("lener_`$exemplaar.id`")->view()}
						{/if}
					{* actieknoppen *}
						<div class="regel actieknoppen">
							<label>&nbsp;</label>
							<div>
								{if $exemplaar.status=='beschikbaar'}
									{if $exemplaar.eigenaar_uid=='x222'} {* bibliothecaris werkt met kaartjes *}
										{if !$boek->isEigenaar($exemplaar.id)} {* basfcie hoeft opmerking niet te zien *}
											<span class="suggestie" style="font-style: normal;">Biebboek lenen: laat het kaartje achter voor de bibliothecaris.</span><br />
										{/if}
									{else}
										<a class="knop" href="/communicatie/bibliotheek/exemplaarlenen/{$boek->getId()}/{$exemplaar.id}" title="Leen dit boek" onclick="return confirm('U wilt dit boek van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} lenen?')">{icon get="lorry"} Exemplaar lenen</a>
									{/if}
								{elseif $exemplaar.status=='uitgeleend' AND LoginModel::getUid()==$exemplaar.uitgeleend_uid AND $exemplaar.uitgeleend_uid!=$exemplaar.eigenaar_uid}
									<a class="knop" href="/communicatie/bibliotheek/exemplaarteruggegeven/{$boek->getId()}/{$exemplaar.id}" title="Boek heb ik teruggegeven" onclick="return confirm('U heeft dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} teruggegeven?')">{icon get="lorry_go"} Teruggegeven</a>
								{/if}
								{if $boek->isEigenaar($exemplaar.id)}
									{if ($exemplaar.status=='uitgeleend' OR $exemplaar.status=='teruggegeven')}
										<a class="knop" href="/communicatie/bibliotheek/exemplaarterugontvangen/{$boek->getId()}/{$exemplaar.id}" title="Boek is ontvangen" onclick="return confirm('Dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} is terugontvangen?')">{icon get="lorry_flatbed"} Ontvangen</a>
									{elseif $exemplaar.status=='beschikbaar'}
										<a class="knop" href="/communicatie/bibliotheek/exemplaarvermist/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar is vermist" onclick="return confirm('Is het exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} vermist?')">{icon get="emoticon_unhappy"} Vermist</a>
									{elseif $exemplaar.status=='vermist'}
										<a class="knop" href="/communicatie/bibliotheek/exemplaargevonden/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar teruggevonden" onclick="return confirm('Is het exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} teruggevonden?')">{icon get="emoticon_smile"} Teruggevonden</a>
									{/if}
									<a class="knop" href="/communicatie/bibliotheek/verwijderexemplaar/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar verwijderen" onclick="return confirm('Weet u zeker dat u dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a>
								{/if}
							</div>
						</div>
					</div>
				</div>
			{foreachelse}
				<p>Geen exemplaren.</p>
			{/foreach}

			{* compacte weergave met alleen foto's *}
			{assign var=total_exemplaren_bibliotheek value=0} {* teller nodig om in compacte weergave slechts 1 biebboek te laten zien. *}
			{if $smarty.foreach.exemplaren.total>4}
				<div class="exemplaar compact">
					{foreach from=$boek->getExemplaren() item=exemplaar}
						{if !$boek->isEigenaar($exemplaar.id) AND ($exemplaar.eigenaar_uid!='x222' OR $total_exemplaren_bibliotheek>0 )}
							{$exemplaar.eigenaar_uid|pasfoto} 
							<div class="statusmarkering"><span class="biebindicator {$exemplaar.status}" title="Boek is {$exemplaar.status}">• </span></div>
						{/if}
						{if $exemplaar.eigenaar_uid=='x222'}
							{assign var=total_exemplaren_bibliotheek value=$total_exemplaren_bibliotheek+1}
						{/if}
					{/foreach}
					<br /><div style="clear: both;"></div>
					<label>&nbsp;</label><a onclick="jQuery(this).parent().parent().children('div.exemplaar.uitgebreid').show(); jQuery(this).parent().remove();">» meer informatie</a>
				</div>
			{/if}
		</div>
	</div>
	{if $boek->isEigenaar()}
		{* javascript invoegen van de fields *}
		{$boek->ajaxformuliervelden->getScriptTag()}
	{/if}

	{* beschrijvingen *}

	<div class="beschrijvingen">
		<h2 class="header">Recensies en beschrijvingen</h2>
		{if $boek->countBeschrijvingen()>0}
			<table id="beschrijvingentabel">
			{foreach from=$boek->getBeschrijvingen() item=beschr}
				{assign var=beschrijving value=$beschr->getBeschrijving()}
				<tr><td class="linkerkolom"></td><td style="width:506px"></td></tr>
				<tr >
					{if isset($beschrijving.bewerk)}
						<td colspan="2">
							{* formulier voor toevoegen/bewerken van beschrijvingen *}
							{$boek->getFormulier()->view()}
						</td>
					{else}
						<td class="linkerkolom recensist">
							<span class="recensist">{$beschrijving.schrijver_uid|csrnaam:'user':'visitekaartje'}</span><br />
							<span class="moment">{$beschrijving.toegevoegd|reldate}</span><br />

						{* knopjes bij elke post *}	
							{if $boek->magBeschrijvingVerwijderen($beschrijving.id)}
								{knop url="/communicatie/bibliotheek/bewerkbeschrijving/`$boek->getId()`/`$beschrijving.id`#Beschrijvingsformulier" type=bewerken}
								{knop url="/communicatie/bibliotheek/verwijderbeschrijving/`$boek->getId()`/`$beschrijving.id`" type=verwijderen confirm='Weet u zeker dat u deze beschrijving wilt verwijderen?'}
							{/if}
						</td>
						<td class="beschrijving b{cycle values="0,1"}" id="beschrijving{$beschrijving.id}">
							{$beschrijving.beschrijving|ubb}
							{if $beschrijving.bewerkdatum!='0000-00-00 00:00:00'}
								<br /><div class="offtopic">Bewerkt {$beschrijving.bewerkdatum|reldate}</div>
							{/if}
						</td>
					{/if}
				</tr>
				<tr>
					<td class="linkerkolom"></td><td class="tussenschot"></td>
				</tr>
			{/foreach}
			</table>
		{else}
			<p class="header">Nog geen beschrijvingen.</p>
		{/if}

	</div>
{/if}
