{$view->getMelding()}

<form id="forum_zoeken" action="/forum/zoeken" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum" class="forumGrootlink">Forum</a>
	</div>
{/capture}

{$smarty.capture.navlinks}

<h1>{$view->getTitel()}</h1>

{if $resultaten}
	<table id="forumtabel">
		{foreach from=$resultaten item=draad}
			<thead>
				<tr>
					<th style="font-weight: normal;">
						{if LoginLid::instelling('forum_datumWeergave') === 'relatief'}
							{$draad->datum_tijd|reldate}
						{else}
							{$draad->datum_tijd}
						{/if}
					</th>
					<th>
						{if $draad->wacht_goedkeuring}
							<span title="Nieuw onderwerp in {$delen[$draad->forum_id]->titel}">
								<small style="font-weight: normal;">[{$delen[$draad->forum_id]->titel}]</small>
								{$draad->titel}
								{icon get="new"}
							</span>
						{else}
							<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}"{if !$draad->alGelezen()} style="{LidInstellingen::instance()->getTechnicalValue('forum', 'ongelezenWeergave')}"{/if}>
								<small style="font-weight: normal;">[{$delen[$draad->forum_id]->titel}]</small>
								{$draad->titel}
								{if $draad->gesloten}
									{icon get="slotje" title="Dit onderwerp is gesloten, u kunt niet meer reageren"}
								{elseif $draad->belangrijk}
									{icon get="belangrijk" title="Dit onderwerp is door het bestuur aangemerkt als belangrijk."}
								{/if}
							</a>
						{/if}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$draad->getForumPosts() item=post}
					{include file='MVC/forum/post_lijst.tpl'}
					<tr class="tussenschot">
						<td colspan="2"></td>
					</tr>
				{/foreach}
			</tbody>
		{/foreach}
		{if isset($query)}
			<thead>
				<tr>
					<th colspan="2">
						{sliding_pager baseurl="/forum/zoeken/"|cat:$query|cat:"/"
					pagecount=ForumDradenModel::instance()->getHuidigePagina() curpage=ForumDradenModel::instance()->getHuidigePagina()
					separator=" &nbsp;"}
						&nbsp;<a href="/forum/zoeken/{$query}/{ForumDradenModel::instance()->getAantalPaginas(0)}">verder zoeken</a>
					</th>
				</tr>
			</thead>
		{/if}
	</table>

	<h1>{$view->getTitel()}</h1>
	{$smarty.capture.navlinks}

{else}
	Geen resultaten.
{/if}