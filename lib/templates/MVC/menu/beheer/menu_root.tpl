{*
	menu_root.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<a href="/menubeheer/bewerken/{$root->item_id}" class="knop post modal" title="Naam van dit menu bewerken">{icon get="bewerken"}</a>
<a href="/menubeheer/toevoegen/{$root->item_id}" class="knop post modal" title="Menu-item toevoegen">{icon get="add"}</a>
<span class="">{$root->tekst}</span>
<span class="lichtgrijs">{$root->item_id}</span>
<div class="float-right">
	<a href="{$root->link}">{$root->link}</a>
{if !$root->children}
	<a href="/menubeheer/verwijderen/{$root->item_id}" title="Dit menu definitief verwijderen" class="knop post confirm ReloadPage">{icon get="cross"}</a>
{/if}
</div>
<hr />