<?php

/**
 * VerticalenView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Weergeven van verticalen.
 */
class VerticalenView implements View {

	private $verticalen;

	public function __construct() {
		$this->verticalen = VerticaleOldModel::getAll();
	}

	public function getModel() {
		return $this->verticalen;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><img src="' . CSR_PICS . '/knopjes/people-16.png" class="module-icon"></a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Verticalen der Civitas';
	}

	public function view() {
		?><ul class="horizontal nobullets">
			<li>
				<a href="/ledenlijst">Ledenlijst</a>
			</li>
			<li>
				<a href="/leden/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
			</li>
			<li class="active">
				<a href="/verticalen">Kringen</a>
			</li>
		</ul>
		<hr />
		<?php
		foreach ($this->verticalen as $verticale) {
			echo '<div class="verticale">';
			echo '<h2><a name="' . $verticale->getLetter() . '">Verticale ' . $verticale->getNaam() . '</a></h2>';
			foreach ($verticale->getKringen() as $kringnaam => $kring) {
				$kringstyle = 'kring';
				if ($kringnaam == 0) {
					$kringstyle = 'geenkring';
				}
				echo '<div class="' . $kringstyle . '" id="kring' . $verticale->getLetter() . '.' . $kringnaam . '">';
				echo '<div class="mailknopje" onclick="toggleEmails(\'' . $verticale->getLetter() . '.' . $kringnaam . '\')">@</div>';
				if ($kringnaam == 0) {
					echo '<h5>Geen kring</h5>';
				} else {
					echo '<h5>Kring ' . $kringnaam . '</h5>';
				}
				echo '<div id="leden' . $verticale->getLetter() . '.' . $kringnaam . '" class="kringleden">';
				foreach ($kring as $lid) {
					if ($lid->isKringleider())
						echo '<em>';
					echo $lid->getNaamLink('volledig', 'visitekaartje');
					if ($lid->getStatus() == 'S_KRINGEL')
						echo '&nbsp;~';
					if ($lid->isVerticaan())
						echo '&nbsp;L';
					if ($lid->isKringleider())
						echo '</em>';
					echo '<br />';
				}
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
		}
		// bottom spacing
		for ($i = 0; $i < 11; $i++) {
			echo '<p>&nbsp;</p>';
		}
		?>
		<script type="text/javascript">
			if (document.location.hash.substring(1, 6) == 'kring') {
				kring = document.location.hash.substring(1);
				document.getElementById(kring).style.backgroundColor = '#f5f5f5';
				document.getElementById(kring).style.borderBottom = '1px solid black';
			}
		</script>
		<?php
	}

}

class VerticaleEmailsView implements View {

	private $verticale;
	private $kring;

	public function __construct($vertkring) {
		try {
			$this->verticale = new VerticaleOldModel(substr($vertkring, 0, 1));
			$this->kring = $this->verticale->getKring((int) substr($vertkring, 2, 1));
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
			$this->kring = array();
		}
	}

	public function view() {
		$leden = array();
		foreach ($this->kring as $kringlid) {
			$leden[] = $kringlid->getEmail();
		}
		echo implode(', ', $leden);
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->kring;
	}

	public function getTitel() {
		return null;
	}

}
