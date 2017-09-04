<?php
namespace CsrDelft\model\entity\groepen;
use CsrDelft\common\CsrException;
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OnderverenigingStatus.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De status van een ondervereniging.
 *
 */
abstract class OnderverenigingStatus extends PersistentEnum {

	const AdspirantOndervereniging = 'a';
	const Ondervereniging = 'o';
	const VoormaligOndervereniging = 'v';

	public static function getTypeOptions() {
		return array(self::AdspirantOndervereniging, self::Ondervereniging, self::VoormaligOndervereniging);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::AdspirantOndervereniging: return 'adspirant-ondervereniging';
			case self::Ondervereniging: return 'ondervereniging';
			case self::VoormaligOndervereniging: return 'voormalig ondervereniging';
			default: throw new CsrException('OnderverenigingStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::AdspirantOndervereniging:
			case self::Ondervereniging:
			case self::VoormaligOndervereniging:
				return ucfirst($option);
			default: throw new CsrException('OnderverenigingStatus onbekend');
		}
	}

}
