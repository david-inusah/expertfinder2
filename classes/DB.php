<?php
include_once("Settings.php");

class DB{
	public static function connect(){
		$pdo = new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8',''.DBUSERNAME.'',''.DBPASSWORD.'');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $pdo;
	}
	public static function query($query, $params=array()){
		$statement = self::connect()->prepare($query);
		$statement-> execute($params);

		if (explode(' ', $query)[0]== 'SELECT'){
			$data = $statement->fetchAll();
			return $data;
		}
	}
}

?>