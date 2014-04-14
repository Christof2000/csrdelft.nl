<div id="groepen-menu">
	<ul class="horizontal">
		{assign var="link" value="/groepen/commissies"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Commissies</a>
		</li>
		{assign var="link" value="/groepen/besturen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Besturen</a>
		</li>
		{assign var="link" value="/groepen/sjaarcies"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">SjaarCies</a>
		</li>
		{assign var="link" value="/groepen/woonoorden"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Woonoorden</a>
		</li>
		{assign var="link" value="/groepen/werkgroepen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Werkgroepen</a>
		</li>
		{assign var="link" value="/groepen/onderverenigingen"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Onderverenigingen</a>
		</li>
		{assign var="link" value="/groepen/ketzers"}
		<li{if Instellingen::get('stek', 'request') === $link} class="active"{/if}>
			<a href="{$link}">Overig</a>
		</li>
	</ul>
</div>
<hr/>
<table style="width: 100%;"><tr id="tr-melding"><td id="td-melding">{$view->getMelding()}</td></tr></table>
<h1>{$view->getTitel()}</h1>