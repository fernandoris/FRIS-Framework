<?php
/**
 * FRIS Framework
 * Framework loader - actúa como un único punto de acceso al framework
 *
 * @version 0.1
 * @author Fernando Rodríguez-Izquierdo Serrano
 */
header('Content-Type: text/html; charset=utf-8');
/**
 * Magic autoload function
 * se usa para incluir los controladores apropiados cuando se necesitan.
 * @param String El nombre de la clase
 */
function miAutoload($class_name) {
	if(file_exists('controllers/' . $class_name . '.php')){
		require_once 'controllers/' . $class_name . '.php';
	}else if(file_exists('models/' . strtolower($class_name) . 'Model.php')){			
		require_once 'models/'. strtolower($class_name) .'Model.php';
	}else echo "No se puede cargar la clase ".$class_name;
}
spl_autoload_register("miAutoload");

require_once ('FRISFrameworkRegistro/registro.class.php');
require_once('config.php');
session_name('FRISFW');
$session_expiration = time() + 3600 * HOURS_SESSION; 
session_set_cookie_params($session_expiration);
session_start();

try{
	
	$controladorPrincipal = new mainController($_POST,$_GET);
	//echo FRIS::getFrameworkName();
	
}catch (Exception $e){
	
	echo FRIS::trataExcepcion($e);
	
}

exit();
?>
