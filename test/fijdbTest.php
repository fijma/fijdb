<?php

use \fijma\fijdb\Fijdb;

class FijdbTest extends PHPUnit_Framework_TestCase
{
	public function test_fijdb_validates_user_array()
	{
		$users = ['ro' => ['id' => 'readonly',
				   'pw' => 'readonlypw'],
			  'rw' => ['id' => 'readwrite',
			  	   'pw' => 'readwritepw']
			 ];

		$this->assertInstanceOf('fijma\fijdb\Fijdb', new Fijdb('host', 'dbname', $users));
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_empty_user_array()
	{
		$users = array();
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijd_db_rejects_user_array_with_non_string_keys()
	{
		$users = array(['id' => 'userid', 'pw' => 'password']);
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_user_array_without_id()
	{
		$users = ['no_good' => ['username', 'pw' => 'password']];
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_user_array_without_password()
	{
		$users = ['no_good' => ['id' => 'username', 'password']];
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_user_array_with_too_many_details()
	{
		$users = ['no_good' => ['id' => 'username', 'pw' => 'password', 'willyoumarryme' => true]];
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_user_array_with_non_string_value_for_id()
	{
		$users = ['no_good' => ['id' => 1234, 'pw' => 'password']];
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid users array.
	 */
	public function test_fijdb_rejects_user_array_with_non_string_value_for_password()
	{
		$users = ['no_good' => ['id' => 'username', 'pw' => 1234]];
		$db = new Fijdb('host', 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid host value.
	 */
	public function test_fijdb_rejects_invalid_host()
	{
		$users = ['ro' => ['id' => 'readonly',
				   'pw' => 'readonlypw'],
			  'rw' => ['id' => 'readwrite',
			  	   'pw' => 'readwritepw']
			 ];
	
		$db = new Fijdb(null, 'dbname', $users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid dbname value.
	 */
	public function test_fijdb_rejects_invalid_dbname()
	{
		$users = ['ro' => ['id' => 'readonly',
				   'pw' => 'readonlypw'],
			  'rw' => ['id' => 'readwrite',
			  	   'pw' => 'readwritepw']
			 ];
	
		$db = new Fijdb('host', null, $users);
	}

}

?>
