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
		return $this->createFlatXMLDataSet('test/setup.xml');
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

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb does not know who fij is.
	 */
	public function test_fijdb_rejects_unknown_users()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn = $m->invokeArgs($db, array('fij'));
	}

	public function test_fijdb_saves_connections_to_array()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn1 = $m->invokeArgs($db, array('u1'));
		$conn2 = $m->invokeArgs($db, array('u2'));
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$connArray = $p->getValue($db);
		$this->assertArrayHasKey('u1', $connArray);
		$this->assertIdentical($connArray['u1'], $conn1);
		$this->assertArrayHasKey('u2', $connArray);
		$this->assertIdentical($connArray['u2'], $conn2);
	}

	public function test_fijdb_closes_all_connections_when_calling_close_without_args()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$m->invokeArgs($db, array('u1'));
		$m->invokeArgs($db, array('u2'));
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'close');
	        $m->invoke($db);
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$conArray = $p->getValue($db);
		$this->assertEmpty($conArray);
	}

	public function test_fijdb_only_closes_the_requested_connection()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$m->invokeArgs($db, array('u1'));
		$m->invokeArgs($db, array('u2'));
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'close');
	        $m->invokeArgs($db, array('u1'));
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$conArray = $p->getValue($db);
		$this->assertCount(1, $conArray);
		$this->assertArrayHasKey('u2', $conArray);
	}

	public function test_fijdb_select_without_parameters()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$results = $select->invoke($db, 'u2', 'SELECT * FROM testtable');
		$this->assertTrue(is_array($results));
		$this->assertCount(1, $results);
		$this->assertTrue(is_array($results[0]));
		$this->assertCount(2, $results[0]);
		$this->assertArrayHasKey('id', $results[0]);
		$this->assertArrayHasKey('value', $results[0]);
		$this->assertEquals([['id' => 1,
				   'value' => 'Because I have to have something.']], $results);
	}

	public function test_fijdb_select_with_parameters()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$results = $select->invoke($db, 'u2', 'SELECT * FROM testtable WHERE id = ?', 'i', array(1));
		$this->assertTrue(is_array($results));
		$this->assertCount(1, $results);
		$this->assertTrue(is_array($results[0]));
		$this->assertCount(2, $results[0]);
		$this->assertArrayHasKey('id', $results[0]);
		$this->assertArrayHasKey('value', $results[0]);
		$this->assertEquals([['id' => 1,
				   'value' => 'Because I have to have something.']], $results);
	}

	public function test_fijdb_insert()
	{
		$db = new Fijdb('localhost', 'fijdb', $this->users);
		$insert = $this->getMethod('\fijma\fijdb\Fijdb', 'insert');
		$id = $insert->invoke($db, 'u2', 'INSERT INTO testtable(value) VALUES(?);', 's', array('Hello, world!'));
		$this->assertEquals(2, $id);
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_insert.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}


}

?>
