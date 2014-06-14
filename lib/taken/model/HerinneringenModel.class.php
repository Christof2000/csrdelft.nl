<?php

require_once 'MVC/model/entity/Mail.class.php';
require_once 'taken/model/AanmeldingenModel.class.php';

/**
 * HerinneringenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class HerinneringenModel {

	public static function stuurHerinnering(CorveeTaak $taak) {
		$datum = date('d-m-Y', strtotime($taak->getDatum()));
		$uid = $taak->getLidId();
		if (!Lid::exists($uid)) {
			throw new Exception($datum . ' ' . $taak->getCorveeFunctie()->naam . ' niet toegewezen!' . (!empty($uid) ? ' ($uid =' . $uid . ')' : ''));
		}
		//$to = $lid->getEmail();
		$to = $uid . '@csrdelft.nl';
		$from = 'corvee@csrdelft.nl';
		$onderwerp = 'C.S.R. Delft corvee ' . $datum;
		$bericht = $taak->getCorveeFunctie()->email_bericht;
		$lidnaam = Lid::naamLink($uid, 'civitas', 'plain');
		$eten = '';
		if ($taak->getMaaltijdId() !== null) {
			$aangemeld = AanmeldingenModel::getIsAangemeld($taak->getMaaltijdId(), $uid);
			if ($aangemeld) {
				$eten = Instellingen::get('corvee', 'mail_wel_meeeten');
			} else {
				$eten = Instellingen::get('corvee', 'mail_niet_meeeten');
			}
		}
		$mail = new Mail($to, $onderwerp, $bericht);
		$mail->setFrom($from);
		$mail->setPlaceholders(array('LIDNAAM' => $lidnaam, 'DATUM' => $datum, 'MEEETEN' => $eten));
		if ($mail->send()) { // false if failed
			TakenModel::updateGemaild($taak);
			return $datum . ' ' . $taak->getCorveeFunctie()->naam . ' verstuurd! (' . $lidnaam . ')';
		} else {
			throw new Exception($datum . ' ' . $taak->getCorveeFunctie()->naam . ' faalt! (' . $lidnaam . ')');
		}
	}

	public static function stuurHerinneringen() {
		$vooraf = str_replace('-', '+', Instellingen::get('corvee', 'herinnering_1e_mail'));
		$van = strtotime(date('Y-m-d'));
		$tot = strtotime($vooraf, $van);
		$taken = TakenModel::getTakenVoorAgenda($van, $tot, true);
		$verzonden = array();
		$errors = array();
		foreach ($taken as $taak) {
			if ($taak->getMoetHerinneren()) {
				try {
					$verzonden[] = self::stuurHerinnering($taak);
				} catch (\Exception $e) {
					$errors[] = $e;
				}
			}
		}
		return array($verzonden, $errors);
	}

}
