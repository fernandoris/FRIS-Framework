<?php

/**
 * Clase de gesti�n y acceso a la base de datos
 * Este es un nivel de abstracci�n muy b�sico
 */
class database {

	/**
	 * Pemite acceso a varias bases de datos
	 * probablemente no se use para muchas aplicaciones, pero resulta pr�ctico.
	 */
	private $connections = array();

	/**
	 * Le dice al objeto DB que conexion usar
	 * setActiveConnection($id) permite cambiar esto.
	 */
	private $activeConnection = 0;

	/**
	 * Queries que van a ser ejecutados y luego "guardados para m�s tarde"
	 */
	private $queryCache = array();

	/**
	 * Datos que van a ser peraparados y luego "guardados para m�s tarde"
	 */
	private $dataCache = array();

	/**
	 * Ultimo query
	 */
	private $last;

	/**
	 * Constructor de la clase
	 */
	public function __construct() {

	}

	/**
	 * Crea una nueva conexion de base de datos
	 * @param String host
	 * @param String nombre de usuario
	 * @param String contraseña
	 * @param String base de datos que estamos usando
	 * @return int el id de la nueva conexion
	 */
	public function newConnection($host, $user, $password, $database) {
		$this -> connections[] = new mysqli($host, $user, $password, $database);		
		$connection_id = count($this -> connections) - 1;
		$this -> connections[$connection_id] -> set_charset(DBCHARSET);
		if (mysqli_connect_errno()) {
			trigger_error('Error connecting to host. ' . $this -> connections[$connection_id] -> error, E_USER_ERROR);
		}

		return $connection_id;
	}

	/**
	 * Cierra la conexion activa
	 * @return void
	 */
	public function closeConnection() {
		$this -> connections[$this -> activeConnection] -> close();
	}

	/**
	 * Cambia la conexión que estará activa para la proxima consulta
	 * @param int la nueva id de conexion
	 * @return void
	 */
	public function setActiveConnection(int $new) {
		$this -> activeConnection = $new;
	}

	/**
	 * Guarda un query en la cache para procesarlo más tarde
	 * @param String la cadena del query
	 * @return el puntero al query en la cache
	 */
	public function cacheQuery($queryStr) {
		if (!$result = $this -> connections[$this -> activeConnection] -> query($queryStr)) {
			trigger_error('Error executing and caching query: ' . $this -> connections[$this -> activeConnection] -> error, E_USER_ERROR);
			return -1;
		} else {
			$this -> queryCache[] = $result;
			return count($this -> queryCache) - 1;
		}
	}

	/**
	 * Devuelve el numero de filas de la cache
	 * @param int el puntero a la cache
	 * @return int numero de filas
	 */
	public function numRowsFromCache($cache_id) {
		return $this -> queryCache[$cache_id] -> num_rows;
	}

	/**
	 * Devuelve una fila de un query en cache
	 * @param int puntero al query en cache
	 * @return array la fila
	 */
	public function resultsFromCache($cache_id) {
		return $this -> queryCache[$cache_id] -> fetch_array(MYSQLI_ASSOC);
	}

	/**
	 * Guarda datos en cache para m�s tarde
	 * @param array los datos
	 * @return int el puntero a la tabla en el cache de datos
	 */
	public function cacheData($data) {
		$this -> dataCache[] = $data;
		return count($this -> dataCache) - 1;
	}

	/**
	 * Devuelve datos del cache de datos
	 * @param int el puntero a los datos en cache
	 * @return array los datos
	 */
	public function dataFromCache($cache_id) {
		return $this -> dataCache[$cache_id];
	}

	/**
	 * Borra registros de la base de datos
	 * @param String La tabla de la que se desean borrar registros
	 * @param String La condicion por la que los registros ser�n borrados
	 * @param int el numero de registros que ser�n borrados
	 * @return void
	 */
	public function deleteRecords($table, $condition, $limit) {
		$limit = ($limit == '') ? '' : ' LIMIT ' . $limit;
		$delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
		$this -> executeQuery($delete);
	}

	/**
	 * Actualiza registros en la base de datos
	 * @param String la tabla
	 * @param array de cambios campo => valor
	 * @param String la condicion
	 * @return bool
	 */
	public function updateRecords($table, $changes, $condition) {
		$table = $this->sanitizeData($table);
		$update = "UPDATE " . $table . " SET ";
		foreach ($changes as $field => $value) {
			$field = $this -> sanitizeData($field);
			$value = $this -> sanitizeData($value);
			$update .= "`" . $field . "`='{$value}',";
		}

		// quita la última coma ,
		$update = substr($update, 0, -1);
		if ($condition != '') {
			$update .= " WHERE " . $condition;
		}
		$this -> executeQuery($update);

		return true;

	}

	/**
	 * Inserta registros en la base de datos
	 * @param String la tabla de la base de datos
	 * @param array de datos a insertar clave => valor
	 * @return bool
	 */
	public function insertRecords($table, $data) {
		// configura las variables para campo y valor
		$fields = "";
		$values = "";
		$table = $this->sanitizeData($table);
		// las rellenamos
		foreach ($data as $f => $v) {
			$f = $this -> sanitizeData($f);
			$v = $this -> sanitizeData($v);
			$fields .= "`$f`,";
			$values .= (is_numeric($v) && (intval($v) == $v)) ? $v . "," : "'$v',";

		}

		// quitamos la última coma
		$fields = substr($fields, 0, -1);
		// quitamos la última coma
		$values = substr($values, 0, -1);

		$insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
		$this -> executeQuery($insert);
		return mysqli_insert_id($this -> connections[$this -> activeConnection]);
	}

	/**
	 * Ejecuta un query
	 * @param String query
	 * @return void
	 */
	public function executeQuery($queryStr) {
		$result = NULL;   
		if (!$result = $this -> connections[$this -> activeConnection] -> query($queryStr)) {
			trigger_error('Error executing query: ' . $this -> connections[$this -> activeConnection] -> error, E_USER_ERROR);
		} else {
			$this -> last = $result;
            $result = $this->getRows();
		}
        return $result;
	}
		
	/**
	 * Devuelve los registros del �ltimo query, excluyendo los almacenados en cache
	 * @return array
	 */
	public function getRows() {
		return $this -> last;
	}

	/**
	 * Devuelve el numero de filas acfectadas por el query anterior
	 * @return int el numero de filas afectadas
	 */
	public function affectedRows() {
		return $this -> connections[$this -> activeConnection] -> affected_rows;
	}

	/**
	 * Desinfecta datos
	 * @param String datos para ser desinfectados
	 * @return String datos desinfectados
	 */
	public function sanitizeData($data) {
		return $this -> connections[$this -> activeConnection] -> real_escape_string($data);
	}

	/**
	 * Deconstruye el objeto
	 * cierra todas las conexiones
	 */
	public function __deconstruct() {
		foreach ($this->connections as $connection) {
			$connection -> close();
		}
	}

}
?>