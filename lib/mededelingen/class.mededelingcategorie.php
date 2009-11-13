<?php
/*
 * class.mededelingcategorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */


class MededelingCategorie{

	private $id;
	private $naam;
	private $prioriteit;
	private $permissie;
	private $plaatje;
	private $beschrijving;

	private $mededelingen=null;

	public function __construct($init){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$init=(int)$init;
			if($init!=0){
				$this->load($init);
			}else{
				//default waarden

			}
		}
	}
	public function load($id=0){
		$db=MySql::instance();
		$loadQuery="
			SELECT id, naam, prioriteit, plaatje, beschrijving
			FROM mededelingcategorie
			WHERE id=".(int)$id.";";
		$mededeling=$db->getRow($loadQuery);
		if(!is_array($mededeling)){
			throw new Exception('MededelingCategorie bestaat niet. (MededelingCategorie::load() met id='.$id.')');
		}
		$this->array2properties($mededeling);
	}
	public function loadMededelingen(){
		$query="SELECT id FROM mededelingen WHERE categorie=".$this->getId().";";

		$this->mededelingen=MySql::instance()->query($query);
	}
	public function save(){
		throw new Exception('Nog niet geïmplementeerd');
		$db=MySql::instance();
		if($this->getId()==0){
			$saveQuery="
				INSERT INTO mededelingcategorie (
					naam, prioriteit, plaatje, beschrijving
				)VALUES(
					'".$db->escape($this->getNaam())."',
					'".$db->escape($this->getPrioriteit())."',
					'".$db->escape($this->getPlaatje())."',
					'".$db->escape($this->getBeschrijving())."'
				)";
		}else{
			$saveQuery="
				UPDATE mededelingcategorie
				SET
					naam='".$db->escape($this->getNaam())."',
					prioriteit='".$db->escape($this->getPrioriteit())."',
					plaatje='".$db->escape($this->getPlaatje())."',
					beschrijving='".$db->escape($this->getBeschrijving())."'
				WHERE id=".$this->getId()."
				LIMIT 1;";
		}
	}
	public function delete(){
		throw new Exception('Nog niet geïmplementeerd MededelingCategorie::delete()');
	}
	public function array2properties($array){
		$this->id=$array['id'];
		$this->naam=$array['naam'];
		$this->prioriteit=$array['prioriteit'];
		$this->permissie=$array['permissie'];
		$this->plaatje=$array['plaatje'];
		$this->beschrijving=$array['beschrijving'];
	}

	public function getMededelingen($force=false){
		if($force OR $this->mededelingen===null){
			//load
			$this->loadMededelingen();
		}
		return $this->mededelingen;
	}

	public function getId(){ return $this->id; }
	public function getNaam(){ return $this->naam; }
	public function getPrioriteit(){ return $this->prioriteit; }
	public function getPlaatje(){ return $this->plaatje; }
	public function getBeschrijving(){ return $this->beschrijving; }

	public function magUitbreiden(){ return LoginLid::instance()->hasPermission($this->permissie); }

	public static function getAll(){
		return MededelingCategorie::getCategorieen();
	}
	public static function getCategorieen(){
		$db=MySql::instance();
		$sCategorieQuery="
			SELECT id, naam, prioriteit, permissie, plaatje, beschrijving
			FROM mededelingcategorie
			ORDER BY prioriteit, id";
		$cats=$db->query2array($sCategorieQuery);

		if(is_array($cats)){
			$return=array();
			foreach($cats as $categorie){
				$return[]=new MededelingCategorie($categorie);
			}
			return $return;
		}
		return false;
	}
}
?>
