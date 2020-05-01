<?php
/**
 * GroepLijstView.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\groepen\leden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\groepen\formulier\GroepAanmeldenForm;
use CsrDelft\view\groepen\formulier\GroepBewerkenForm;
use CsrDelft\view\Icon;

class GroepLijstView extends GroepTabView {

	public function getTabContent() {
		$em = ContainerFacade::getContainer()->get('doctrine.orm.entity_manager');

		$html = '<table class="groep-lijst"><tbody>';
		if ($this->groep->mag(AccessAction::Aanmelden)) {
			$html .= '<tr><td colspan="2">';
			$lid = $em->getRepository($this->groep->getLidType())->nieuw($this->groep, LoginModel::getUid());
			$form = new GroepAanmeldenForm($lid, $this->groep, false);
			$html .= $form->getHtml();
			$html .= '</td></tr>';
		}
		$leden = group_by_distinct('uid', $this->groep->getLeden());
		if (empty($leden)) {
			return $html . '</tbody></table>';
		}
		// sorteren op achernaam
		$uids = array_keys($leden);
		$profielRepository = ContainerFacade::getContainer()->get(ProfielRepository::class);
		/** @var Profiel[] $profielen */
		$profielen = $profielRepository->createQueryBuilder('p')
			->where('p.uid in (:uids)')
			->setParameter('uids', $uids)
			->orderBy('p.achternaam')
			->getQuery()->getResult();
		foreach ($profielen as $profiel) {
			$html .= '<tr><td>';
			if ($profiel->uid === LoginModel::getUid() AND $this->groep->mag(AccessAction::Afmelden)) {
				$html .= '<a href="' . $this->groep->getUrl() . '/ketzer/afmelden" class="post confirm float-left" title="Afmelden">' . Icon::getTag('bullet_delete') . '</a>';
			}
			$html .= $profiel->getLink('civitas');
			$html .= '</td><td>';
			if ($profiel->uid === LoginModel::getUid() AND $this->groep->mag(AccessAction::Bewerken)) {
				$form = new GroepBewerkenForm($leden[$profiel->uid], $this->groep);
				$html .= $form->getHtml();
			} else {
				$html .= $leden[$profiel->uid]->opmerking;
			}
			$html .= '</td></tr>';
		}
		return $html . '</tbody></table>';
	}

}
