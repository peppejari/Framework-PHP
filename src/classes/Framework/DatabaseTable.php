<?php

namespace Framework;

class DatabaseTable
{
	private $pdo;
	private $primaryKey;
	private $table;
	private $className;
	private $constructorArgs;
	
	public function __construct(\PDO $pdo, string $table, string $primaryKey, string $className = '\stdClass', array $constructorArgs = []){
		$this->pdo = $pdo;
		$this->primaryKey = $primaryKey;
		$this->table = $table;
		$this->className = $className;
		$this->constructorArgs = $constructorArgs;
	}
	
	private function query(string $sql, array $fields = []){
			$query = $this->pdo->prepare($sql);
			$query->execute($fields);
			return $query;
	}
	
	private function processDate($fields){
		foreach($fields as $key => $value) {
			if ($value instanceof \DateTime){
				$fields[$key] = $value->format('Y-m-d H:i:s');
			}
		}
		return $fields;
	}
	
	public function find($fields) {
		$query = 'SELECT ' . $fields . ' FROM ' . $this->table;
		$save = $this->query($query);
		return $save->fetchAll(\PDO::FETCH_CLASS, $this->className, $this->constructorArgs);
	}
	
	public function findById(int $value) {
		$query = 'SELECT * FROM `' . $this->table . '` WHERE `' . $this->primaryKey . '` = :value';
		$fields = ['value' => $value];
		$save = $this->query($query, $fields);
		return $save->fetchObject($this->className, $this->constructorArgs);
	}

	public function findByField($column, $value) {
		$query = 'SELECT * FROM `' . $this->table . '` WHERE `' . $column . '` = :value';
		$fields = ['value' => $value];
		$save = $this->query($query, $fields);
		return $save->fetchAll(\PDO::FETCH_CLASS, $this->className, $this->constructorArgs);
	}
	
	public function delete(int $id){
		$query = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = ' . $id;
		$this->query($query);
	}
	
	private function insert(array $fields){
		$query = 'INSERT INTO `' . $this->table . '` (';
		foreach($fields as $key => $value){
			$query .= '`' . $key . '`, ';
			}
			$query = rtrim($query, ', ');
			$query .= ') VALUES (';
			
			foreach($fields as $key => $valye) {
				$query .= ':' . $key . ', ';
				}
			$query = rtrim($query, ', ');
			$query .= ')';
			$fields = $this->processDate($fields);
			$this->query($query, $fields);
	}
	
		private function update(array $fields){	
			$query = ' UPDATE `' . $this->table . '` SET ';
			foreach($fields as $key => $value){
				$query .= '`'. $key .'` = :' . $key . ',';
			}
			$query =  rtrim($query, ', ');
			$query.= ' WHERE `' . $this->primaryKey . '` = :primaryKey';
			$fields['primaryKey'] = $fields['id'];
			$fields = $this->processDate($fields);
			$this->query($query, $fields);
			}
	
		public function save($fields){
			try
			{
			if($fields[$this->primaryKey] == ''){
				$fields[$this->primaryKey] = null;
			}
			$this->insert($fields);
		} 
		catch (\PDOException $e){
			$this->update($fields);
		}
	}
}