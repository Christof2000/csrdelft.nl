{strip}
	{assign var=draad value=$draden[$post->draad_id]}
	{assign var=timestamp value=strtotime($post->datum_tijd)}
	<div class="item hoverIntent">
		{*include file='MVC/forum/post_preview.tpl'*}
		{if date('d-m', $timestamp) === date('d-m')}
			{$timestamp|date_format:"%H:%M"}
		{elseif strftime('%U', $timestamp) === strftime('%U')}
			<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
		{else}
			{$timestamp|date_format:"%d-%m"}
		{/if}
		&nbsp;
		<a href="/forum/reactie/{$post->post_id}#{$post->post_id}" title="{$draad->titel}"{if !$draad->alGelezen()} class="opvallend"{/if}>
			{$draad->titel|truncate:25:"…":true}
		</a>
	</div>
{/strip}