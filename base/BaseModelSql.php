<?php
abstract class BaseModelSql extends PDO
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
	 * Retrieves all records
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
	 * that are defined in an object OR
	 * if $param is specified - retrieves
	 * custom query(needs some work - retrieves
	 * only AND queries) OR if nothing is specified
	 * retrieves all records
	 * @author Artur
	 * @version 1.0
	 * @params - array of query params
	 * @return array - query
	 */
	public function find($params = null){
		$where = $this->constructRetrieveQuery($params);
		$data = $this->runQuery('retrieve', $where);
				
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
		
		if($result)
			$this->afterSave();
		
		return $result ? true : false;
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

	/**
	 * Returns current object's properties list
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
	public function getProperties(){
		return $this->propertiesList();
	}
	
	/**
	 * Converts current object to array
	 * stripping protected and private properties
	 * @author Artur
	 * @version 1.0
	 * @return array - query
	 */
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
	
	private function runQuery($queryType, $where = null){
		$query = $this->getQuery($queryType, $where);
		$data = $query->execute();
	
		if(!$data){
			$error = $query->errorInfo();
			throw new BaseException('SQL ERROR: ' . $error[2]);
		}
	
		return $query;
	}
	
	private function getQuery($queryType, $where){
		$methodName = 'get' . ucfirst($queryType) . 'Query';
		return $this->$methodName($this->getProperties(), $where);
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
//		return array('primary' => 'id','author','title','isbn','created','modified');
		if(!$where){
			foreach($properties as $prop){
				if(isset($this->$prop)){
					if($counter == 1){
						$delimiter = " AND ";
					}
					
					$fields .= $delimiter . $prop . " = :" . $prop;
				//	" WHERE id = :id"
					$counter++;
				}
			}

			if(!$counter){
				$fields = null;
			}
		}

		$query = $this->prepare("SELECT * FROM " . $this->table . $fields);
		
		
		if($where && is_array($where) && isset($where['querydata'])){			
			foreach($where['querydata'] as $prop => $value){
				$query->bindValue(':' . $prop, $value);
			}
		}

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
		$methodName = $prop . 'Validate';
		
		if(method_exists($this, $methodName)){
			if(!$this->$methodName($this->$prop)){
				throw new BaseException("Model: propertie '" . $prop . "' did not pass validation");
			}
		}
		
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
	 *		'title' => array('=' , var), - single condition for one parameter
	 *		'id'    => array(
	 *				array('>',var)
     *			)  	                     - multiple conditions for one parameter
	 *	);
	 */
	private function constructRetrieveQuery($params){
		if(!isset($params))
			return null;
		
		$query = "";
		$data = array();
		$delimiter = null;
		$delimiterInside = null;
		$count = 0;
		$countInside = 0;
		if(is_array($params)){
			foreach($params as $prop => $values){
				if($count == 1){
					$delimiter = " AND ";
				}
				
				$query .= $delimiter;
				
				if(isset($values[0]) && isset($values[1])
						&& (!is_array($values[0]) && !is_array($values[1]))){
					$query .= $prop . " " . $values[0] . " :" . $prop;
					$data[$prop] = $values[1];
				}
				
				if(isset($params[$prop][0]) && is_array($params[$prop][0])){
					foreach($values as $queries){
						if($countInside == 1){
							$delimiterInside = " AND ";
						}
						
						$query .= $delimiterInside;

						if(isset($queries[0]) && isset($queries[1])){
							$query .= $prop . " " . $queries[0] . " :" . $prop . $countInside;
							$data[$prop . $countInside] = $queries[1];
						}
						
						$countInside++;
					}
				}

				$countInside = 0;
				$delimiterInside = null;
				$count++;
			}
		}

		return array('query' => $query, 'querydata' => $data);
	}
	
	private function getRelations(){
		return $this->relations;
	}
	
	protected function setRelations(){
		return array();
	}	
	
	protected function setPropertie($name, $value){
		if(isset($this->$name)){
			$this->$name = $value;
			return true;
		}
		return false;
	}
	
	abstract protected function propertiesList();
	protected function beforeSave(){}
	protected function  afterSave(){}
}