<?php
/*
 * class.knop.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Algemene code om knopjes in html te regelen.
 * Door dit te centraliseren komen er niet op 100 plekken links naar plaatjes te staan enzo.
 *
 * Knopjes staan in {PICS_PATH}/knopjes/{$type}.png, zijn dus altijd van het type png.
 */
class Knop{

	private $knoptypes=array('default', 'toevoegen', 'bewerken', 'verwijderen', 'citeren');
	public $url;			//url van de knop.
	public $type='default';	//type van de knop, default= zonder plaatje, bij de andere opties hoort een plaatje.
	public $class='knop'; 	//css class
	public $text=null;		//eventuele tekst naast het plaatje
	public $confirm=false;	//javascript confirm toevoegen?
							//Kan true zijn of een string, bij true wordt de vraag 'weet u het zeker'
							//anders de inhoud van de string.

	public function __construct($url){
		$this->url=$url;
	}
	public function setText($text){
		$this->text=mb_htmlentities($text);
	}
	public function setType($type){
		if(in_array($type, $this->knoptypes)){
			$this->type=$type;
			$this->setClass('knopKaal');
		}else{
			$this->type='default';
			return false;
		}

	}
	public function setClass($class){
		$this->class=mb_htmlentities($class);
	}
	/*
	 * false == geen javascript confirm
	 * true == een javascript confirm met 'weet u het zeker?' als tekst
	 * string == een javascript confirum met $string als tekst
	 */
	public function setConfirm($confirm){
		$this->confirm=$confirm;
	}
	private function getImgTag(){
		return '<img src="'.CSR_PICS.'/knopjes/'.$this->type.'.png" title="'.ucfirst($this->type).'" />';
	}
	public function getHtml(){
		$html='<a href="'.$this->url.'" class="'.$this->class.'" ';
		if($this->confirm!==false){
			if($this->confirm===true){
				$confirm='Weet u het zeker?';
			}else{
				$confirm=$this->confirm;
			}
			$html.='onclick="return confirm(\''.$confirm.'\')" ';
		}
		$html.='>';
		if($this->type=='default'){
			//knopje zonder plaatje, checken of er wel een tekst is, anders een foutmelding meegeven
			if($this->text==null){
				$this->text=='Knop::getHtml(): Geen tekst opgegeven bij een knop zonder plaatje.';
			}
		}else{
			//we gaan een plaatje erbij doen.
			$html.=$this->getImgTag();
		}
		$html.=$this->text;
		$html.='</a>';
		return $html;
	}
	public function view(){
		echo $this->getHtml();
	}
	public static function getKnop($url, $type, $text=null){
		$knop=new Knop($url);
		$knop->setType($type);
		$knop->setText($text);
		return $knop->getHtml();
	}
	public static function viewKnop($url, $type, $text=null){
		echo getKnop($url, $type, $text);
	}
}

?>