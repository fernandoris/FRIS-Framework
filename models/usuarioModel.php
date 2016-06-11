<?php
class Usuario extends Tabla {
	protected $tabla = 'usuarios';
	private $fieldId = 'idUsuario';
	

	public function __construct($id = '') {
		parent::__construct($this -> tabla, $this -> fieldId, $id);
	}

	public function loadRecord($id) {
		parent::loadRecord($this -> fieldId, $id);
	}

	public function updateRecord($changes) {
		parent::updateRecord($changes, $this -> fieldId);
	}

	public function saveChanges() {
		parent::saveChanges($this -> fieldId);
	}

	public function addRecord($datos) {
		if (array_key_exists("pass", $datos))
			$datos['pass'] = FRIS::crypt_blowfish($datos['pass']);
		parent::addRecord($datos, $this -> fieldId);
	}

	public function delRecord() {
		parent::delRecord($this -> fieldId, $this -> get($this -> fieldId));
	}

	public function set($field, $value) {
		if ($field == "pass") {
			$value = FRIS::crypt_blowfish($value);
		}
		parent::set($field, $value);
	}

	static function login($email, $pass) {
		$logueado = false;
		$registro = FRIS::getInstancia();
		$query = "SELECT idUsuario,pass FROM usuarios WHERE email = '$email' LIMIT 1";
		$result = $registro -> getObject('db') -> executeQuery($query);
		if (list($idUsuario, $passDB) = mysqli_fetch_array($result)) {
			if (crypt($pass, $passDB) == $passDB) {
				$_SESSION['user'] = new Usuario($idUsuario);
				$logueado = true;
			}
		}
		$_SESSION['intentoLogin'] = true;
		return $logueado;
	}
	
}
?>