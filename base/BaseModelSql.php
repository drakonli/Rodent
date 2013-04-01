<?php
class BaseModelSql extends PDO
{
	protected $table;
	protected $recieved;
	protected $query;
	protected $relations;
	
	public function __construct(){
		$config = App::get()->getSetting('sql');
		$this->setTable($this->parseTableName());
		$this->relations = $this->setRelations();

		parent::__construct(
					"mysql:host=" . $config['address'] . ";dbname=" . $config['dbName'],
					$config['username'], 
					$config['password'],
					array(PDO::ATTR_PERSISTENT => $config['persist'])
				);
	}
	
	/**
	 * Retrieves a custom query
	 * WARNING! Escape this array properly
	 * @author Artur
	 * @version 1.0
	 * @param array $params - array of query parameters
	 * @return array
	 */
	public function findCustom($params){
		$where = $this->constructRetrieveQuery($params);

		$data = $this->runQuery('retrieve', $where);
		if($data){
			$data = $data->fetchAll();
			$data = $this->dataToObject($data);
		}
		
		return $data;
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
		if($this->recieved){
			$result = $this->runQuery('update');
		} else {
			$result = $this->runQuery('create');
			$this->recieved = true;
		}
		
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

	public function getProperties(){
		return $this->propertiesList();
	}
	
	public function toArray(){
		$data = (array) $this;
		foreach($data as $relatedKey => &$related){
			
			if(is_object($related)){
				$related = (array) $related;
				foreach($related as $key => $value){						
					if(preg_match('/\*/',$key)){
						unset($related[$key]);
					}
				}
			}
			if(preg_match('/\*/', $relatedKey)){
				unset($data[$relatedKey]);
			}
		}

		return $data;
	}
	//C
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
			$query->bindValue(':' . $prop, isset($this->$prop) ? $this->getPropertieData($prop) : NULL);
		}
	
		return $query;
	}
	
	//R
	private function getRetrieveQuery($properties, $where = null){
		$fields = " WHERE " . (isset($where['query']) ? $where['query'] : null);
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
					$query->bindValue(':' . $prop, $this->getPropertieData($prop));
				}
			}
		}
		
		return $query;		
	}
	
	//U
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

		$query = $this->prepare("UPDATE " . $this->table . " SET " . $values . " WHERE " 
				. $properties['primary'] . " = :" . $properties['primary']);
		
		foreach($properties as $prop){
			$query->bindValue(':' . $prop, isset($this->$prop) ? $this->getPropertieData($prop) : NULL);
		}

		return $query;
	}
	
	//D
	private function getDeleteQuery($properties, $where = null){
		$query = $this->prepare("DELETE FROM " . $this->table
				. " WHERE " . $properties['primary'] . " = :" . $this->$properties['primary']);
		$query->bindValue(':' . $this->$properties['primary'], $this->$properties['primary']);
	
		return $query;
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
	
	private function getPropertieData($prop){
		$propValue = $this->$prop;
		if($this->$prop && isset($this->relations[$prop])){
			if(!is_object($this->$prop) && !($this->$prop instanceof $this->relations[$prop])){
				throw new BaseException('Model: propertie ' . $prop . " should be an instance of ". $this->relations[$prop]);
			}
			$propValue = $this->$prop->id;
		}
		
		return $propValue;
	}
	
	private function dataToObject($data){
		$objects = array();
		$className = get_class($this);
		foreach($data as $key => $value){
			$objects[$key] = new $className();
			foreach($value as $propName => $propValue){
				if(!is_numeric($propName)){
					if(!empty($this->relations) && isset($this->relations[$propName])){
						$relatedObject = new $this->relations[$propName]();
						$relatedObjectProps = $relatedObject->getProperties();
						$relatedObject->$relatedObjectProps['primary'] = $propValue;
						$relatedObject = $relatedObject->findOne();
						$objects[$key]->$propName = $relatedObject;
						continue;
					}
					$objects[$key]->$propName = $propValue;
				}
			}
			$objects[$key]->recieved = true;
		}
		return $objects;
	}
	
	/**
	 * Creates custom query
	 * @author Artur
	 * @version 1.0
	 * @return array - 
	 * 	array(
	 *		'title' => array('=' , 'bam'),
	 *		'id'    => array(
	 *				array('>',7)
     *			)
	 *	);
	 */
	private function constructRetrieveQuery($params){
		$query = "";
		$data = array();
		$delimiter = null;
		$count = 0;
		if(is_array($params)){			
			foreach($params as $prop => $values){
				if($count == 1){
					$delimiter = " AND ";
				}
				
				$query .= $delimiter;
				
				if(isset($params[$prop][0]) && is_array($params[$prop][0])){
					foreach($values as $queries){
						if(isset($queries[0]) && isset($queries[1])){
							$query .= $prop . " " . $queries[0] . " '" . $queries[1] . "'";
						}
					}
				}
				
				if(isset($values[0]) && isset($values[1])){
					$query .= $prop . " " . $values[0] . " '" . $values[1] . "'";
				}
				
				$count++;
			}
		}
		return array('query' => $query);
	}
	
	private function getRelations(){
		return $this->relations;
	}
	
	protected function setRelations(){
		return array();
	}
	
	protected function propertiesList(){
		return array();
	}
	
	protected function setPropertie($name, $value){
		if(isset($this->$name)){
			$this->$name = $value;
			return true;
		}
		return false;
	}
	
	protected function beforeSave(){}
	protected function  afterSave(){}
}