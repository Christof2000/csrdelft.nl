<?php

require_once 'MVC/model/DatabaseAdmin.singleton.php';
require_once 'MVC/model/Persistence.interface.php';
require_once 'MVC/model/entity/PersistentEntity.abstract.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses database to provide persistence.
 * Requires a static property $instance in superclass.
 * Requires an ORM class constant to be defined in superclass.
 */
abstract class PersistenceModel implements Persistence {

	public static function instance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * ORM entity class
	 * @var PersistentEntity
	 */
	private $orm_entity;

	protected function __construct($subdir = '') {
		$class = static::orm;
		require_once 'MVC/model/entity/' . $subdir . $class . '.class.php';
		$class::__constructStatic();
		$this->orm_entity = new $class();
		if (defined('DB_CHECK')) {
			$class::checkTable();
		}
	}

	/**
	 * Find existing entities with optional search criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $limit = null, $start = 0) {
		$result = Database::sqlSelect($this->orm_entity->getFields(), $this->orm_entity->getTableName(), $criteria, $criteria_params, $orderby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, static::orm, array(true));
		return $result;
	}

	/**
	 * Count existing entities with optional criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		$result = Database::sqlSelect(array('COUNT(*)'), $this->orm_entity->getTableName(), $criteria, $criteria_params);
		return (int) $result->fetchColumn();
	}

	/**
	 * Check if entities with optional search criteria exist.
	 * 
	 * @param string $criteria
	 * @param array $criteria_params
	 * @return boolean entities with search criteria exist
	 */
	public function exist($criteria = null, array $criteria_params = array()) {
		return Database::sqlExists($this->orm_entity->getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if enitity exists.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function exists(PersistentEntity $entity) {
		return $this->existsByPrimaryKeys($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKeys(array $primary_keys_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->exist(implode(' AND ', $where), $primary_keys_values);
	}

	/**
	 * Save new entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return Database::sqlInsert($this->orm_entity->getTableName(), $entity->getValues());
	}

	/**
	 * Load saved enitity data and replace entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return PersistentEntity
	 */
	public function retrieve(PersistentEntity $entity) {
		$entity = $this->retrieveByPrimaryKeys($entity->getValues(true));
		return $entity;
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return PersistentEntity or FALSE on failure
	 */
	protected function retrieveByPrimaryKeys(array $primary_keys_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($this->orm_entity->getFields(), $this->orm_entity->getTableName(), implode(' AND ', $where), $primary_keys_values, null, 1);
		return $result->fetchObject(static::orm, array(true));
	}

	/**
	 * Save existing entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int rows affected
	 */
	public function update(PersistentEntity $entity) {
		$properties = $entity->getValues();
		$where = array();
		$params = array();
		foreach ($this->orm_entity->getPrimaryKeys() as $key) {
			$where[] = $key . ' = :W' . $key; // name parameters after column
			$params[':W' . $key] = $properties[$key];
			unset($properties[$key]); // do not update primary key
		}
		return Database::sqlUpdate($this->orm_entity->getTableName(), $properties, implode(' AND ', $where), $params, 1);
	}

	/**
	 * Remove existing entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return boolean rows affected === 1
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKeys($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return boolean rows affected === 1
	 */
	protected function deleteByPrimaryKeys(array $primary_keys_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		return 1 === Database::sqlDelete($this->orm_entity->getTableName(), implode(' AND ', $where), $primary_keys_values, 1);
	}

}
