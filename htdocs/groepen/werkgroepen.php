<?php

# instellingen & rommeltjes
require_once('include.config.php');


# Het middenstuk
$midden = new Includer('informatie', 'werkgroep.html', $lid);

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);
$pagina->view();
	


?>
