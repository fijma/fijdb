<?php

use \fijma\fijdb\Fijdb;

class FijdbTest extends FijmaPHPUnitExtensions 
{

	protected $users = ['u1' => ['id' => 'u1',
			             'pw' => 'u1pw'],
		            'u2' => ['id' => 'u2',
			             'pw' => 'u2pw']
			   ];

	public function getConnection()
	{
		$pdo = new PDO('mysql:host=localhost;dbname=fijdb', 'u2', 'u2pw');
		return $this->createDefaultDBConnection($pdo, 'fijdb');
	}

	public function getDataSet()
	{
		return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}
		
		
	public function test_fijdb_accepts_valid_user_array()
	{
		$this->assertInstanceOf('fijma\fijdb\Fijdb', new Fijdb('host', 'dbname', $this->users));
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
		$db = new Fijdb(null, 'dbname', $this->users);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb received invalid dbname value.
	 */
	public function test_fijdb_rejects_invalid_dbname()
	{
		$db = new Fijdb('host', null, $this->users);
	}

	public function test_get_connection_gets_a_connection()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn = $m->invokeArgs($db, array('u1'));
		$this->assertInstanceOf('\mysqli', $conn);
	}

}

?>
