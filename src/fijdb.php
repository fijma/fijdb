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
	 * ['ro' => ['id' => 'readonly', 'pw' => 'readonlypw'],
	 *  'rw' => ['id' => 'readwrite', 'pw' => 'readwritepw']];
	 */
	public function validateUsers(array $users)
	{
		if(count($users) !== 2) return false;
		foreach(['ro', 'rw'] as $k) {
			if(!array_key_exists($k, $users)) return false;
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
