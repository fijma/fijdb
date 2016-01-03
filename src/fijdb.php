<?php

namespace fijma\fijdb;

class Fijdb
{

	var $host;
	var $dbname;
	var $users;
	var $conn;

	public function __construct($host, $dbname, array $users)
	{
		if(!is_string($host)) throw new \Exception('Fijdb received invalid host value.');
		$this->host = $host;
		if(!is_string($dbname)) throw new \Exception('Fijdb received invalid dbname value.');
		$this->dbname = $dbname;
		if(!$this->validateUsers($users)) throw new \Exception('Fijdb received invalid users array.');
		$this->users = $users;
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
