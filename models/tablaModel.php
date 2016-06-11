<?php
class Tabla{
	
	protected $campos,$tabla,$registro,$cambios;
	
	public function __construct($tabla,$fieldId = '',$id = ''){	
		$this -> registro = FRIS::getInstancia();
		$this -> tabla = $this -> registro -> getObject('db') -> sanitizeData($tabla);
		$this -> cambios = array();
		$resultado = $this -> registro -> getObject('db') -> executeQuery("DESCRIBE ".$this->tabla);
		if(mysqli_num_rows($resultado)>0){
				while ($result = mysqli_fetch_array($resultado)) {					
					$this->campos[$result['Field']]['Type'] = $result['Type'];
					$this->campos[$result['Field']]['Null'] = $result['Null'];
					$this->campos[$result['Field']]['Key'] = $result['Key'];
					$this->campos[$result['Field']]['Default'] = $result['Default'];
					$this->campos[$result['Field']]['Extra'] = $result['Extra'];
					$this->campos[$result['Field']]['Value'] = '';
				}				
				if(($fieldId != '')&&($id != '')) self::loadRecord($fieldId,$id);
		}else throw new Exception("La tabla indicada no existe", 2);
	}
	
	public function loadRecord($fieldId,$id){		
		if(array_key_exists($fieldId, $this->campos)){
			if($this->campos[$fieldId]['Key']=='PRI'){
				if((strpos($this->campos[$fieldId]['Type'],'int')!==false)&&(!is_numeric($id))){
					throw new Exception("El id introducido no es un número en la tabla ".$this->tabla, 2);
				}else{				
					$id = $this -> registro -> getObject('db') -> sanitizeData($id);					
					$query = "SELECT * FROM ".$this->tabla." WHERE $fieldId = ".$id;
					$resultado = $this -> registro -> getObject('db') -> executeQuery($query);
					if(mysqli_num_rows($resultado)>0){
						$result = mysqli_fetch_array($resultado);
						foreach ($result as $key => $value) {
							$this->campos[$key]['Value'] = $value;
						}
					}else throw new Exception("No existe el $fieldId $id en la tabla ".$this->tabla, 2);
				}
			}else throw new Exception("El campo $fieldId no es una clave primaria ".$this->tabla, 2);
		}else throw new Exception("El campo $fieldId no existe en la tabla ".$this->tabla, 2);
	}
	
	public function updateRecord($changes,$fieldId){
		if(array_key_exists($fieldId, $this->campos)){
			if($this->compruebaArrayDatos($changes)){
				$condicion = $fieldId."=".$this->campos[$fieldId]['Value'];		
				foreach ($changes as $key => $value) {
					$this->campos[$key]['Value'] = $value;
				}					
				$this -> registro -> getObject('db') -> updateRecords($this->tabla,$changes,$condicion);
			}else throw new Exception("Los array de datos para la tabla ".$this->tabla." no es correcto",2);	
		}else throw new Exception("No existe el campo $fieldId en la tabla ".$this->tabla,2);
	}
	
	public function saveChanges($fieldId){
		if(array_key_exists($fieldId, $this->campos)){	
			if(count($this->cambios)>0){						
				$this -> updateRecord($this->cambios, $fieldId);
				$this -> cambios = array();	
			}else throw new Exception("No hay cambios que aplicar para el registro ".$this->campos[$fieldId]['Value'],2);
		}else throw new Exception("No existe el campo $fieldId en la tabla ".$this->tabla,2);
	}
	
	public function addRecord($datos,$fieldId){		
		if(array_key_exists($fieldId, $this->campos)){
			if($this->compruebaArrayDatos($datos)){
				foreach ($datos as $key => $value) {
					$this->campos[$key]['Value'] = $value;
				}				
				$this->campos[$fieldId]['Value'] = $this -> registro -> getObject('db') -> insertRecords($this -> tabla,$datos);
			}else throw new Exception("Los array de datos para la tabla ".$this->tabla." no es correcto",2);	
		}else throw new Exception("No existe el campo $fieldId en la tabla ".$this->tabla,2);	
	}
	
	public function delRecord($fieldId,$id){
		if(array_key_exists($fieldId, $this->campos)){
			$this -> registro -> getObject('db') -> deleteRecords($this -> tabla,"$fieldId = $id","1");
		}else throw new Exception("No existe el campo $fieldId en la tabla ".$this->tabla,2);	
	}
	
	public function set($field,$value){
		if(array_key_exists($field, $this->campos)){
			$this->cambios[$field] = $value;
		}else throw new Exception("El campo $field no existe en la tabla ".$this->tabla, 2);
	}
	
	public function get($field){
		if(array_key_exists($field, $this->campos)){
			$devuelve = $this->campos[$field]['Value'];	
			if(array_key_exists($field, $this->cambios)) $devuelve = $this->cambios[$field];
			return $devuelve;
		}else throw new Exception("El campo $field no existe en la tabla ".$this->tabla, 2);
	}
	
	private function compruebaArrayDatos($datos){
		$correcto = true;
		foreach ($datos as $key => $value) {			
			if(!array_key_exists($key, $this->campos)) $correcto = false;
		}
		return $correcto;
	}
}
?>