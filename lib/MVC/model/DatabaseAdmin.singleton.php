<?php

require_once 'MVC/model/Database.singleton.php';

/**
 * DatabaseAdmin.singleton.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Separate login credentials in the future perhaps.
 */
class DatabaseAdmin extends Database {

	/**
	 * Singleton instance
	 * @var DatabaseAdmin
	 */
	private static $instance;

	/**
	 * Get singleton Database instance.
	 * 
	 * @return DatabaseAdmin
	 */
	public static function instance() {
		if (defined('DB_MODIFY_ENABLE') AND ! LoginLid::mag('P_ADMIN')) {
			//header('location: ' . CSR_ROOT . '/onderhoud.html');
			//exit;
		}
		if (!isset(self::$instance)) {
			$cred = parse_ini_file(ETC_PATH . '/mysql.ini');
			if ($cred === false) {
				$cred = array(
					'host' => 'localhost',
					'user' => 'admin',
					'pass' => 'password',
					'db' => 'csrdelft'
				);
			}
			$dsn = 'mysql:host=' . $cred['host'] . ';dbname=' . $cred['db'];
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			);
			self::$instance = new DatabaseAdmin($dsn, $cred['user'], $cred['pass'], $options);
		}
		return self::$instance;
	}

	/**
	 * Array of SQL statements for file.sql
	 * @var array
	 */
	private static $queries = array();

	/**
	 * Get array of SQL statements for file.sql
	 * @return array
	 */
	public static function getQueries() {
		return self::$queries;
	}

	/**
	 * Get table fields.
	 * 
	 * @param string $name
	 * @return PDOStatement
	 */
	public static function sqlDescribeTable($name) {
		$sql = 'DESCRIBE ' . $name;
		$query = self::instance()->prepare($sql);
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // lowercase field properties
		$query->execute();
		self::instance()->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // reset
		$query->setFetchMode(PDO::FETCH_CLASS, 'PersistentField');
		return $query;
	}

	/**
	 * Restore table data from file.
	 * 
	 * @param string $name
	 * @param string $path to data file
	 * @return int number of affected rows
	 */
	public static function sqlRestoreTable($name, $path) {
		$path = TMP_PATH . '/' . $name . '_' . time();
		$sql = 'LOAD DATA INFILE "' . $path . '" INTO TABLE ' . $name;
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query->rowCount();
	}

	/**
	 * Backup table data to file.
	 * 
	 * @param string $name
	 * @return string path to data file
	 */
	public static function sqlBackupTable($name) {
		$path = TMP_PATH . '/' . $name . '_' . time();
		$sql = 'SELECT * INTO OUTFILE "' . $path . '" FROM ' . $name;
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $path;
	}

	/**
	 * Get all tables.
	 * 
	 * @return string SQL query
	 */
	public static function sqlShowTables() {
		$sql = 'SHOW TABLES';
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query;
	}

	/**
	 * Get create table query.
	 * 
	 * @param string $name
	 * @return string SQL query
	 */
	public static function sqlShowCreateTable($name) {
		$sql = 'SHOW CREATE TABLE ' . $name;
		$query = self::instance()->prepare($sql);
		$query->execute();
		return $query->fetchColumn(1);
	}

	/**
	 * Create table and return SQL.
	 * 
	 * @param string $name
	 * @param array $fields
	 * @param array $primary_keys
	 * @return string SQL query
	 */
	public static function sqlCreateTable($name, array $fields, array $primary_keys) {
		$sql = 'CREATE TABLE ' . $name . ' (';
		foreach ($fields as $name => $field) {
			$sql .= $field->toSQL() . ', ';
		}
		$sql .= 'PRIMARY KEY (' . implode(', ', $primary_keys) . ')) ENGINE=InnoDB DEFAULT CHARSET=utf8 auto_increment=1';
		if (defined('DB_MODIFY_ENABLE')) {
			$query = self::instance()->prepare($sql);
			$query->execute();
			self::$queries[] = $query->queryString;
		}
		return $sql;
	}

	public static function sqlAddField($table, PersistentField $field, $after_field = null) {
		$sql = 'ALTER TABLE ' . $table . ' ADD ' . $field->toSQL();
		$sql .= ($after_field === null ? ' FIRST' : ' AFTER ' . $after_field);
		if (defined('DB_MODIFY_ENABLE')) {
			$query = self::instance()->prepare($sql);
			$query->execute();
			self::$queries[] = $query->queryString;
		}
		return $sql;
	}

	public static function sqlChangeField($table, PersistentField $field, $old_name = null) {
		$sql = 'ALTER TABLE ' . $table . ' CHANGE ' . ($old_name === null ? $field->field : $old_name) . ' ' . $field->toSQL();
		if (defined('DB_MODIFY_ENABLE')) {
			$query = self::instance()->prepare($sql);
			$query->execute();
			self::$queries[] = $query->queryString;
		}
		return $sql;
	}

	public static function sqlDeleteField($table, PersistentField $field) {
		$sql = 'ALTER TABLE ' . $table . ' DROP ' . $field->field;
		if (defined('DB_MODIFY_ENABLE') AND defined('DB_DROP_ENABLE')) {
			$query = self::instance()->prepare($sql);
			$query->execute();
			self::$queries[] = $query->queryString;
		}
		return $sql;
	}

}
