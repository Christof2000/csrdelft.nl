<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# plakkerig.php
# -------------------------------------------------------------------
# Verwerkt het plakkerig maken van ondewerpen in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

if(!$lid->hasPermission('P_FORUM_MOD')){
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Geen rechten hiervoor';
	exit;
}

require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();
if(isset($_GET['topic'])){
	$forum->load((int)$_GET['topic']);
	if(!$forum->togglePlakkerigheid()){
		$_SESSION['forum_foutmelding']='Oeps, feutje, niet gelukt dus';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Niets om te sluiten of te openen.';
}
	

?>
