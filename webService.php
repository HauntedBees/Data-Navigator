<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json");
	if(!isset($_GET["function"])) { echo "{\"success\": false}"; exit; }
	class WebServiceMethods {
		private $data;
		public function __construct() { 
			$this->data = [
				["id" => 0, "name" => "index", "type" => "html", "size" => 2], 
				["id" => 1, "name" => "webservice", "type" => "php", "size" => 7], 
				["id" => 2, "name" => "navigator", "type" => "js", "size" => 13], 
				["id" => 3, "name" => "examples", "type" => "folder", "size" => 0], 
				["id" => 4, "name" => "example-linguistics", "type" => "html", "size" => 2, "source" => 2, "parentId" => 3], 
				["id" => 5, "name" => "example-pokemon", "type" => "html", "size" => 2, "source" => 1, "parentId" => 3], 
				["id" => 6, "name" => "example-linguistics", "type" => "js", "size" => 15, "source" => 2, "parentId" => 3], 
				["id" => 7, "name" => "example-pokemon", "type" => "js", "size" => 15, "source" => 1, "parentId" => 3], 
				["id" => 8, "name" => "example-linguistics", "type" => "php", "size" => 9, "source" => 2, "parentId" => 3], 
				["id" => 9, "name" => "example-pokemon", "type" => "php", "size" => 16, "source" => 1, "parentId" => 3], 
				["id" => 10, "name" => "styles", "type" => "css", "size" => 2, "notes" => "shared by all examples"]
			];
		}
		public function Fail() { echo "{\"success\": false}"; exit; }
		public function Test() { echo json_encode(["success" => true, "args" => func_get_args()]); }
		
		public function GetTypes() {
			$filterData = json_decode($_POST["filters"], true); 
			$parsedFilterData = $this->GetAcceptableValues($filterData);
			$results = []; $i = 0;
			foreach($this->data as $item) {
				$t = $item["type"];
				if(!$this->IsAcceptable($parsedFilterData, $item, "type")) { continue; }
				if(isset($results[$t])) { $results[$t]["count"]++; }
				else { $results[$t] = ["code" => $t, "name" => $t, "count" => 1, "id" => $i++]; }
			}
			$actualResults = [];
			echo json_encode(["success" => true, "result" => array_values($results)]);
		}
		public function GetSizes() {
			$filterData = json_decode($_POST["filters"], true);
			$parsedFilterData = $this->GetAcceptableValues($filterData);
			$results = []; $i = 0;
			foreach($this->data as $item) {
				if(!$this->IsAcceptable($parsedFilterData, $item, "size")) { continue; }
				$sSanitized = floor($item["size"] / 5) * 5;
				if(isset($results[$sSanitized])) { $results[$sSanitized]["count"]++; }
				else { $results[$sSanitized] = ["code" => $sSanitized, "name" => $sSanitized."KB - ".($sSanitized + 5)."KB", "count" => 1, "id" => $i++]; }
			}
			$actualResults = [];
			echo json_encode(["success" => true, "result" => array_values($results)]);
		}
		public function GetSources() {
			echo json_encode(["success" => true, "result" => [["info" => "Pok%C3%A9mon Data gathered from Bulbapedia"], ["info" => "The World Sexual Terminology Resource uses many sources; they can all be found at http://hauntedbees.com/ling/index.html"]]]);
		}
		
		public function GetContent() {
			if(!isset($_POST["filters"])) { $this->Fail(); }
			$filterData = json_decode($_POST["filters"], true); 
			if($filterData == null || count($filterData) == 0) { $this->Fail(); }
			$parsedFilterData = $this->GetAcceptableValues($filterData);
			$results = [];
			foreach($this->data as $item) {
				$t = $item["type"]; $s = $item["size"]; $i = $item["id"];
				if(!$this->IsAcceptable($parsedFilterData, $item)) { continue; }
				$res = ["id" => $i, "name" => $item["name"], "type" => $t, "size" => $s];
				if(isset($item["notes"])) { $res["notes"] = $item["notes"]; }
				if(isset($item["source"])) { $res["source"] = $item["source"]; }
				if(isset($item["parentId"])) { $res["parentId"] = $item["parentId"]; }
				$results[] = $res;
			}
			$actualResults = [];
			echo json_encode(["success" => true, "result" => $results]);
		}
		public function GetSingle($id) {
			if(!isset($id)) { $this->Fail(); }
			$id = intval($id);
			if($id <= 0) { $this->Fail(); }
			$results = [];
			foreach($this->data as $item) {
				if($item["id"] == $id) { $results = $item; break; }
				continue;
			}
			echo json_encode(["success" => true, "result" => [$results]]);
		}
		
		private function GetAcceptableValues($filterData) {
			if($filterData == null || count($filterData) == 0) { return null; }
			$filters = [];
			foreach($filterData as $filter) {
				$filterKey = $filter["key"]; $filterValue = $filter["value"];
				if(!isset($filters[$filterKey])) { $filters[$filterKey] = []; }
				$filters[$filterKey][] = $filterValue;
			}
			return $filters;
		}
		private function IsAcceptable($parsedFilterData, $value, $ignore = "") {
			if($parsedFilterData == null) { return true; }
			foreach($parsedFilterData as $key => $filters) {
				if($key == $ignore) { continue; }
				$val = $value[$key];
				if($key == "search") {
					$filter = strtolower($filters[0]);
					if(strpos(strtolower($value["name"]), $filter) === false) { return false; }
				} else {
					if($key == "size") { $val = floor($val / 5) * 5; }
					if(!in_array($val, $filters)) { return false; }
				}
			}
			return true;
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