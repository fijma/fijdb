<?php

namespace fijma\fijdb;

class Fijdb
{

	protected $host;
	protected $dbname;
	protected $users;
	protected $conn = array();

	public function __construct($host, $dbname, array $users)
	{
		if(!is_string($host)) throw new \Exception('Fijdb received invalid host value.');
		$this->host = $host;
		if(!is_string($dbname)) throw new \Exception('Fijdb received invalid dbname value.');
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
				if (is_resource($this->conn[$user])) {
					$this->conn[$user]->close();
				}
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


}

?>
