<?php
/*
 * naamlink.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * geeft een naamlink voor een gegeven uid.
 */

require_once 'configuratie.include.php';

if(!LoginLid::mag('P_LEDEN_READ')){
	echo 'Niet voldoende rechten';
	exit;
}
//is er een uid gegeven?
$given = 'uid';
if(isset($_GET['uid'])){
	$string=urldecode($_GET['uid']);
}elseif(isset($_POST['uid'])){
	$string=$_POST['uid'];

//is er een naam gegeven?
}elseif(isset($_GET['naam'])){
	$string=urldecode($_GET['naam']);
	$given='naam';
}elseif(isset($_POST['naam'])){
	$string=$_POST['naam'];
	$given='naam';

//geen input
}else{
	echo 'Fout in invoer in tools/naamlink.php';
}

//welke subset van leden?
$zoekin=array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID','S_ERELID');
$toegestanezoekfilters=array('leden', 'oudleden', 'alleleden', 'allepersonen', 'nobodies');
if(isset($_GET['zoekin']) AND in_array($_GET['zoekin'], $toegestanezoekfilters)){
	$zoekin=$_GET['zoekin'];
}

function uid2naam($uid){
	$lid=LidCache::getLid($uid);
	if($lid instanceof Lid){
		return $lid->getNaamLink('civitas', 'visitekaartje');
	}else{
		return 'Geen geldig lid';
	}
}

//zoekt uid op en returnt met uid2naam weer de naam
function naam2naam($naam, $zoekin){
	$rnaam=namen2uid($naam, $zoekin);
	if($rnaam){
		if(isset($rnaam[0]['uid'])){
			return uid2naam($rnaam[0]['uid']);
		}else{
			if(count($rnaam[0]['naamOpties'])>0){
				return 'Meerdere leden mogelijk';
			}
		}
	}
	return 'Geen lid gevonden';
}

if($given=='uid'){
	if(Lid::isValidUid($string)){
		echo uid2naam($string);
	}else{
		$uids=explode(',', $string);
		foreach($uids as $uid){
			echo uid2naam($uid);
		}
	}
}elseif($given=='naam'){
	echo naam2naam($string, $zoekin);
}
?>
