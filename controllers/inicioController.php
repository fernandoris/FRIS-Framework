<?php
class inicioController {
	
	private static $instance;

	public static function getInstancia() {
		if (!isset(self::$instance)) {
			$obj = __CLASS__;
			self::$instance = new $obj();
		}

		return self::$instance;
	}	

	public function __construct() {

		$fris = FRIS::getInstancia();			
		
		
		
			
		$fris -> buildFromTemplates('inicio');
			
		
		
        $fris -> parseOutput();
		print $fris -> getContent();
		
	}

}
?>