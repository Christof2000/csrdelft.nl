<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Weergave van categorieën en het forumoverzicht
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('forum/class.forum.php');
	$forum = new Forum();
	require_once('forum/class.forumcontent.php');
	$midden = new ForumContent($forum, 'forum');
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}


$page=new csrdelft($midden);
$page->addStylesheet('forum.css');
$page->view();

?>
