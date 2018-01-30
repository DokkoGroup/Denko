<?php

require_once dirname(__FILE__).'/../denko/dk.denko.php';

class JsonDB {

	///////////////////////////////////////////////////////////////////////////
	
	private static $CACHE_JSONDB = array();

	///////////////////////////////////////////////////////////////////////////

	private static function &getJsonDB($name){
		if(isset(self::$CACHE_JSONDB[$name])) return self::$CACHE_JSONDB[$name];
		$fName = dirname(__FILE__).'/../jsonDB/'.$name;
		if(!file_exists($fName)) $fName=$fName.'.json';
		if(!file_exists($fName)) {
			throw new Exception("Can't find jsonDB file ".$name, 1);
		}
		@$data = json_decode(file_get_contents($fName),true);
		if(empty($data)) $data = array();
		self::$CACHE_JSONDB[$name] = $data;
		return $data;
	}

	///////////////////////////////////////////////////////////////////////////

	public static function getByID($dbName, $id) {
		$db = self::getJsonDB($dbName);
		if(!isset($db[$id])) return null;
		return $db[$id];
	}

	///////////////////////////////////////////////////////////////////////////

	public static function getFullDB($dbName) {
		return self::getJsonDB($dbName);
	}

	///////////////////////////////////////////////////////////////////////////

	public static function getOneFieldFromDB($dbName,$field) {
		$key = $dbName.'->'.$field;
		if(isset(self::$CACHE_JSONDB[$key])) return self::$CACHE_JSONDB[$key];
		$res = array();
		foreach(self::getJsonDB($dbName) as $k => $v) {
			if(isset($v[$field])) $res[$k] = $v[$field];
		}
		self::$CACHE_JSONDB[$key] = $res;
		return $res;
	}

	///////////////////////////////////////////////////////////////////////////

	public static function __callStatic($name, $arguments) {
		$aux = explode('_',$name,2);
		if(count($aux)!=2 || ($aux[0]!='getFull' && $aux[0]!='getField')) {
			throw new Exception("Call to unkown function JsonDB::$name", 1);
		}
		if(count($arguments)>0) {
			throw new Exception("JsonDB::$name requires no arguments", 1);
		}
		if($aux[0]=='getFull') return self::getJsonDB($aux[1]);
		$aux = explode('__',$aux[1],2);
		if(count($aux)!=2) throw new Exception("Call to unkown function JsonDB::$name", 1);
		return self::getOneFieldFromDB($aux[0],$aux[1]);
	}

	///////////////////////////////////////////////////////////////////////////

}