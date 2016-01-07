<?php

namespace fijma\fijdb;

class Fijdb
{

	const SELECT = 1;
	const UPDATE = 2;
	const INSERT = 4;
	const DELETE = 8;
	const MULTI_INSERT = 16;

	protected $host;
	protected $dbname;
	protected $users;
	protected $conn = array();
	protected $inTransaction = false;

	public function __construct($host, $dbname, array $users)
	{
		if(!is_string($host)) throw new \Exception('Fijdb received invalid host value.');
		$this->host = $host;
		if(!is_string($dbname)) {
			throw new \Exception('Fijdb received invalid dbname value.');
		}
		$this->dbname = $dbname;
		if(!$this->validateUsers($users)) throw new \Exception('Fijdb received invalid users array.');
		$this->users = $users;
	}

	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Return the database connection for the given user, creating it if required.
	 */
	protected function &connect($user)
	{
		if(!array_key_exists($user, $this->conn)) {
			if(array_key_exists($user, $this->users)) {
				$this->conn[$user] = new \mysqli($this->host, $this->users[$user]['id'], $this->users[$user]['pw'], $this->dbname);
			} else {
				throw new \Exception('Fijdb does not know who ' . $user . ' is.');
			}
			if($this->conn[$user]->connect_error) {
				throw new \Exception('Fijdb failed to connect to the database: ' . $this->conn[$user]->connect_error);
			}
		}
		return $this->conn[$user];
	}

	/**
	 * Close the database connection for the given user.
	 * If not user is given, close all connections.
	 */
	protected function close($user = null)
	{
		if(is_null($user)) {
			foreach($this->conn as $db) {
				$db->close();
			}
			$this->conn = [];
		} else {
			if(array_key_exists($user, $this->conn)) {
				$this->conn[$user]->close();
				unset($this->conn[$user]);
			}
		}
	}


	/**
	 * Ensures the user array conforms to the expected format of
	 * ['user1' => ['id' => 'user1id', 'pw' => 'user1pw'],
	 *  'user2' => ['id' => 'user2id', 'pw' => 'user2pw'],...];
	 */
	protected function validateUsers(array $users)
	{
		if(count($users) === 0) return false;
		foreach($users as $k => $v) {
			if(!is_string($k)) return false;
			if(!is_array($users[$k])) return false;
			if(count($users[$k]) !== 2) return false;
			foreach(['id', 'pw'] as $l) {
				if(!array_key_exists($l, $users[$k])) return false;
				if(!is_string($users[$k][$l])) return false;
			}
		}
		return true;
	}

	/**
	 * We have our own transaction functions so we can keep track of
	 * whether we are in a transaction or not in the other functions).
	 * Note that connect will return the existing connection, so we're safe to simply
	 * keep calling it.
	 */
	protected function beginTransaction($user)
	{
		$conn = $this->connect($user);
		$conn->begin_transaction();
		$this->inTransaction = true;
	}

	protected function rollbackTransaction($user)
	{
		$conn = $this->connect($user);
		$conn->rollback();
		$this->inTransaction = false;
	}

	protected function commitTransaction($user)
	{
		$conn = $this->connect($user);
		$conn->commit();
		$this->inTransaction = false;
	}

	/**
	 * Generic query functions. These, along with the preceding transaction functions,
	 * are the ones to use.
	 */

	// Returns the insert id for the row.
	protected function insert($user, $sql, $typestring, $args)
	{
		return $this->runQuery(self::INSERT, $user, $sql, $typestring, $args);
	}

	// Expects an array of parameter arrays. 
	protected function insertMultiple($user, $sql, $typestring, $args)
	{
		$this->runQuery(self::MULTI_INSERT, $user, $sql, $typestring, $args);
	}

	protected function select($user, $sql, $typestring = '', $args = [])
	{
		return $this->runQuery(self::SELECT, $user, $sql, $typestring, $args);
	}

	protected function update($user, $sql, $typestring, $args)
	{
		$this->runQuery(self::UPDATE, $user, $sql, $typestring, $args);
	}

	protected function delete($user, $sql, $typestring, $args)
	{
		$this->runQuery(self::DELETE, $user, $sql, $typestring, $args);
	}

	/**
	 * Performs our queries. Note that none of these functions will close the connection if
	 * a transaction is being run (unless an error is encountered, in which case they will
	 * automatically rollback the transaction if an error is encountered.
	 */

	// Main worker function
	protected function runQuery($queryType, $user, $sql, $typestring, $args)
	{
		$stmt = $this->prepareStatement($user, $sql);
		// Normalise argument array to handle multiple inserts.
		$params = ($queryType & self::MULTI_INSERT) ? $args : array($args);
		foreach($params as $args_array) {
			$this->bindParameters($user, $stmt, $typestring, $args_array);
			$this->executeStatement($stmt, $user);
		}
		
		if($queryType & self::SELECT) return $this->getResult($stmt, $user);
		$stmt->close();
		if($queryType & self::INSERT) return $this->getInsertId($user);
	}

	protected function &prepareStatement($user, $sql)
	{
		$conn = $this->connect($user);
		$stmt = $conn->stmt_init();
		if(!$stmt->prepare($sql)) {
			$stmt->close();
			if($this->inTransaction) $this->rollbackTransaction($user);
			$this->close($user);
			throw new \Exception('Fijdb could not prepare the statement: ' . $sql);
		}
		return $stmt;
	}

	protected function bindParameters($user, &$stmt, $typestring, $args)
	{
		if($typestring !== '' && count($args) > 0) {
			$refArgs = array();
			foreach($args as $k => $arg) {
				$refArgs[] = &$args[$k];
			}
			array_unshift($refArgs, $typestring);
			if(!call_user_func_array(array($stmt, 'bind_param'), $refArgs)) {
				$err = $stmt->error;
				$stmt->close();
				if($this->inTransaction) $this->rollbackTransaction($user);
				$this->close($user);
				throw new \Exception('Fijdb could not bind parameters: ' . $err);
			}
		} elseif($typestring !== '' && empty($args)) {
			throw new \Exception('Fijdb did not receive arguments array.');
		} elseif($typestring === '' && count($args) > 0) {
			throw new \Exception('Fijdb did not receive a typestring.');
		}
	}

	protected function executeStatement(&$stmt, $user)
	{
		if(!$stmt->execute()) {
			$err = $stmt->error;
			$stmt->close();
			$this->rollbackTransaction($user);
			$this->close($user);
			throw new \Exception('Could not execute query: ' . $err);
		}
	}

	protected function getInsertId($user)
	{
		$conn = $this->connect($user);
		$id = $conn->insert_id;
		if(!$id) {
			$err = $conn->error;
			if($err !== '') {
				$this->close($user);
				throw new \Exception('Fijdb could not get insert id: ' . $err);
			}
		}
		if(!$this->inTransaction) $this->close($user);
		return $id;
	}

	/**
	 * This function tabulates the results such that the return value is an array of
	 * associative arrays.
	 */
	protected function getResult(&$stmt, $user)
	{
		// Retrieve metadata
		if(!$result = $stmt->result_metadata()) {
			$err = $stmt->error;
			$stmt->close();
			$this->close($user);
			throw new \Exception('Fijdb could not access result metadata: ' . $err);
		}

		// Bind results
		$data = array();
		$fields = array();
		$fieldlist = $result->fetch_fields();
		if(!count($fieldlist)) {
			$result->close();
			$stmt->close();
			$this->close($user);
			return [];
		} else {
			foreach($fieldlist as $field) {
				$fields[] = &$data[$field->name];
			}
			if(!call_user_func_array(array($stmt, 'bind_result'), $fields)) {
				$err = $stmt->error;
				$stmt->close();
				$this->close($user);
				throw new \Exception('Fijdb could not bind results: ' . $err);
			}
		}

		// Tabulate data
		$res = [];
		while($fetch = $stmt->fetch()) {
			$row = [];
			foreach($data as $k => $v) {
				$row[$k] = $v;
			}
			$res[] = $row;
		}
		if($fetch === false) {
			$err = $stmt->error;
			$stmt->close();
			$this->close($user);
			throw new \Exception('Failed to fetch data: ' . $err);
		}
		
		// Finish up
		$stmt->close();
		if(!$this->inTransaction) $this->close($user);
		return $res;
	}

}

?>
