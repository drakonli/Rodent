<?php
class BaseModelSql extends PDO
{
	protected $table;
	protected $recieved;
	protected $query;
	
	public function __construct(){
		$config = App::get()->getSetting('sql');
		$this->setTable($this->parseTableName());
		parent::__construct(
					"mysql:host=" . $config['address'] . ";dbname=" . $config['dbName'],
					$config['username'], 
					$config['password'],
					array(PDO::ATTR_PERSISTENT => $config['persist'])
				);
	}
	
	/**
	 * Retrieves all records
	 * that are defined in an object
	 * @author Artur
	 * @version 1.0
	 * @return array
	 */
	public function findAll(){
		$data = $this->runQuery('retrieve', 'all');
		if($data){
			$data = $data->fetchAll();
			$data = $this->dataToObject($data);
		}
		return $data;
	}
	
	/**
	 * Retrieves a record by object
	 * properties
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
	public function findOne(){
		$data = $this->runQuery('retrieve');
		if($data){
			$data = $data->fetchAll();
			$data = $this->dataToObject($data);
			$data = isset($data[0]) ? $data[0] : null;
		}
		return $data;
	}
	
	/**
	 * Retrieves records by properties
	 * that are defined in an object
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
	public function find(){
		$data = $this->runQuery('retrieve');	
		if($data){
			$data = $data->fetchAll();
			$data = $this->dataToObject($data);
		}
		return $data;
	}
	
	/**
	 * Saves object properties
	 * into a record
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
	public function save(){
		$this->beforeSave();
		
		$data = false;
		if($this->recieved)
			$result = $this->runQuery('update');
		else	
			$result = $this->runQuery('create');
		
		return $result;
	}
	
	/**
	 * Removes a record
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
	public function remove(){
		$result = $this->runQuery('delete');
		return $result;
	}
	
	public function runQuery($queryType, $where = null){
		$query = $this->getQuery($queryType, $where);
		$data = $query->execute();
		
		if(!$data){
			$error = $query->errorInfo();
			throw new BaseException('SQL ERROR: ' . $error[2]);
		}
		
		return $query;
	}
	
	public function getQuery($queryType, $where){
		$methodName = 'get' . ucfirst($queryType) . 'Query';	
		return $this->$methodName($this->getProperties(), $where);
	}
	
	
	private function getDeleteQuery($properties, $where = null){
		$query = $this->prepare("DELETE FROM " . $this->table
				. " WHERE " . $properties['primary'] . " = :" . $this->$properties['primary']);
		$query->bindValue(':' . $this->$properties['primary'], $this->$properties['primary']);
		
		return $query;
	}
	
	private function getRetrieveQuery($properties, $where = null){
		$fields = " WHERE ";
		$delimiter = null;
		$counter = 0;
		
		if(isset($where) && $where == 'all'){
			$fields = null;
		}
		
		if(!$where){
			foreach($properties as $prop){
				if(isset($this->$prop)){
					if($counter == 1){
						$delimiter = " AND ";
					}
					
					$fields .= $delimiter . $prop . " = :" . $prop;
					$counter++;
				}
			}
		}

		$query = $this->prepare("SELECT * FROM " . $this->table . $fields);
		
		if(!$where){			
			foreach($properties as $prop){
				if(isset($this->$prop)){
					$query->bindValue(':' . $prop, $this->$prop);
				}
			}
		}
		
		return $query;		
	}
	
	private function getCreateQuery($properties){
		$fields = "";
		$values = "";
		$delimiter = null;
		$counter = 0;

		foreach($properties as $prop){		
			if($counter == 1){
				$delimiter = ", ";
			}
			
			$fields .= $delimiter . $prop;
			$values .= $delimiter . " :" . $prop;
			$counter++;
		} 

		$query = $this->prepare("INSERT INTO " . $this->table . " ({$fields}) VALUES ({$values})");
		
		foreach($properties as $prop){
			$query->bindValue(':' . $prop, isset($this->$prop) ? $this->$prop : NULL);
		}
		
		return $query;
	}
	
	private function getUpdateQuery($properties){
		$values = "";
		$delimiter = null;
		$counter = 0;

		foreach($properties as $prop){		
			if($counter == 1){
				$delimiter = ", ";
			}
			
			$values .= $delimiter . $prop . " = :" . $prop;
			$counter++;
		} 
		/* UPDATE table_name
		SET column1=value, column2=value2,...
		WHERE some_column=some_value */
		$query = $this->prepare("UPDATE " . $this->table . " SET " . $values . " WHERE " 
				. $properties['primary'] . " = :" . $properties['primary']);
		
		foreach($properties as $prop){
			$query->bindValue(':' . $prop, isset($this->$prop) ? $this->$prop : NULL);
		}

		return $query;
	}
	
	private function getProperties(){
		return $this->propertiesList();
	}
	
	private function parseTableName(){
		preg_match("/[A-Z][^A-Z]*/",get_class($this),$matches);
		return isset($matches[0]) ? strtolower($matches[0]) : null;
	}
	
	private function setTable($table){
		$this->table = $table;
	}
	
	private function getTable(){
		return $this->table;
	}
	
	private function dataToObject($data){
		$objects = array();
		$className = get_class($this);
		foreach($data as $key => $value){
			$objects[$key] = new $className();
			foreach($value as $propName => $propValue){
				if(!is_numeric($propName)){
					$objects[$key]->$propName = $propValue;
				}
			}
			$objects[$key]->recieved = true;
		}
		return $objects;
	}
	
	protected function propertiesList(){
		return array();
	}
	
	protected function beforeSave(){}
	protected function  afterSave(){}
}