<?php

require_once 'MVC/model/Database.singleton.php';


class Barsysteem
{

    var $db;
	private $beheer;

    function Barsysteem()
    {
        $this->db = Database::instance();
    }
	
	function isLoggedIn()
	{
		return isset($_COOKIE['barsysteem']) && md5('my_salt_is_strong' . $_COOKIE['barsysteem']) == '8f700ce34a77ef4ef9db9bbdde9e97d8';
	}
	
	function isBeheer()
	{
		if(!$this->beheer)
			$this->beheer = isset($_COOKIE['barsysteembeheer']) && md5('my_salt_is_strong' . $_COOKIE['barsysteembeheer']) == '49ee17fb49f2075df6bb538eee4e415e';
		
		return $this->beheer;
	}

    function getPersonen()
    {
		 
        $terug = $this->db->query("SELECT socCieKlanten.stekUID, socCieKlanten.socCieId, socCieKlanten.naam, socCieKlanten.saldo, COUNT(socCieBestelling.totaal) AS recent FROM socCieKlanten LEFT JOIN socCieBestelling ON (socCieKlanten.socCieId = socCieBestelling.socCieId AND DATEDIFF(NOW(), tijd) < 100 AND socCieBestelling.deleted = 0) WHERE socCieKlanten.deleted = 0 GROUP BY socCieKlanten.socCieId;");
        $result = array();
        foreach ($terug as $row) {
            $persoon = array();
            $persoon["naam"] = $row["naam"];
            if ($row["stekUID"]) {
                $lid = LidCache::getLid($row["stekUID"]);
                $persoon["naam"] = $lid->getNaam();
				$persoon["status"] = $lid->getStatus()->__toString();
            }
            $persoon["socCieId"] = $row["socCieId"];
            $persoon["bijnaam"] = $row["naam"];
            $persoon["saldo"] = $row["saldo"];
            $persoon["recent"] = $row["recent"];
            $result[$row["socCieId"]] = $persoon;
        }
        return $result;
    }

    function getProducten()
    {
        $q = $this->db->prepare("SELECT id, beheer, prijs, beschrijving, prioriteit FROM socCieProduct as P JOIN socCiePrijs as R ON P.id=R.productId WHERE status = '1' AND CURRENT_TIMESTAMP<tot AND CURRENT_TIMESTAMP>van ORDER BY prioriteit DESC");
		$q->execute();
	
		$result = array();
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $product = array();
            $product["productId"] = $row["id"];
            $product["prijs"] = $row["prijs"];
            $product["beheer"] = $row["beheer"];
            $product["beschrijving"] = $row["beschrijving"];
            $product["prioriteit"] = $row["prioriteit"];
            $result[$row["id"]] = $product;
        }
        return $result;
    }

    function verwerkBestelling($data)
    {
        $this->db->beginTransaction();

        $q = $this->db->prepare("INSERT INTO socCieBestelling (socCieId) VALUES (:socCieId);");
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $bestelId = $this->db->lastInsertId();
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId,  :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
            $q->execute();
        }
        $totaal = $this->getBestellingTotaal($bestelId);
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :totaal WHERE socCieId=:socCieId ;");
        $q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling  SET totaal = :totaal WHERE id = :bestelId;");
        $q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->execute();

        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function getBestellingPersoon($socCieId)
    {
        $q = $this->db->prepare("SELECT * FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId WHERE socCieId=:socCieId AND B.deleted = 0");
        $q->bindValue(":socCieId", $socCieId, PDO::PARAM_INT);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function getBestellingLaatste($persoon, $begin, $eind)
    {
        if ($begin == "") {
            $begin = date("Y-m-d H:i:s", time() - 15 * 3600);
        } else {
            $begin = $this->parseDate($begin) . " 00:00:00";
        }
        if ($eind == "") {
            $eind = date("Y-m-d H:i:s");
        } else {
            $eind = $this->parseDate($eind) . " 23:59:59";
        }
        $qa = "";
        if ($persoon != "alles") $qa = "B.socCieId=:socCieId AND";
        $q = $this->db->prepare("SELECT * FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId JOIN socCieKlanten AS K ON B.socCieId = K.socCieId WHERE " . $qa . " (tijd BETWEEN :begin AND :eind) AND B.deleted = 0 AND K.deleted = 0");
        if ($persoon != "alles") $q->bindValue(":socCieId", $persoon, PDO::PARAM_INT);
        $q->bindValue(":begin", $begin);
        $q->bindValue(":eind", $eind);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function updateBestelling($data)
    {
	
        $this->db->beginTransaction();

		// Add old order to saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
		
		// Remove old contents of the order
		$q = $this->db->prepare("DELETE FROM socCieBestellingInhoud WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
		$q->execute();		
		
		// Add contents of the order
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId, :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->execute();
        }
		
		// Substract new order from saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
		
		// Update old order
        $q = $this->db->prepare("UPDATE socCieBestelling SET totaal = :totaal  WHERE id = :bestelId");
		$q->bindValue(":totaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
        $q->execute();
		
		// Roll back if error
        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function getSaldo($socCieId)
    {
        $q = $this->db->prepare("SELECT saldo FROM socCieKlanten WHERE socCieId = :socCieId");
        $q->bindValue(":socCieId", $socCieId);
        $q->execute();
        return $q->fetchColumn();
    }

    function verwijderBestelling($data)
    {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon, PDO::PARAM_INT);
        $q->execute();
		/* Don't remove this for backup
        $q = $this->db->prepare("DELETE FROM socCieBestellingInhoud  WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute(); */
        $q = $this->db->prepare("UPDATE socCieBestelling SET deleted = 1 WHERE id = :bestelId");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit() || $q->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }
        return true;

    }

    private function verwerkBestellingResultaat($queryResult)
    {
        $result = array();
        foreach ($queryResult as $row) {
            if (!array_key_exists($row["bestellingId"], $result)) {
                $result[$row["bestellingId"]] = array();
                $result[$row["bestellingId"]]["bestelLijst"] = array();
                $result[$row["bestellingId"]]["bestelTotaal"] = $row["totaal"];
                $result[$row["bestellingId"]]["persoon"] = $row["socCieId"];
                $result[$row["bestellingId"]]["tijd"] = $row["tijd"];
                $result[$row["bestellingId"]]["bestelId"] = $row["id"];

            }
            $result[$row["bestellingId"]]["bestelLijst"][$row["productId"]] = 1 * $row["aantal"];
        }
        return $result;
    }

    private function getBestellingTotaal($bestelId)
    {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND CURRENT_TIMESTAMP < tot AND CURRENT_TIMESTAMP > van");
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchColumn();
    }

    private function getBestellingTotaalTijd($bestelId, $timestamp)
    {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND :timeStamp < tot AND :timeStamp > van");
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->bindValue(":timeStamp", $timestamp, PDO::PARAM_STMT);
        $q->execute();
        return $q->fetchColumn();
    }

    private function parseDate($date)
    {
        $elementen = explode(" ", $date);
        $datum = str_pad($elementen[0], 2, "0", STR_PAD_LEFT);
        $maanden = ["Januari" => "01", "Februari" => "02", "Maart" => "03", "April" => "04", "Mei" => "05", "Juni" => "06", "Juli" => "07", "Augustus" => "08", "September" => "09", "Oktober" => "10", "November" => "11", "December" => "12"];
        return ($elementen[2] . "-" . $maanden[$elementen[1]] . "-" . $datum);
    }
	
	// Beheer
	public function getGrootboekInvoer() {
	
		// GROUP BY week 
		$q = $this->db->prepare("
SELECT G.type,
	SUM(I.aantal * PR.prijs) AS total,
	WEEK(B.tijd, 3) AS week,
	YEARWEEK(B.tijd) AS yearweek
FROM socCieBestelling AS B
JOIN socCieBestellingInhoud AS I ON
	B.id = I.bestellingId
JOIN socCieProduct AS P ON
	I.productId = P.id
JOIN socCiePrijs AS PR ON
	P.id = PR.productId
	AND (B.tijd BETWEEN PR.van AND PR.tot)
JOIN socCieGrootboekType AS G ON
	P.grootboekId = G.id
WHERE
	B.deleted = 0
GROUP BY
	yearweek,
	G.id
ORDER BY yearweek DESC
		");
		$q->execute();
		
		$weeks = array();
		
		while($r = $q->fetch(PDO::FETCH_ASSOC)) {
		
			$exists = isset($weeks[$r['yearweek']]);
			
			$week = $exists ? $weeks[$r['yearweek']] : array();
			
			if($exists) {
				$week['content'][] = array('type' => $r['type'], 'total' => $r['total']);
			} else {
				$week['content'] = array(array('type' => $r['type'], 'total' => $r['total']));
				$week['title'] = 'Week ' . $r['week'];
			}
			
			$weeks[$r['yearweek']] = $week;
		
		}
		
		return $weeks;
	
	}
	
	public function updatePerson($id, $name) {
	
		$q = $this->db->prepare("UPDATE socCieKlanten SET naam = :naam WHERE socCieId = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		return $q->execute();
	
	}
	
	public function removePerson($id) {
	
		$q = $this->db->prepare("UPDATE socCieKlanten SET deleted = 1 WHERE socCieId = :id");
		$q->bindValue(':id', $id, PDO::PARAM_INT);
		return $q->execute();
	
	}
	
	public function addPerson($name, $saldo) {
	
		$q = $this->db->prepare("INSERT INTO socCieKlanten (naam, saldo) VALUES (:naam, :saldo)");
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		$q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
		return $q->execute();
	
	}
	
	// Log action by type
	public function log($type, $data)
	{
		$value = array();
		foreach($data as $key => $item) {
		
			$value[] = $key . ' = ' . $item;
		
		}
		$value = implode("\r\n", $value);
	
		$q = $this->db->prepare("INSERT INTO socCieLog (ip, type, value) VALUES(:ip, :type, :value)");
		$q->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$q->bindValue(':type', $type, PDO::PARAM_STR);
		$q->bindValue(':value', $value, PDO::PARAM_STR);
		$q->execute();
	}

}
