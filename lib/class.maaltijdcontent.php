<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.maaltijdcontent.php
# -------------------------------------------------------------------
#
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
#
# -------------------------------------------------------------------
# Historie:
# 20-01-2006 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltrack;

	### public ###

	function MaaltijdContent (&$lid, &$maaltrack) {
		$this->_lid =& $lid;
		$this->_maaltrack =& $maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer'; }
	
	function view(){
		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();
		$profiel->caching=false;
		
		//Dingen ophalen voor....
		//...de eigen aanmeldingen
		$nu=time();
		$aMaal['zelf']['error']=$this->_maaltrack->getError();
		$aMaal['zelf']['maaltijden']=$this->_maaltrack->getMaaltijden($nu, $nu+MAALTIJD_LIJST_MAX_TOT);
		
		//...de abonnementen
		$aMaal['abo']['abos']=$this->_maaltrack->getAbo();
		$aMaal['abo']['nietAbos']=$geenabo = $this->_maaltrack->getNotAboSoort();
		
		//...het aanmelden van andere verenigingsleden
		$aMaal['anderen']['error']=$this->_maaltrack->getProxyError();
		$aMaal['anderen']['maaltijden']=$this->_maaltrack->getMaaltijden($nu, $nu+MAALTIJD_PROXY_MAX_TOT);
		//de door het huidige lid aangemelde leden ophalen voor de opgehaalde maaltijden...
		for($i=0; $i<count($aMaal['anderen']['maaltijden']); $i++){
			$maalID=$aMaal['anderen']['maaltijden'][$i]['id'];
			$anderen=$this->_maaltrack->getProxyAanmeldingen($this->_lid->getUid(), $maalID);
			if(count($anderen)==0){
				$aMaal['anderen']['maaltijden'][$i]['derden']=false;
			}else{
				$aMaal['anderen']['maaltijden'][$i]['derden']=$anderen;
			}
		}
				
		//...gasten, FIXME
		$aMaal['gasten']=array();
		
		//arrays toewijzen en weergeven
		$profiel->assign('maal', $aMaal);
		$profiel->assign('toonLijsten', $this->_lid->hasPermission('P_MAAL_MOD') or opConfide());
		$profiel->assign('datumFormaat', '%a %e %b %H:%M'); 
		$profiel->display('maaltijdketzer.tpl');
	}
}

?>
