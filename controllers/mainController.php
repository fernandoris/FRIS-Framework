<?php
class mainController {

	public function __construct() {

		$_POST = $this -> sanitize($_POST);
		$_GET = $this -> sanitize($_GET);
	
		$fris = FRIS::getInstancia();
		$fris -> storeCoreObjects();
		$fris -> storeSetting(TEMPLATENAME, 'skin');
		$conexion = $fris -> getObject('db') -> newConnection(SERVIDOR, USUARIO, PASS, DATABASE);
		$mostrarPaginaPorDefecto = true;

		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'login' :
					if (!Usuario::login($_POST['usuario'], $_POST['pass']))
						unset($_SESSION['user']);
					break;
				
				default :
					echo "Acción no permitida";
					break;
			}
		}
		
		if (isset($_POST['ruta']))
			$_GET['ruta'] = $_POST['ruta'];
		if (isset($_GET['ruta']))
			$ruta = explode("/", $_GET['ruta']);
		if ((isset($ruta)) && (count($ruta) > 0)) {
			switch ($ruta[0]) {
				default :
					if ($mostrarPaginaPorDefecto)
						inicioController::getInstancia();
				break;
			}

		} else {
			if ($mostrarPaginaPorDefecto)
				inicioController::getInstancia();
		}
	}

	private function cleanInput($input) {
		$search = array('@<script [^>]*?>.*?@si', // Strip out javascript
		'@< [/!]*?[^<>]*?>@si', // Strip out HTML tags
		'@<style [^>]*?>.*?</style>@siU', // Strip style tags properly
		'@< ![sS]*?--[ tnr]*>@' // Strip multi-line comments
		);

		$output = preg_replace($search, '', $input);

		return $output;
	}

	private function sanitize($input) {
		$output = '';
		if (is_array($input)) {
			foreach ($input as $var => $val) {
				$output[$var] = $this -> sanitize($val);
			}
		} else {
			if (get_magic_quotes_gpc()) {
				$input = stripslashes($input);
			}
			$input = $this -> cleanInput($input);
			$output = $input;
		}
		return $output;
	}

}
?>