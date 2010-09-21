<?php
require_once "Dao.php";
require_once "PeeweeException.php";

class Peewee{
	private $pdo;

	protected function __construct($connectConfig){
		try {
			$dbHost = $connectConfig{"host"};
			$dbDatabase = $connectConfig{"database"};
			$dbUser = $connectConfig{"user"};
			$dbPassword = $connectConfig{"password"};
			$pdo = new PDO("mysql:host={$dbHost}; dbname={$dbDatabase}", $dbUser, $dbPassword);
			
			$characterSet = $connectConfig{"character-set"};
			if(!empty($characterSet)){
				$pdo -> query("SET NAMES {$characterSet};");
			}
			$this->pdo = $pdo;
		} catch(PDOException $e){
			$pdo = null;
			throw new PeeweeException("データベース接続に失敗しました", array("host"=>$dbHost, "db"=>$dbDatabase, "user"=>$dbUser, "password"=>"[display:none]"), $e);
		}
	}

	public function create($connectConfig){
		return new Peewee($connectConfig);
	}

	public function createDao($tableName){
		if(!$this->pdoReady()){
			throw new PeeweeException("接続が初期化されていません");
		}
		if(empty($tableName)){
			throw new PeeweeException("テーブル名が指定されていません。");
		}

		$dao = Dao::create($this->pdo, $tableName);

		return $dao;
	}

	public function destroy(){
		$this->pdo = null;
	}

	private function pdoReady(){
		if(!isset($this->pdo)){
			return false;
		}

		return true;
	}
}
?>