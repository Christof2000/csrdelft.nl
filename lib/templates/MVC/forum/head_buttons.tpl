{if LoginModel::mag('P_LOGGED_IN')}
	<div class="forumheadbtn">
		<a href="/forum/toonalles" class="knop post confirm ReloadPage" title="Verborgen onderwerpen weer laten zien">{icon get="eye"} {ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid()}</a>
	</div>
	<div class="forumheadbtn">
		<a href="/forum/volgniets" class="knop post confirm ReloadPage" title="Geen onderwerpen meer volgen">{icon get="email"} {ForumDradenVolgenModel::instance()->getAantalVolgenVoorLid()}</a>
	</div>
	<div class="forumheadbtn">
		<a href="/forum/wacht" class="knop" title="Reacties die wachten op goedkeuring">{icon get="hourglass"} {ForumPostsModel::instance()->getAantalWachtOpGoedkeuring()}</a>
	</div>
{/if}