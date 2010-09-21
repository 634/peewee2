<?php
class Dto{
	private $tableName = null;
	private $data = array();

	public function getTableName(){
		return $this->tableName;
	}

	public function __construct($tableName){
		if(empty($tableName)){
			throw new Exception("テーブル名が指定されていません。");
		}
		$this->tableName = $tableName;
	}
	
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __get($name) {
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}else{
			throw new Exception("プロパティが設定されていません。" . $name);
		}
	}
	
	public function getParameters(){
		return $this->data;
	}

	public function __toString(){
		$str = "TableDto:";
		$str .= $this->tableName;
		if(count($this->data) > 0){
			foreach($this->data as $key=>$value){
				$str .= "(" . $key . ":" . $value . ")";
			}
		}
		return $str;
	}

}?>