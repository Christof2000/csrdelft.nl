<tr class="forumdraad kleur{cycle values="0,1"}">
	<td  colspan="2" class="titel">
		{if $draad->wacht_goedkeuring}
			<small style="font-weight: normal;">[ter goedkeuring...]</small>
		{/if}
		<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'open_draad_op_pagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'open_draad_op_pagina') == 'laatste'}#reageren{/if}"{if !$draad->alGelezen()} style="{LidInstellingen::instance()->getTechnicalValue('forum', 'ongelezenWeergave')}"{/if}>
			{if $draad->gesloten}
				{icon get="slotje" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
			{elseif $draad->belangrijk}
				{icon get="belangrijk" title="Dit onderwerp is door het bestuur aangemerkt als belangrijk."}
			{elseif $draad->plakkerig}
				{icon get="plakkerig" title="Dit onderwerp is plakkerig, het blijft bovenaan."}
			{/if}
			{$draad->titel}
		</a>
		{sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/"
			pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=0
			txt_pre="&nbsp;[ " txt_post=" ]" link_current=true}
	</td>
	<td class="reacties">{$draad->aantal_posts}</td>
	<td class="reacties">{$draad->lid_id|csrnaam:'user'}</td>
	<td class="reactiemoment">
		{if LoginLid::instelling('forum_datumWeergave') === 'relatief'}
			{$draad->laatst_gewijzigd|reldate}
		{else}
			{$draad->laatst_gewijzigd}
		{/if}
		<br /><a href="/forum/reactie/{$draad->laatste_post_id}#{$draad->laatste_post_id}">bericht</a>
		door {$draad->laatste_lid_id|csrnaam:'user'}
	</td>
</tr>