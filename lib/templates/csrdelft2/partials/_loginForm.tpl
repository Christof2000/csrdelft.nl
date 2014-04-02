{if LoginLid::mag('P_LOGGED_IN') }
<p>
	<a href="/instellingen/" class="instellingen no-line" title="Webstekinstellingen">{icon get="instellingen"}</a>
	{if LoginLid::instance()->isSued()}
		<a href="/endsu/" style="color: red;">{LoginLid::instance()->getSuedFrom()->getNaamLink('civitas', 'link')} als</a><br />�
	{/if}
	{LoginLid::instance()->getUid()|csrnaam}
	<a href="/logout.php">log&nbsp;uit</a><br />
		{foreach from=LoginLid::instance()->getLid()->getSaldi() item=saldo}
				{$saldo.naam}: &euro; {$saldo.saldo|number_format:2:",":"."} <br />
		{/foreach}

</p>
<p><a href="/leden">Ga naar ledengedeelte &raquo;</a></p>
{else}

<form action="/login.php" method="post">
	<fieldset>
		<input type="hidden" name="url" value="/" />
		<input class="text" type="text" name="user" placeholder="Bijnaam of lidnummer" />
		<input class="text" type="password" name="pass" placeholder="Wachtwoord" />
		<input class="submit" type="submit" name="login" value="Inloggen" />
	</fieldset>{if isset($smarty.session.auth_error)}
	<p class="error">{$smarty.session.auth_error}</p>{/if}
</form>
<ul>
	<li><a href="#" class="login-submit">Inloggen</a> &raquo;</li>
	<li><a href="/accountaanvragen">Account aanvragen</a> &raquo;</li>
</ul>
{/if}
