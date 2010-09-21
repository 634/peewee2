<?php

require_once "../core/Peewee.php";

$peewee = Peewee::create(array("host"=>"localhost", "database"=>"peewee", "user"=>"root", "password"=>"", "character-set"=>"utf8"));

$empDao = $peewee->createDao("emp");
try{
	$empDao->truncate();
}catch(PeeweeException $e){
	print_r($e);
}

// insert
$dto = $empDao->createDto();
$dto->name = "emp1";
$dto->age = 33;
$dto->insertdate = time();
$dto->dept_id = null;
try{
	$result = $empDao->insert($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";

// insert exception
$dto = $empDao->createDto();
$dto->name = null;
$dto->age = 33;
$dto->insertdate = time();
$dto->dept_id = null;
try{
	$result = $empDao->insert($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";

// find and insert
$dto = $empDao->find(1);
$dto->id = null;
$dto->name = "emp2";
$dto->age = 44;
$dto->insertdate = time();
$dto->dept_id = 10001;
try{
	$result = $empDao->insert($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";


// dao create exception
try{
	$empDao2 = $peewee->createDao("emp2");
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";


// insert
for($i = 0; $i < 10; $i++){
	$dto = $empDao->createDto();
	$dto->name = "emp" . ($i + 5);
	$dto->age = $i;
	$dto->insertdate = time();
	$dto->dept_id = null;
	try{
		$result = $empDao->insert($dto);
	}catch(PeeweeException $e){
		print_r($e);
	}
	print_r($result);
	print "<hr>";
	print_r($dto);
	print "<hr>";
}

// delete
$deleteKeys = array(3, 9, 11);
foreach($deleteKeys as $deleteKey){
	$dto = $empDao->find($deleteKey);
	try{
		$result = $empDao->delete($dto);
	}catch(PeeweeException $e){
		print_r($e);
	}
	print_r($result);
	print "<hr>";
	print_r($dto);
	print "<hr>";
}

// find and update
$dto = $empDao->find(5);
$dto->name = $dto->name . ">emp15";
print_r($dto);
try{
	$result = $empDao->update($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";


// table dept
$deptDao = $peewee->createDao("dept");
try{
	$deptDao->truncate();
}catch(PeeweeException $e){
	print_r($e);
}

$dto = $deptDao->createDto();
$dto->id = 10001;
$dto->name = "dept1";
$dto->createdate = time();
try{
	$result = $deptDao->insert($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";


$dto = $empDao->find(2);
$dto->name = $dto->name . ">emp3";
try{
	$result = $empDao->update($dto);
}catch(PeeweeException $e){
	print_r($e);
}
print_r($result);
print "<hr>";
print_r($dto);
print "<hr>";

$dto = $empDao->search(array("id"=>2), null, array("dept"=>array("dept_id"=>"id")));
print_r($dto);

?>