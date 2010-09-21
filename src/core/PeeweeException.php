<?php
class PeeweeException extends Exception{
	public function __construct($message = null, $params=null, $previous=null, $code=0){
		$objectMessages = "";
		if(!empty($params)){
			$objectMessages .= var_export($params, true);
		}

		$errorMessage = $message;
		if(!empty($objectMessages)){
			$errorMessage .= "\r\n" . $objectMessages . "\r\n";
		}

		parent::__construct($errorMessage, $code, $previous);
	}
}
?>