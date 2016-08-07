<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
	if(!isset($_GET["function"])) { echo "{\"success\": false}"; exit; }
	final class Db {
		protected static $dbInstance;
		public static function factory(){
			if(!self::$dbInstance){ self::$dbInstance = new PDO("mysql:host="."YOURHOST".";dbname="."YOURDBNAME", "YOURUSERNAME", "YOURPASSWORD"); }
			return self::$dbInstance;
		}
	}
	class WebServiceMethods {
		private $pdo;
		public function __construct() { $this->pdo = Db::factory(); }
		public function Fail() { echo "{\"success\": false}"; exit; }
		public function Test() { echo json_encode(["success" => true, "args" => func_get_args()]); }
		
		public function PokeSearch() {
			if(!isset($_POST["filters"])) { $this->Fail(); }
			$filterData = json_decode($_POST["filters"], true);
			if($filterData == null || count($filterData) == 0) { $this->Fail(); }
			$sqlWhereData = $this->GetWhereQueryFromJSON($filterData);
			$q = $this->pdo->prepare($this->GetPokeQuery($sqlWhereData["sql"]));
			$q->execute($sqlWhereData["params"]);
			$results = $this->DataTableToWordArray($q);
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetSinglePokemon($pokeID) {
			if(!isset($pokeID)) { $this->Fail(); }
			$id = intval($pokeID);
			if($id <= 0) { $this->Fail(); }
			$q = $this->pdo->prepare($this->GetPokeQuery(" p.cID = :poke"));
			$q->execute(["poke" => $id]);
			$results = $this->DataTableToWordArray($q);
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetTypes() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "t"); }
			}
			$sql = "SELECT t.cID, t.sName, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemontypes t";
			$sql .= " INNER JOIN test_pokemontypexref tx ON t.cID = tx.cType INNER JOIN test_pokemon p ON tx.cPokemon = p.cID";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY t.cID";
			$sql .= " ORDER BY t.sName ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["code" => $row["sName"], "name" => $row["sName"], "count" => $row["numEntries"], "id" => $row["cID"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetEggGroups() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "e"); }
			}
			$sql = "SELECT e.cID, e.sName, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemonegggroups e";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON e.cID = ex.cEgg INNER JOIN test_pokemon p ON ex.cPokemon = p.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY e.cID";
			$sql .= " ORDER BY e.sName ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["code" => $row["sName"], "name" => $row["sName"], "count" => $row["numEntries"], "id" => $row["cID"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetGenerations() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "g"); }
			}
			$sql = "SELECT p.iGeneration, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY p.iGeneration";
			$sql .= " ORDER BY p.iGeneration ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["code" => $row["iGeneration"], "name" => "Generation ".$row["iGeneration"], "count" => $row["numEntries"], "id" => $row["iGeneration"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetStages() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "s"); }
			}
			$sql = "SELECT p.iStage, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY p.iStage";
			$sql .= " ORDER BY p.iStage ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$name = $row["iStage"] == 0 ? "Baby" : "Stage ".$row["iStage"]; 
				$results[] = ["code" => $row["iStage"], "name" => $name, "count" => $row["numEntries"], "id" => $row["iStage"]];
			}
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetBodyTypes() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "b"); }
			}
			$sql = "SELECT p.iBodyType, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY p.iBodyType";
			$sql .= " ORDER BY p.iBodyType ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$name = "";
				switch($row["iBodyType"]) {
					case 1: $name = "Head"; break;
					case 2: $name = "Head+Legs"; break;
					case 3: $name = "Fins"; break;
					case 4: $name = "Insectoid"; break;
					case 5: $name = "Quadraped"; break;
					case 6: $name = "Many Wings"; break;
					case 7: $name = "Multiple"; break;
					case 8: $name = "Tentacles"; break;
					case 9: $name = "Head+Base"; break;
					case 10: $name = "Tailed"; break;
					case 11: $name = "Tailless"; break;
					case 12: $name = "Wings"; break;
					case 13: $name = "Serpentine"; break;
					case 14: $name = "Head+Arms"; break;
				}
				$results[] = ["code" => $row["iBodyType"], "name" => $name, "count" => $row["numEntries"], "id" => $row["iBodyType"]];
			}
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetBaseExperiences() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, "be"); }
			}
			$sql = "SELECT 50*FLOOR(p.iBaseExperience/50) AS baseExp, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY FLOOR(p.iBaseExperience/50)";
			$sql .= " ORDER BY p.iBaseExperience ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$baseExp = $row["baseExp"];
				$name = "$baseExp - ".($baseExp + 50);
				$results[] = ["code" => $baseExp, "name" => $name, "count" => $row["numEntries"], "id" => $baseExp];
			}
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetHpEVs() { return $this->GetEVs("iEVhp", "ehp"); }
		public function GetAtkEVs() { return $this->GetEVs("iEVatk", "eatk"); }
		public function GetDefEVs() { return $this->GetEVs("iEVdef", "edef"); }
		public function GetSpAtkEVs() { return $this->GetEVs("iEVspatk", "esatk"); }
		public function GetSpDefEVs() { return $this->GetEVs("iEVspdef", "esdef"); }
		public function GetSpdEVs() { return $this->GetEVs("iEVspd", "espd"); }
		private function GetEVs($ev, $f) {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, $f); }
			}
			$sql = "SELECT p.$ev, COUNT(DISTINCT p.cID) AS numEntries FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY p.$ev";
			$sql .= " ORDER BY p.$ev ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$name = $row["$ev"];
				$results[] = ["code" => $name, "name" => $name, "count" => $row["numEntries"], "id" => $name];
			}
			echo json_encode(["success" => true, "result" => $results]);
		}
		
		private function GetPokeQuery($whereQuery) {
			$sql = "SELECT p.cID, p.sName, p.iGeneration, p.cEvolvesFrom, p.sDesc, pc.sName AS childName, p.cEvolvesInto, pp.sName AS parentName";
			$sql .= ", (SELECT GROUP_CONCAT(DISTINCT ti.sName) FROM test_pokemontypes ti INNER JOIN test_pokemontypexref xi ON xi.cType = ti.cID INNER JOIN test_pokemon pi ON xi.cPokemon = pi.cID WHERE pi.cID = p.cID) AS sTypes";
			$sql .= " FROM test_pokemon p";
			$sql .= " INNER JOIN test_pokemontypexref tx ON p.cID = tx.cPokemon INNER JOIN test_pokemontypes t ON tx.cType = t.cID";
			$sql .= " INNER JOIN test_pokemoneggxref ex ON p.cID = ex.cPokemon INNER JOIN test_pokemonegggroups e ON ex.cEgg = e.cID";
			$sql .= " LEFT JOIN test_pokemon pc ON p.cEvolvesFrom = pc.cID";
			$sql .= " LEFT JOIN test_pokemon pp ON p.cEvolvesInto = pp.cID";
			$sql .= " WHERE ".$whereQuery;
			$sql .= " GROUP BY p.cID";
			$sql .= " ORDER BY p.sName";
			return $sql;
		}
		private function DataTableToWordArray($q) {
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$res = [
					"name" => $row["sName"],
					"id" => $row["cID"],
					"types" => $row["sTypes"],
					"desc" => $row["sDesc"]
				];
				if($row["cEvolvesFrom"] !== null) { $res["childID"] = $row["cEvolvesFrom"]; }
				if($row["childName"] !== null) { $res["childName"] = $row["childName"]; }
				if($row["cEvolvesInto"] !== null) { $res["parentID"] = $row["cEvolvesInto"]; }
				if($row["parentName"] !== null) { $res["parentName"] = $row["parentName"]; }
				$results[] = $res;
			}
			return $results;
		}
		private function GetWhereQueryFromJSON($filterData, $ignoreColumn = "") {
			$sqlParameters = []; $sqlStrings = []; $sqlStringData = [];
			foreach($filterData as $filter) {
				$key = $filter["key"];
				if($ignoreColumn == $key) { continue; }
				if($key == "search") {
					$sqlParameters["search"] = $filter["value"]."%";
					$sqlParameters["searchpartial"] = "%".$filter["value"]."%";
					$sqlStrings["search"] = "(p.sName LIKE :search OR p.sDesc LIKE :searchpartial)";
				} else {
					$isSet = isset($sqlStringData[$key]);
					$sqlKey = $isSet ? ":$key".count($sqlStringData[$key]) : ":$key"."0";
					$query = "$key.sName = $sqlKey";
					switch($key) {
						case "g": $query = "p.iGeneration = $sqlKey"; break;
						case "s": $query = "p.iStage = $sqlKey"; break;
						case "b": $query = "p.iBodyType = $sqlKey"; break;
						case "be": $query = "p.iBaseExperience BETWEEN $sqlKey AND $sqlKey + 50"; break;
						case "ehp": $query = "p.iEVhp = $sqlKey"; break;
						case "eatk": $query = "p.iEVatk = $sqlKey"; break;
						case "edef": $query = "p.iEVdef = $sqlKey"; break;
						case "esatk": $query = "p.iEVspatk = $sqlKey"; break;
						case "esdef": $query = "p.iEVspdef = $sqlKey"; break;
						case "espd": $query = "p.iEVspd = $sqlKey"; break;
					}
					if($isSet) {
						$i = count($sqlStringData[$key]);
						$sqlParameters["$key$i"] = $filter["value"];
						$sqlStringData[$key][] = $query;
					} else {
						$sqlParameters[$key."0"] = $filter["value"];
						$sqlStringData[$key] = [$query];
					}
				}
			}
			foreach($sqlStringData as $k => $v) { $sqlStrings[$k] = "(".implode(" OR ", $v).")"; }
			return ["sql" => implode(" AND ", $sqlStrings), "params" => $sqlParameters];
		}
	}
	$ws = new WebServiceMethods();
	$m = [$ws, $_GET["function"]];
	$callable_name = "";
	if(is_callable($m, false, $callable_name)) {
		$len = strlen("WebServiceMethods::");
		if(substr($callable_name, 0, $len) === "WebServiceMethods::") {
			if($_SERVER["REQUEST_METHOD"] === 'POST') {
				call_user_func($m);
			} else {
				$params = [];
				$pos = strpos($_SERVER["QUERY_STRING"], "&");
				if($pos !== false) { $params = explode("/", substr($_SERVER["QUERY_STRING"], $pos + 1)); }
				call_user_func_array($m, $params);
			}
			return;
		}
	}
	echo "{\"success\": false}";
?>