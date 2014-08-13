<div id="menu" onmouseover="ResetTimer()" onmouseout="StartTimer()">
	<div id="menuleft"><a href="/"><div id="beeldmerk"></div></a></div>
	<div id="menucenter">
		<div id="menubanners">
			{assign var=active value=false}
			{foreach from=$root->children item=item name=banner}
				<div id="banner{$smarty.foreach.banner.iteration}" class="menubanner"{if !$active AND $item->active} style="display: block;"{assign var=active value=true}{/if}></div>
			{/foreach}
		</div>
		<ul id="mainmenu">
			{assign var=active value=false}
			{foreach from=$root->children item=item name=main}
				<li>
					<a href="{$item->link}" id="top{$smarty.foreach.main.iteration}" onmouseover="StartShowMenu('{$smarty.foreach.main.iteration}');" onmouseout="ResetShowMenu();"{if !$active AND $item->active} class="active"{/if} title="{$item->tekst}">{$item->tekst}</a>
				</li>
				{if !$active AND $item->active} 
					{assign var=active value=true}
					<script language="javascript" type="text/javascript">
						$(document).ready(function() {
							SetActive({$smarty.foreach.main.iteration});
						});
					</script>
				{/if}
			{/foreach}
		</ul>
	</div>
	<div id="menuright">
		{if LoginModel::mag('P_LOGGED_IN') }
			<div id="ingelogd">
				<a href="/instellingen/" class="instellingen" title="Webstekinstellingen">{icon get="instellingen"}</a>
				{if LoginModel::instance()->isSued()}
					<a href="/endsu/" style="color: red;">{LoginModel::instance()->getSuedFrom()->getNaamLink('civitas', 'plain')} als</a><br />»
				{/if}
				{LoginModel::getUid()|csrnaam}<br />
				<div id="uitloggen"><a href="/logout">log&nbsp;uit</a></div>
				<div id="saldi">
					{foreach from=LoginModel::instance()->getLid()->getSaldi() item=saldo}
						<div class="saldoregel">
							<div class="saldo{if $saldo.saldo < 0 AND LoginModel::getUid()!='0524'} staatrood{/if}">&euro; {$saldo.saldo|number_format:2:",":"."}</div>
							{$saldo.naam}:
						</div>
					{/foreach}
				</div>
				{if LoginModel::mag('P_LEDEN_MOD')}
					<div id="adminding">
						Beheer
						{if LoginModel::mag('P_ADMIN')}
							{if $forumcount > 0 OR $queues.meded->count()>0}
								({$forumcount}/{$queues.meded->count()})
							{/if}
						{/if}
						<div>
							{if LoginModel::mag('P_ADMIN')}
								<span class="queues">
									<a href="/forum/wacht">Forum: <span class="count">{$forumcount}</span><br /></a>
										{foreach from=$queues item=queue key=name}
										<a href="/tools/query.php?id={$queue->getID()}">
											{$name|ucfirst}: <span class="count">{$queue->count()}</span><br />
										</a>
									{/foreach}
								</span>
								<a href="/su/x101">&raquo; SU Jan Lid.</a><br />
							{/if}
							<a href="/tools/query.php">&raquo; Opgeslagen queries</a><br />
							<a href="/instellingenbeheer">&raquo; Instellingen</a><br />
							<a href="/beheer">&raquo; Beheeroverzicht</a><br />
						</div>
					</div>
					{literal}
						<script>
							jQuery(document).ready(function($) {
								$('#adminding').click(function() {
									$(this).children('div').toggle();
								});
								$('#adminding div').hide();
							});
						</script>
					{/literal}
				{/if}
				<br />
				<form name="lidzoeker" method="get" action="/communicatie/lijst.php">
					<p>
						{if isset($smarty.get.q)}
							<input type="text" value="{$smarty.get.q|escape:'htmlall'}" name="q" id="zoekveld" />
						{else}
							<input type="text" name="q" id="zoekveld" />
						{/if}
					</p>
				</form>
			</div>
		{/if}
	</div>
</div>
<div id="submenu" onmouseover="ResetTimer();" onmouseout="StartTimer();">
	<div id="submenuitems">
		{assign var=active value=false}
		{foreach name=level1 from=$root->children item=item}
			<div id="sub{$smarty.foreach.level1.iteration}"{if !$active AND $item->active} class="active"{assign var=active value=true}{/if}>
				{foreach name=level2 from=$item->children item=subitem}
					<a href="{$subitem->link}" title="{$subitem->tekst}"{if $subitem->active} class="active"{/if}>{$subitem->tekst}</a>
					{if !$smarty.foreach.level2.last}
						<span class="separator">&nbsp;&nbsp;</span>
					{/if}
				{/foreach}
			</div>
		{/foreach}
	</div>
</div>