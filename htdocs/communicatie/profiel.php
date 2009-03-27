<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

# /leden/profiel.php

require_once 'include.config.php';

require_once 'lid/class.profiel.php';

if(isset($_GET['uid'])){
	$uid=$_GET['uid'];
}else{
	$uid=$loginlid->getUid();
}

if(isset($_GET['a'])){
	$actie=$_GET['a'];
}else{
	$actie='view';
}

if(!($loginlid->hasPermission('P_LEDEN_READ') or $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	require_once 'class.paginacontent.php';
	$midden=new PaginaContent(new Pagina('geentoegang'));
	$midden->setActie('bekijken');
}else{
	require_once 'lid/class.profielcontent.php';
	
	switch($actie){
		case 'bewerken':
			require_once 'lid/class.profiel.php';
			$profiel=new Profiel($uid);
			if($profiel->magBewerken()){
				if($profiel->isPosted() AND $profiel->valid() AND $profiel->save()){
					header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid);
				}else{
					$midden=new ProfielEditContent($profiel);
				}
			}else{
				$midden=new ProfielContent(LidCache::getLid($uid));
			}
			
		break;
		case 'rssToken':
			if($uid==$loginlid->getUid()){
				$loginlid->getToken();
				header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid.'#forum');
				exit;
			}
		//geen break hier.
		case 'view':
		default;
			$midden=new ProfielContent(LidCache::getLid($uid));
		break;
	}
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('profiel.css');
$pagina->addScript('profiel.js');
$pagina->view();

?>
