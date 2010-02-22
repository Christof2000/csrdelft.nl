<?php
/*
 *  C.S.R. Delft | pubcie@csrdelft.nl
 * 
 * LLWeergave, LLLijst, LLKaartje, LLCSV:
 * 		verschillende methode's om dingen in de ledenlijst weer te geven. 
 * 		Als je een nieuwe weergave erbij wilt klussen maak dan een class
 * 		LL<Naam> extends LLWeergave{} aan en voeg die naam toe aan de 
 * 		array private $weergave in LidZoeker.
 * 
 * LedenlijstContent
 * 		Algemene View voor de ledenlijst.
 */
require_once 'lid/class.lidzoeker.php';


class LedenlijstContent extends SimpleHTML{
	private $zoeker;
	
	public function __construct(LidZoeker $zoeker){
		$this->zoeker=$zoeker;
	}
	
	public function getTitel(){ return 'Ledenlijst der Civitas'; }
	
	public function viewSelect($name, $options){
		echo '<select name="'.$name.'" id="f'.$name.'">';
		foreach($options as $key => $value){
			echo '<option value="'.htmlspecialchars($key).'"';
			if($key==$this->zoeker->getRawQuery($name)){
				echo ' selected="selected"';
			}
			echo '>'.mb_htmlentities($value).'</option>';
		}
		echo '</select> ';
	}
	public function viewVeldselectie(){
		echo '<label for="veldselectie">Veldselectie: </label>';
		echo '<div id="veldselectie">';
		$velden=$this->zoeker->getSelectableVelden();
		foreach($velden as $key => $veld){
			echo '<div class="selectVeld">';
			echo '<input type="checkbox" name="velden[]" id="veld'.$key.'" value="'.$key.'" ';
			if(in_array($key, $this->zoeker->getSelectedVelden())){
				echo 'checked="checked" ';
			}
			echo ' />';
			echo '<label for="veld'.$key.'">'.ucfirst($veld).'</label>';
			echo '</div>';
		}
		echo '</div>';
	}
	public function view(){
		echo '<ul class="horizontal nobullets">
	<li class="active"><a href="/communicatie/ledenlijst/">Ledenlijst</a></li>
	<li><a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a></li>
	<li><a href="/communicatie/verticalen/">Kringen</a></li>
</ul>';
		echo '<hr /><h1>Ledenlijst</h1>';
		echo '<form method="get" id="zoekform">';
		echo '<label for="q"></label><input type="text" name="q" value="'.htmlspecialchars($this->zoeker->getQuery()).'" /> ';
		echo '<input type="submit" class="submit" value="zoeken" /> <a class="knop" id="toggleAdvanced" href="#geavanceerd">Geavanceerd</a>';
		
		echo '<div id="advanced" class="verborgen">';
		echo '<label for="status">Status:</label>';
		$this->viewSelect('status', array(
			'LEDEN'=>'Leden', 
			'NOVIET'=>'Novieten', 'GASTLID'=>'Gastlid', 
			'LEDEN|OUDLID'=>'Leden & oudleden', 'ALL'=>'Alles'));
		echo '<br />';
		echo '<label for="weergave">Weergave:</label>';
		$this->viewSelect('weergave', array(
			'lijst' => 'Lijst (standaard)', 
			'kaartje' => 'Visitekaartjes',
			'CSV' => 'CSV-bestand'));
		echo '<br />';
			
		//sorteren op:
		echo '<label for="sort">Sorteer op:</label>';
		$this->viewSelect('sort', $this->zoeker->getSortableVelden());
		echo '<br />';
			
		//selecteer velden
		echo '<div id="veldselectiecontainer">';
		$this->viewVeldselectie();
		echo '</div><br />';
		
		echo '</div>'; //einde advanced div.
		echo '</form>';
		
		echo '<hr class="clear" />';
		
		if($this->zoeker->count()>0){
			$viewclass=$this->zoeker->getWeergave();
			$view=new $viewclass($this->zoeker);
			$view->view();
		}elseif($this->zoeker->searched()){
			echo 'Geen resultaten';
		}else{
			//nog niet gezocht.
		}
		?>
		<script type="text/javascript">
			function updateVeldselectie(){
				if($('#fweergave').val()=='kaartje'){
					$('#veldselectiecontainer').hide('fast');
				}else{
					$('#veldselectiecontainer').show('fast');
				}
			}
			
			$(document).ready(function(){
				$('#toggleAdvanced').click(function(){
					adv=$('#advanced');
					adv.toggleClass('verborgen');
					
					if(adv.hasClass('verborgen')){
						window.location.hash='';
						$('#advanced input').attr('disabled', 'disabled');
					}else{
						window.location.hash='geavanceerd';
						$('#zoekform').attr('action', '#geavanceerd');
						$('#advanced input').removeAttr('disabled');
						$('#advanced select').removeAttr('disabled');
					}
				});
				
				if(document.location.hash=='#geavanceerd'){
					$('#advanced').removeClass('verborgen');
				}else{
					$('#advanced input').attr('disabled', 'disabled');
					$('#advanced select').attr('disabled', 'disabled');
				}
				//weergave van selectie beschikbare veldjes
				$('#fweergave').change(function(){
					updateVeldselectie();
					$('#zoekform').submit();
					});
				updateVeldselectie();
			});
		</script>
		
		<?php
	}
}

abstract class LLWeergave{
	protected $leden;
	
	public function __construct(LidZoeker $zoeker){
		$this->leden=$zoeker->getLeden();
		$this->velden=$zoeker->getVelden();
	}
	
	public abstract function viewHeader();
	public abstract function viewFooter();
	
	//viewLid print één regel of vakje ofzo.
	public abstract function viewLid(Lid $lid);
	
	public function view(){
		$this->viewHeader();
		foreach($this->leden as $lid){
			$this->viewLid($lid);
		}
		$this->viewFooter();
	}
	
}

/*
 * De 'normale' ledenlijst, zoals het is zoals het was.
 */
class LLLijst extends LLweergave{ 
	
	private function viewVeldnamen(){
		echo '<tr>';
		foreach($this->velden as $veld){
			echo '<th>'.ucfirst($veld).'</th>';
		}
		echo '</tr>';
	}
	public function viewHeader(){
		echo '<table class="zoekResultaat" id="zoekResultaat">';
		echo '<thead class="above">';
		$this->viewVeldnamen();
		echo '</thead><tbody>';
	}

	public function viewFooter(){
		echo '</tbody><thead class="below">';
		$this->viewVeldnamen();
		
		echo '</thead></table>';
		
		//fix jQuery datatables op deze tabel.
		$aoColumns=array();
		foreach($this->velden as $veld){
			switch($veld){
				case 'pasfoto':
					$aoColumns[]='{"bSortable": false}';
				break;
				case 'email':
				case 'naam':
				case 'kring':
				case 'patroon':
				case 'verticale':
					$aoColumns[]='{"sType": \'html\'}';
				break;
				default:
					$aoColumns[]='null';
			}
		}
		?><script type="text/javascript">
		$(document).ready(function(){
			$("#zoekResultaat tr:odd").addClass('odd');

			$("#zoekResultaat").dataTable({
				"oLanguage": {
					"sSearch": "Zoeken in selectie:"
				},
				"iDisplayLength": 50,
				"bInfo": false,
				"bLengthChange": false,
				"aoColumns": [ <?php echo implode(', ', $aoColumns); ?> ]
			}
			
			);
		});
		</script><?php
	}
	
	public function viewLid(Lid $lid){
		echo '<tr id="lid'.$lid->getUid().'">';
		foreach($this->velden as $veld){
			echo '<td class="'.$veld.'">';
			switch($veld){
				case 'adres':
					echo mb_htmlentities($lid->getAdres());
				break;
				case 'kring':
					echo $lid->getKring(true);
				break;
				case 'naam': 
					echo $lid->getNaamLink('full', 'link'); 
				break;
				case 'pasfoto': 
					echo $lid->getPasfoto(); 
				break;
				case 'patroon':
					$patroon=$lid->getPatroon();
					if($patroon instanceof Lid){
						echo $patroon->getNaamLink('full', 'link');
					}else{
						echo '-';
					}
				break;
				case 'status':
					echo $lid->getStatusDescription();
				break;
				case 'verticale':
					echo mb_htmlentities($lid->getVerticale());
				break;
				default:
					echo mb_htmlentities($lid->getProperty($veld));
			}
			echo '</td>';
		}
		
		echo '</tr>';
	}

}

/*
 * Visitekaartjes, 3 op één regel.
 */
class LLKaartje extends LLweergave{ 
	public function viewHeader(){}
	public function viewFooter(){}
	
	public function viewLid(Lid $lid){
		echo '<div class="visitekaartje"  id="lid'.$lid->getUid().'">';
		echo $lid->getNaamLink('pasfoto', 'link');
		echo '<h2>'.$lid->getNaamLink('full', 'link' ).'</h2>';
		echo '<div class="adres">';
		echo $lid->getProperty('adres').'<br />'.$lid->getProperty('postcode').' '.$lid->getProperty('woonplaats').'<br />';
		echo $lid->getProperty('mobiel').'<br />';
		echo '<a href="mailto:'.htmlspecialchars($lid->getEmail()).'">'.mb_htmlentities($lid->getEmail()).'</a><br />';
		echo '</div></div>';
	}
}

/*
 * CSV in een textarea. 
 * Eventueel zou het nog geforceerd downloadbaar gemaakt kunnen worden
 */
class LLCSV extends LLweergave{
	public function viewHeader(){
		echo '<textarea class="csv">';
	}
	public function viewFooter(){
		echo '</textarea>';
	}
	
	public function viewLid(Lid $lid){
		$return='';
		foreach($this->velden as $veld){
			switch($veld){
				case 'adres':
					$return.=$lid->getProperty('adres').';';
					$return.=$lid->getProperty('postcode').';';
					$return.=$lid->getProperty('woonplaats');
				break;
				case 'naam':
					$return.=$lid->getProperty('voornaam').';';
					$return.=$lid->getProperty('tussenvoegsel').';';
					$return.=$lid->getProperty('achternaam');
				break;
				case 'kring':
					$return.=$lid->getKring(false);
				break;
				case 'pasfoto':
					$return.=$lid->getPasfoto(false);
				break;
				case 'patroon':
					$return.=$lid->getPatroonUid();
				break;
				case 'verticale':
					$return.=$this->getVerticale();
				break;
				default:
					try{
						$return.=$lid->getProperty($veld);
					}catch(Exception $e){
						//omit non-existant fields
					}
				break;
			}
			echo htmlspecialchars($return).';';
		}
		echo "\n";
	}
}

?>
