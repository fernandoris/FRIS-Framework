<?php
/**
 * El objeto FRIS
 * Implementa los patrones de diseño registro y singleton
 *
 * @version 0.1
 * @author Fernando Rodríguez-Izquierdo Serrano
 */
class FRIS {

	/**
	 * Nuestro array de objetos
	 * @access private
	 */
	private static $objects = array();

	/**
	 * Nuestro array de parámetros
	 * @access private
	 */
	private static $settings = array();

	/**
	 * El nombre del Framework para los humanos
	 * @access private
	 */
	private static $frameworkName = '<br> FRIS Framework version 0.1 <br>';

	/**
	 * La instancia de registro
	 * @access private
	 */
	private static $instance;

	/**
	 * El constructor es privado para evitar que se creen instancias directamente
	 * @access private
	 */
	private function __construct() {

	}

	/**
	 * Método singleton para obtener la instancia
	 * @access public
	 * @return
	 */
	public static function getInstancia() {
		if (!isset(self::$instance)) {
			$obj = __CLASS__;
			self::$instance = new $obj;
		}

		return self::$instance;
	}

	/**
	 * Prevenimos el clonado del objeto lanzando un error
	 */
	public function __clone() {
		trigger_error('Cloning the registry is not permitted', E_USER_ERROR);
	}

	/**
	 * Guarda un objeto en el registro
	 * @param String $object el nombre del registro
	 * @param String $key el indice para el array
	 * @return void
	 */
	public function storeObject($object, $key) {
		require_once ('objetos/' . $object . '.class.php');
		self::$objects[$key] = new $object(self::$instance);
	}

	/**
	 * Recuperando un objeto del registro
	 * @param String $key el indice del array
	 * @return object
	 */
	static function getObject($key) {
		if (is_object(self::$objects[$key])) {
			return self::$objects[$key];
		}
	}

	/**
	 * Guarda parámetros en el registro
	 * @param String $data
	 * @param String $key el indice del array
	 * @return void
	 */
	static function storeSetting($data, $key) {
		self::$settings[$key] = $data;

	}

	/**
	 * Recupera un parámetro del registro
	 * @param String $key el indice del array
	 * @return void
	 */
	static function getSetting($key) {
		return self::$settings[$key];
	}

	/**
	 * Devuelve el nombre del Framework
	 * @return String
	 */
	public function getFrameworkName() {
		return self::$frameworkName;
	}

	//Carga los objetos del núcleo del framework
	public function storeCoreObjects() {
		$this -> storeObject('database', 'db');
		$this -> storeObject('template', 'template');
	}
	
	public function addTag($tag,$texto){
		$this -> getObject('template') -> getPage() -> addTag($tag,$texto);
	}

	public function addCss($file){
		$this-> getObject('template') -> getPage() -> addCss($file);
	}
	
	public function addJs($file){
		$this->getObject('template') -> getPage() -> addJs($file);
	}
	
	public function addImg($img){
		$this->getObject('template') -> getPage() -> addImg($img);
	}
	
	public function executeQuery($query){
		return $this -> getObject('db') -> executeQuery($query);
	}
			
	public function buildFromTemplates($template){
		$this -> getObject('template') -> buildFromTemplates($template);
	}
	
	public function parseOutput(){
		$this -> getObject('template') -> parseOutput();
	}
	
	public function getContent(){
		return $this -> getObject('template') -> getPage() -> getContent();
	}	
	
	public static function trataExcepcion(Exception $e){
		$msg = 'Se ha producido una excepción. Código de excepción ';
		$msg .= $e->getCode().'<br>';
		$msg .= $e->getFile().' Linea ';
		$msg .= $e->getLine().'<br>';
		$msg .= $e->getMessage().'<br>';		
		$msg .= FRIS::dibujaTraceException($e->getTrace());
		echo $msg;
	}
	
	public static function dibujaTraceException($trace){
		$msg = '';
		if(is_array($trace)){
			$msg .= '<table border ="1">';
			foreach ($trace as $key => $value) {
				$msg .= "<tr>";
				$msg .= "<td>$key</td>";
				$msg .= "<td>".FRIS::dibujaTraceException($value)."</td>";
				$msg .= "</tr>";
			}
			$msg .= "</table>";
		}else{
			$msg .= $trace;
		}
		return $msg;
	}
	
	public static function codificaUrl($url){
    	$url = str_replace(' ','-',$url);
    	$url = str_replace('á','a',$url);
    	$url = str_replace('é','e',$url);
    	$url = str_replace('í','i',$url);
    	$url = str_replace('ó','o',$url);
    	$url = str_replace('ú','u',$url);
    	$url = str_replace('ü','u',$url);
    	$url = str_replace('ñ','n',$url);
    	$url = str_replace('%','por-ciento',$url);
    	$url = str_replace(',','',$url);
    	$url = str_replace('\'','',$url);
    	$url = str_replace('"','',$url);
    	$url = str_replace(':','',$url);
    	$url = str_replace('.','',$url);
    	return $url;
    }
    
    public static function decodificaUrl($url){
    	$url = str_replace('-',' ',$url);    	
    	return $url;
    }
	
	public static function crypt_blowfish($password, $digito = 7) {  
		$set_salt = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';  
		$salt = sprintf('$2a$%02d$', $digito);  
		for($i = 0; $i < 22; $i++){  
 			$salt .= $set_salt[mt_rand(0, 63)];  
		}  
		return crypt($password, $salt);  
	}  
	
	public static function dateToSpanish($strDate){
		$english = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
		$spanish = array("Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo");
		$strDate = str_replace($english, $spanish, $strDate);
		$english = array("January","February","March","April","May","June","July","August","September","October","November","December");
		$spanish = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$strDate = str_replace($english, $spanish, $strDate);
		return $strDate;
	}
}
?>