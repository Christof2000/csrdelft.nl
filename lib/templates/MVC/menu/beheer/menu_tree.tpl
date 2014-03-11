{*
	menu_tree.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{$view->getMelding()}
<div style="float: right;">
	<div style="display: inline-block;"><label for="toon">Toon menu:</label>
	</div><select name="toon" onchange="location.href = '/menubeheer/beheer/' + this.value;">
		<option selected="selected">kies</option>
		{foreach from=$menus item=menu}
			<option value="{$menu}">{$menu}</option>
		{/foreach}
	</select>
</div>
<h1 style="width: 650px;">{$view->getTitel()}</h1>
<br />
<ul class="menubeheer-tree">
	{if $root}
		{include file='MVC/menu/beheer/menu_root.tpl'}
		{if $root->children}
			{foreach from=$root->children item=child}
				{include file='MVC/menu/beheer/menu_item.tpl' item=$child}
			{/foreach}
		{/if}
	{/if}
</ul>