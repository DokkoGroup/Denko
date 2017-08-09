<?php

// Denko Internationalization Manager

class i10n {
	public static $current;
	public static $currentLang;
	public static $cache;

	public static function setLang($lang,$context="main",$folder="./i10n"){
		self::$currentLang = $lang;
		if(!isset(self::$cache)) self::$cache=array();

		if(isset(self::$cache[$lang][$context])) {
			self::$current = &self::$cache[$lang][$context];
			return true;
		}

		$file = $folder.'/'.$lang.'/'.$context.'.lang';
		if(!file_exists($file)){
			self::$cache[$lang][$context] = array();
			self::$current = &self::$cache[$lang][$context];
			return false;
		}

		$_ = array();
		require $file;
		self::$cache[$lang][$context] = &$_;
		self::$current = &self::$cache[$lang][$context];
		return true;
	}
}

function _t($msg, $p1=null, $p2=null, $p3=null, $p4=null, $p5=null){
	if(isset(i10n::$current[$msg])) {
		$msg = i10n::$current[$msg];
	}else{
		// echo "String not found: ".$msg; exit;
	}
	if($p1!=null) return sprintf($msg, $p1, $p2, $p3, $p4, $p5);
	return $msg;
}

function _jst($msg, $p1=null, $p2=null, $p3=null, $p4=null, $p5=null){
	$msg = _t($msg, $p1, $p2, $p3, $p4, $p5);
	return '`'.str_replace('`','\\`',$msg).'`';
}

function _tc($msg, $context, $p1=null, $p2=null, $p3=null, $p4=null, $p5=null){
	if(isset(i10n::$cache[i10n::$currentLang][$msg])) {
		$msg = i10n::$cache[i10n::$currentLang][$msg];
		// echo "String not found: ".$msg; exit;
	}
	if($p1!=null) return sprintf($msg, $p1, $p2, $p3, $p4, $p5);
	return $msg;
}
