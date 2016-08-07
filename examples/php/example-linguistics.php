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
		
		public function WordSearchFromJSON() {
			if(!isset($_POST["filters"])) { $this->Fail(); }
			$filterData = json_decode($_POST["filters"], true);
			if($filterData == null || count($filterData) == 0) { $this->Fail(); }
			$sqlWhereData = $this->GetWhereQueryFromJSON($filterData);
			$q = $this->pdo->prepare($this->GetWordQuery($sqlWhereData["sql"]));
			$q->execute($sqlWhereData["params"]);
			$results = $this->DataTableToWordArray($q);
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetSingleWord($wordID) {
			if(!isset($wordID)) { $this->Fail(); }
			$id = intval($wordID);
			if($id <= 0) { $this->Fail(); }
			$q = $this->pdo->prepare($this->GetWordQuery(" w.cID = :word"));
			$q->execute(["word" => $id]);
			$results = $this->DataTableToWordArray($q);
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetSources() {
			$q = $this->pdo->prepare("SELECT sTitle, sUrl, sEditor, CASE WHEN dPublished IS NULL THEN sPublished ELSE DATE_FORMAT(dPublished, '%M %e, %Y') END AS finalPub, DATE_FORMAT(dAccessed, '%M %e, %Y') AS finalAcc FROM ling_sources ORDER BY cID ASC");
			$q->execute();
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["title" => $row["sTitle"], "url" => $row["sUrl"], "editor" => $row["sEditor"], "published" => $row["finalPub"], "accessed" => $row["finalAcc"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetLanguages() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, true); }
			}
			$sql = "SELECT l.cID, l.sCode, l.sName, COUNT(DISTINCT w.cID) AS numEntries FROM ling_languages l";
			$sql .= " INNER JOIN ling_wordLangXref x ON l.cID = x.cLanguage INNER JOIN ling_words w ON x.cWord = w.cID";
			$sql .= " LEFT JOIN ling_wordsKeywordsXref kx ON w.cID = kx.cWord LEFT JOIN ling_keywords k ON kx.cKeyword = k.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY l.cID";
			$sql .= " ORDER BY l.sName ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["code" => $row["sCode"], "name" => $row["sName"], "count" => $row["numEntries"], "id" => $row["cID"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetTags() {
			$sqlWhereData = null;
			if(isset($_POST["filters"])) {
				$filterData = json_decode($_POST["filters"], true);
				if($filterData != null && count($filterData) > 0) { $sqlWhereData = $this->GetWhereQueryFromJSON($filterData, false, true); }
			}
			$sql = "SELECT DISTINCT k.sValue, k.cID, COUNT(w.cID) AS numEntries FROM ling_keywords k";
			$sql .= " INNER JOIN ling_wordsKeywordsXref kx ON k.cID = kx.cKeyword INNER JOIN ling_words w ON kx.cWord = w.cID";
			$sql .= " INNER JOIN ling_wordLangXref x ON w.cID = x.cWord INNER JOIN ling_languages l ON x.cLanguage = l.cID";
			if($sqlWhereData != null && $sqlWhereData["sql"] != "") { $sql .= " WHERE ".$sqlWhereData["sql"]; }
			$sql .= " GROUP BY k.cID";
			$sql .= " ORDER BY k.sValue ASC";
			$q = $this->pdo->prepare($sql);
			if($sqlWhereData == null) {
				$q->execute();
			} else {
				$q->execute($sqlWhereData["params"]);
			}
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) { $results[] = ["code" => $row["sValue"], "name" => $row["sValue"], "count" => $row["numEntries"], "id" => $row["cID"]]; }
			echo json_encode(["success" => true, "result" => $results]);
		}
		private function GetWordQuery($whereQuery) {
			$sql = "SELECT w.cID, w.sWord, w.sRomanization, w.sSimpleRomanization, w.sLiteralMeaning, w.sEtymology, w.sNote, GROUP_CONCAT(DISTINCT xx.cSource) AS sSources, w.cParent, wp.sWord AS parentWord, wp.sRomanization AS parentRoman";
			$sql .= ", (SELECT GROUP_CONCAT(DISTINCT li.sName) FROM ling_languages li INNER JOIN ling_wordLangXref xi ON xi.cLanguage = li.cID INNER JOIN ling_words wi ON xi.cWord = wi.cID WHERE wi.cID = w.cID) AS sLanguages";
			$sql .= " FROM ling_words w INNER JOIN ling_wordLangXref x ON w.cID = x.cWord INNER JOIN ling_languages l ON x.cLanguage = l.cID LEFT JOIN ling_words wp ON w.cParent = wp.cID";
			$sql .= " LEFT JOIN ling_wordSourceXref xx ON xx.cWord = w.cID LEFT JOIN ling_wordsKeywordsXref kx ON kx.cWord = w.cID LEFT JOIN ling_keywords k ON kx.cKeyword = k.cID WHERE";
			$sql .= $whereQuery;
			$sql .= " GROUP BY w.cID";
			$sql .= " ORDER BY w.iType";
			return $sql;
		}
		private function DataTableToWordArray($q) {
			$results = [];
			while($row = $q->fetch(PDO::FETCH_ASSOC)) {
				$res = ["word" => $row["sWord"], "literal" => $row["sLiteralMeaning"], "id" => $row["cID"]];
				if($row["sRomanization"] !== null) { $res["romanization"] = $row["sRomanization"]; }
				if($row["sSimpleRomanization"] !== null) { $res["simple"] = $row["sSimpleRomanization"]; }
				if($row["sEtymology"] !== null) { $res["etymology"] = $row["sEtymology"]; }
				if($row["sNote"] !== null) { $res["note"] = $row["sNote"]; }
				if($row["sSources"] !== null) { $res["source"] = $row["sSources"]; }
				if($row["sLanguages"] !== null) { $res["language"] = $row["sLanguages"]; }
				if($row["cParent"] !== null) { $res["parentID"] = $row["cParent"]; }
				if($row["parentWord"] !== null) { $res["parentWord"] = $row["parentWord"]; }
				if($row["parentRoman"] !== null) { $res["parentRoman"] = $row["parentRoman"]; }
				$results[] = $res;
			}
			return $results;
		}
		private function GetWhereQueryFromJSON($filterData, $ignoreLang = false, $ignoreTag = false) {
			$sqlParameters = [];
			$langCount = 0; $tagCount = 0; $search = false;
			foreach($filterData as $filter) {
				$key = $filter["key"];
				if($key == "lang" && !$ignoreLang) {
					$sqlParameters["lang$langCount"] = $filter["value"];
					$langCount++;
				} elseif($key == "tag" && !$ignoreTag) {
					$sqlParameters["tag$tagCount"] = $filter["value"];
					$tagCount++;
				} elseif($key == "search" && !$search) {
					$sqlParameters["search"] = $filter["value"];
					$sqlParameters["searchpartial"] = "%".$filter["value"]."%";
					$search = true;
				}
			}
			$sql = "";
			if($langCount > 0 && !$ignoreLang) {
				$langs = [];
				for($i = 0; $i < $langCount; $i++) {
					$langs[] = "l.sCode = :lang$i";
				}
				$sql .= " (".implode(" OR ", $langs).")";
			}
			if($tagCount > 0 && !$ignoreTag) {
				$tags = [];
				for($i = 0; $i < $tagCount; $i++) {
					$tags[] = "k.sValue = :tag$i";
				}
				if($langCount > 0 && !$ignoreLang) { $sql .= " AND"; }
				$sql .= " (".implode(" OR ", $tags).")";
			}
			if($search) {
				if(($langCount > 0 && !$ignoreLang) || ($tagCount > 0 && !$ignoreTag)) { $sql .= " AND"; }
				$sql .= " (w.sWord LIKE :search OR w.sRomanization LIKE :search OR w.sSimpleRomanization LIKE :search OR w.sLiteralMeaning LIKE :searchpartial OR w.sEtymology LIKE :searchpartial)";
			}
			return ["sql" => $sql, "params" => $sqlParameters];
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