<?php
require_once "Dto.php";

class Dao{
	const TYPE_INT = 1;
	const TYPE_DATETIME = 2;
	const TYPE_DATE = 3;
	const TYPE_STR = 4;

	private $pdo;
	private $tableName;
	private $desc;

	protected function __construct($pdo, $tableName){
		if(empty($pdo)){
			throw new Exception("接続が初期化されていません。");
		}

		if(empty($tableName)){
			throw new Exception("テーブル名が指定されていません。");
		}

		$this->pdo = $pdo;
		$this->tableName = $tableName;

		// テーブル定義の取得
		// TODO キャッシュ
		$sql = "DESC {$this->tableName}";
		$stmt = $this->executeQuery($sql);

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$Dto = new Dto($this->tableName."_DESC");
			foreach($row as $columnKey=>$columnValue){
				$Dto->$columnKey = $columnValue;
			}
			$desc[] = $Dto;
		}
		$this->desc = $desc;
	}

	public function getTableName(){
		return $this->tableName;
	}

	public function create($pdo, $tableName){
		return new Dao($pdo, $tableName);
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

	public function getCount($conditions=array()){
		if(!$this->pdoReady()){
			throw new Exception("接続が初期化されていません");
		}
		if(empty($this->tableName)){
			throw new Exception("テーブル名を指定してください");
		}

		if($conditions == null){
			$conditions = array();
		}

		$sql = "select count(1) as cnt from " . $this->tableName;

		if(!empty($conditions)){
			$sql .= " where ";
			$index = 0;
			foreach($conditions as $key=>$value){
				if($index != 0){
					$sql .= " and ";
				}
				$sql .= $key . "= :{$key} ";
				$index++;
			}
		}

		$stmt = $this->pdo->prepare($sql);
		foreach($conditions as $key=>$value){
			$stmt->bindValue(":" . $key, $value);
		}
		$result = $stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			foreach($row as $columnKey=>$columnValue){
				return $columnValue;
			}
		}
		return 0;
	}

	public function search($conditions=array(), $page=array(), $relations=array()){
		if(!$this->pdoReady()){
			throw new Exception("接続が初期化されていません");
		}
		if(empty($this->tableName)){
			throw new Exception("テーブル名を指定してください");
		}

		if($conditions == null){
			$conditions = array();
		}

		if($page == null){
			$page = array();
		}

		if($relations == null){
			$relations = array();
		}

		$sql = "select * from " . $this->tableName;

		if(!empty($conditions)){
			$sql .= " where ";

			$index = 0;
			foreach($conditions as $key=>$value){
				if($index != 0){
					$sql .= " and ";
				}
				$sql .= $key . "= :{$key} ";
				$index++;
			}
		}

		$pageNumber = $page{"page"};
		$pageCount = $page{"count"};
		if(empty($pageNumber) || empty($pageCount)){

		}else{
			$offset = ($pageNumber-1)*$pageCount;
			$sql .= " limit " . $offset . " , " . $pageCount;
		}

		$stmt = $this->pdo->prepare($sql);
		foreach($conditions as $key=>$value){
			$stmt->bindValue(":" . $key, $value);
		}
		$result = $stmt->execute();
		$resultArray = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$Dto = new Dto($this->tableName);
			foreach($row as $columnKey=>$columnValue){
				$Dto->$columnKey = $this->convertTypeMismatch($columnKey, $columnValue);
			}
			$resultArray[] = $Dto;
		}

		if(!empty($relations)){

			for($i = 0; $i < count($resultArray); $i++){
				$result = $resultArray[$i];
				foreach($relations as $relationTableName=>$relationKeys){

					$relationParams = array();

					foreach($relationKeys as $relationKey=>$foreignKey){
						$relationParams{$foreignKey} = $result->$relationKey;
					}

					$relationDao = Dao::create($this->pdo, $relationTableName);
					$relationList = $relationDao->search($relationParams);

					$result->$relationTableName = $relationList;
				}

				$resultArray[$i] = $result;
			}
		}

		return $resultArray;
	}

	public function find($id){

		if(!$this->pdoReady()){
			throw new PeeweeException("接続が初期化されていません");
		}
		if(empty($this->tableName)){
			throw new PeeweeException("テーブル名を指定してください");
		}
		if(empty($id)){
			throw new PeeweeException("IDが指定されていません");
		}
		if(!is_numeric($id)){
			throw new PeeweeException("IDが不正です。", $id);
		}

		$sql = "select * from " . $this->tableName . " where id = :ID";

		$stmt = $this->pdo->prepare($sql);
		$stmt->bindValue(":ID", $id, PDO::PARAM_INT);

		$result = $stmt->execute();

		if($result){
			$Dto = new Dto($this->tableName);
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach($row as $columnKey=>$columnValue){
					$Dto->$columnKey = $this->convertTypeMismatch($columnKey, $columnValue);
				}
				return $Dto;
			}
			return null;
		}else{
			return null;
		}
	}

	public function update(Dto $dto, $updateKey=array()){
		$sql = "update ";
		$sql .= $this->tableName;
		$sql .= " set ";

		$parameters = $dto->getParameters();
		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey)){
				continue;
			}

			if($index > 0){
				$sql .= ", ";
			}
			$sql .= $parameterKey . "=:" . $parameterKey;
			$index++;
		}

		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if(!$this->isPrimaryKey($parameterKey)){
				continue;
			}

			if($index == 0){
				$sql .= " where ";
			}else{
				$sql .= " and ";
			}

			$sql .= $parameterKey . "=:" . $parameterKey;
			$index++;
		}

		$stmt = $this->pdo->prepare($sql);

		// bind
		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			$type = $this->getColumnType($parameterKey);
			switch ($type) {
				case Dao::TYPE_INT:
					$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_INT);
					break;

				case Dao::TYPE_DATETIME:
					if(getType($parameterValue) == "integer"){
						$dateTimeStr = date('Y-m-d H:i:s', $parameterValue);
					}

					if(!$dateTimeStr || empty($dateTimeStr)){
						$dateTimeStr = $parameterValue;
					}

					$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
					break;

				case Dao::TYPE_DATE:
					if(getType($parameterValue) == "integer"){
						$dateTimeStr = date('Y-m-d', $parameterValue);
					}

					if(!$dateTimeStr || empty($dateTimeStr)){
						$dateTimeStr = $parameterValue;
					}

					$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
					break;

				default:
					$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_STR);
			}
		}

		$result = $this->executeStatement($stmt);

		return $result;

	}

	public function insert(Dto $dto){
		$sql = "insert into ";
		$sql .= $this->tableName;
		$sql .= "(";

		$parameters = $dto->getParameters();
		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey) && $this->isAutoIncrement($parameterKey) && empty($parameterValue)){
				continue;
			}

			if($index > 0){
				$sql .= ",";
			}
			$sql .= $parameterKey;
			$index++;
		}

		$sql .= ")values(";

		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey) && $this->isAutoIncrement($parameterKey) && empty($parameterValue)){
				continue;
			}

			if($index > 0){
				$sql .= ",";
			}

			$sql .= ":" . $parameterKey;
			$index++;
		}

		$sql .= ")";

		$stmt = $this->pdo->prepare($sql);

		// bind
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey) && $this->isAutoIncrement($parameterKey) && empty($parameterValue)){
				continue;
			}

			if($parameterValue == null){
				$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_NULL);
			}

			$type = $this->getColumnType($parameterKey);
			switch ($type) {
				case Dao::TYPE_INT:
					$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_INT);
					break;

				case Dao::TYPE_DATETIME:
					if(getType($parameterValue) == "integer"){
						$dateTimeStr = date('Y-m-d H:i:s', $parameterValue);
					}

					if(!$dateTimeStr || empty($dateTimeStr)){
						$dateTimeStr = $parameterValue;
					}

					$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
					break;

				case Dao::TYPE_DATE:
					if(getType($parameterValue) == "integer"){
						$dateTimeStr = date('Y-m-d', $parameterValue);
					}

					if(!$dateTimeStr || empty($dateTimeStr)){
						$dateTimeStr = $parameterValue;
					}

					$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
					break;

				default:
					if($parameterValue == null){
						$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_NULL);
					}else{
						$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_STR);
					}
			}
		}

		$result = $this->executeStatement($stmt);

		return $result;
	}

	public function delete(Dto $dto){
		$sql = " delete from ";
		$sql .= $this->tableName;

		$parameters = $dto->getParameters();
		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey)){
				if($index == 0){
					$sql .= " where ";
				}else{
					$sql .= " and ";
				}

				$sql .= $parameterKey . "=:" . $parameterKey;
				$index++;
					
			}
		}

		$stmt = $this->pdo->prepare($sql);

		// bind
		$index = 0;
		foreach($parameters as $parameterKey=>$parameterValue){
			if($this->isPrimaryKey($parameterKey)){
					
				$type = $this->getColumnType($parameterKey);
				switch ($type) {
					case Dao::TYPE_INT:
						$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_INT);
						break;

					case Dao::TYPE_DATETIME:
						if(getType($parameterValue) == "integer"){
							$dateTimeStr = date('Y-m-d H:i:s', $parameterValue);
						}

						if(!$dateTimeStr || empty($dateTimeStr)){
							$dateTimeStr = $parameterValue;
						}

						$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
						break;

					case Dao::TYPE_DATE:
						if(getType($parameterValue) == "integer"){
							$dateTimeStr = date('Y-m-d', $parameterValue);
						}

						if(!$dateTimeStr || empty($dateTimeStr)){
							$dateTimeStr = $parameterValue;
						}

						$stmt->bindValue(":".$parameterKey, $dateTimeStr, PDO::PARAM_STR);
						break;

					default:
						$stmt->bindValue(":".$parameterKey, $parameterValue, PDO::PARAM_STR);
				}
			}
		}

		$result = $this->executeStatement($stmt);

		return $result;

	}

	private function executeQuery($sql){
		$stmt = $this->pdo->query($sql);

		if($this->pdo->errorCode() != "00000"){
			throw new PeeweeException(null, $this->pdo->errorInfo());
		}

		return $stmt;
	}

	private function executeStatement($stmt){
		$result = $stmt->execute();

		if($stmt->errorCode() != "00000"){
			throw new PeeweeException(null, $stmt->errorInfo());
		}

		return $result;
	}

	public function truncate(){
		$sql = "truncate table {$this->tableName}";
		$this->executeQuery($sql);
	}

	private function isPrimaryKey($columnName){
		foreach($this->desc as $columnInfomation){
			if($columnInfomation->Field == $columnName){
				if($columnInfomation->Key == "PRI"){
					return true;
				}
			}
		}
		return false;
	}

	private function isAutoIncrement($columnName){
		foreach($this->desc as $columnInfomation){
			if($columnInfomation->Field == $columnName){
				if($columnInfomation->Extra == "auto_increment"){
					return true;
				}
			}
		}
		return false;
	}

	private function getColumnType($columnName){
		foreach($this->desc as $columnInfomation){
			if($columnInfomation->Field == $columnName){
				$type = $columnInfomation->Type;
				if($this->startWith($type, "int")){
					return Dao::TYPE_INT;
				}else if($type == "datetime"){
					return Dao::TYPE_DATETIME;
				}else if($type == "date"){
					return Dao::TYPE_DATE;
				}else{
					return Dao::TYPE_STR;
				}
					
			}
		}
	}

	private function startWith($haystack, $needle){
		return strpos($haystack, $needle, 0) === 0;
	}

	private function convertTypeMismatch($key, $value){
		$type = $this->getColumnType($key);
		switch ($type) {
			case Dao::TYPE_INT:
				if($value === null){
					return null;
				}

				return (int)$value;

			case Dao::TYPE_DATETIME:
				if($value === null){
					return null;
				}

				return strtotime($value);

			case Dao::TYPE_DATE:
				if($value === null){
					return null;
				}

				return strtotime($value);
					
			default:
				return $value;
		}
	}

	public function createDto(){
		return new Dto($this->tableName);
	}
}
?>