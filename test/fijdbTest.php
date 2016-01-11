<?php

use \fijma\fijdb\Fijdb;

class FijdbTest extends FijmaPHPUnitExtensions 
{

	protected $users = ['u1' => ['id' => 'u1',
			             'pw' => 'u1pw'],
		            'u2' => ['id' => 'u2',
			             'pw' => 'u2pw']
			     ];

	protected $db;

	protected function setUp()
	{
		parent::setUp();
		$this->db = new Fijdb('localhost', 'fijdb', $this->users);
	}	

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

	/**
	 * @expectedException \Exception
	 * @expectedException Message Fijdb failed to connect to the database:
	 */
	public function test_fijdb_reports_failed_connection()
	{
		$users = ['fij' => ['id' => 'fij', 'pw' => 'fijpw']];
		$db = new Fijdb('localhost', 'fijdb', $users);
		set_error_handler([$this, 'bypassError']);
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn = $m->invokeArgs($db, array('fij'));
		restore_error_handler();
	}

	public function test_fijdb_connect_gets_a_connection()
	{
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn = $m->invokeArgs($this->db, array('u1'));
		$this->assertInstanceOf('\mysqli', $conn);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb does not know who fij is.
	 */
	public function test_fijdb_rejects_unknown_users()
	{
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn = $m->invokeArgs($this->db, array('fij'));
	}

	public function test_fijdb_saves_connections_to_array()
	{
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$conn1 = $m->invokeArgs($this->db, array('u1'));
		$conn2 = $m->invokeArgs($this->db, array('u2'));
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$connArray = $p->getValue($this->db);
		$this->assertArrayHasKey('u1', $connArray);
		$this->assertIdentical($connArray['u1'], $conn1);
		$this->assertArrayHasKey('u2', $connArray);
		$this->assertIdentical($connArray['u2'], $conn2);
	}

	public function test_fijdb_closes_all_connections_when_calling_close_without_args()
	{
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$m->invokeArgs($this->db, array('u1'));
		$m->invokeArgs($this->db, array('u2'));
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'close');
	        $m->invoke($this->db);
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$conArray = $p->getValue($this->db);
		$this->assertEmpty($conArray);
	}

	public function test_fijdb_only_closes_the_requested_connection()
	{
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'connect');
		$m->invokeArgs($this->db, array('u1'));
		$m->invokeArgs($this->db, array('u2'));
		$m = $this->getMethod('\fijma\fijdb\Fijdb', 'close');
	        $m->invokeArgs($this->db, array('u1'));
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'conn');
		$conArray = $p->getValue($this->db);
		$this->assertCount(1, $conArray);
		$this->assertArrayHasKey('u2', $conArray);
	}

	public function test_fijdb_select_without_parameters()
	{
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$results = $select->invoke($this->db, 'u2', 'SELECT * FROM testtable');
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
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$results = $select->invoke($this->db, 'u2', 'SELECT * FROM testtable WHERE id = ?', 'i', array(1));
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
		$insert = $this->getMethod('\fijma\fijdb\Fijdb', 'insert');
		$id = $insert->invoke($this->db, 'u2', 'INSERT INTO testtable(value) VALUES(?);', 's', array('Hello, world!'));
		$this->assertEquals(2, $id);
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_insert.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	public function test_fijdb_multi_insert()
	{
		$multiInsert = $this->getMethod('\fijma\fijdb\Fijdb', 'insertMultiple');
		$data = [["Here's an entry."], ["Here's another."]];
		$multiInsert->invoke($this->db, 'u2', 'INSERT INTO testtable(value) VALUES(?);', 's', $data);
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable;'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_multi_insert.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	public function test_fijdb_update()
	{
		$insert = $this->getMethod('\fijma\fijdb\Fijdb', 'insert');
		$update = $this->getMethod('\fijma\fijdb\Fijdb', 'update');
		$insert->invoke($this->db, 'u2', 'INSERT INTO testtable(value) VALUES(?);', 's', array('Hello, world!'));
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable;'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_insert.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
		$update->invoke($this->db, 'u2', 'UPDATE testtable SET value = ? WHERE id = ?;', 'si', ['Hello yourself!', 2]);
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable;'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_update.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	public function test_fijdb_delete()
	{
		$insert = $this->getMethod('\fijma\fijdb\Fijdb', 'insert');
		$delete = $this->getMethod('\fijma\fijdb\Fijdb', 'delete');
		$insert->invoke($this->db, 'u2', 'INSERT INTO testtable(value) VALUES(?);', 's', array('Hello, world!'));
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable;'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_insert.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
		$delete->invoke($this->db, 'u2', 'DELETE FROM testtable WHERE id = ?;', 'i', [1]);
		$queryTable = $this->getConnection()->createQueryTable(
			'testtable', 'SELECT * FROM testtable;'
		);
		$expectedTable = $this->createFlatXMLDataSet('test/test_fijdb_delete.xml')
				      ->getTable('testtable');
		$this->assertTablesEqual($expectedTable, $queryTable);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not prepare the statement: 
	 */
	public function test_prepare_statement_failure_non_transaction()
	{
		set_error_handler([$this, 'bypassError']);
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$select->invoke($this->db, 'u1', 'SELECT * FROM notatable;');
		restore_error_handler();
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not prepare the statement: 
	 */
	public function test_prepare_statement_failure_transaction()
	{
		set_error_handler([$this, 'bypassError']);
		$begin = $this->getMethod('\fijma\fijdb\Fijdb', 'beginTransaction');
		$begin->invoke($this->db, 'u1');
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'inTransaction');
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, true);
		$select = $this->getMethod('\fijma\fijdb\Fijdb', 'select');
		$select->invoke($this->db, 'u1', 'SELECT * FROM notatable;');
		restore_error_handler();
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not bind parameters: 
	 */
	public function test_fijdb_bind_statement_failure_non_transaction()
	{
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(false);
		$bind = $this->getMethod('\fijma\fijdb\Fijdb', 'bindParameters');
		$ref = &$stmt;
		$bind->invoke($this->db, 'u2', $ref, 'i', [0]);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not bind parameters: 
	 */
	public function test_fijdb_bind_statement_failure_transaction()
	{
		$begin = $this->getMethod('\fijma\fijdb\Fijdb', 'beginTransaction');
		$begin->invoke($this->db, 'u1');
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'inTransaction');
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, true);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(false);
		$bind = $this->getMethod('\fijma\fijdb\Fijdb', 'bindParameters');
		$ref = &$stmt;
		$bind->invoke($this->db, 'u1', $ref, 'i', [0]);
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb did not receive arguments array.
	 */
	public function test_fijdb_bindParameters_with_missing_arguments()
	{
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$ref = &$stmt;
		$bind = $this->getMethod('\fijma\fijdb\Fijdb', 'bindParameters');
		$bind->invoke($this->db, 'u1', $ref, 'i', []);
	}
	
	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb did not receive a typestring.
	 */
	public function test_fijdb_bindParameters_with_missing_typestring()
	{
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$ref = &$stmt;
		$bind = $this->getMethod('\fijma\fijdb\Fijdb', 'bindParameters');
		$bind->invoke($this->db, 'u1', $ref, '', [0]);
	}
	
	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Could not execute query: argh!
	 */
	public function test_fijdb_executeStatement_error_non_transaction()
	{
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(false);
		$stmt->error = 'argh!';
		$ref = &$stmt;
		$exec = $this->getMethod('\fijma\fijdb\Fijdb', 'executeStatement');
		$exec->invoke($this->db, $ref, 'u1');
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Could not execute query: argh!
	 */
	public function test_fijdb_executeStatement_error_transaction()
	{
		$begin = $this->getMethod('\fijma\fijdb\Fijdb', 'beginTransaction');
		$begin->invoke($this->db, 'u1');
		$p = $this->getProperty('\fijma\fijdb\Fijdb', 'inTransaction');
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, true);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(false);
		$stmt->error = 'argh!';
		$ref = &$stmt;
		$exec = $this->getMethod('\fijma\fijdb\Fijdb', 'executeStatement');
		$exec->invoke($this->db, $ref, 'u1');
		$inTransaction = $p->getValue($this->db);
		$this->assertEquals($inTransaction, false);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not get insert id: argh!
	 */
	public function test_fijdb_getInsertId_error()
	{
		$db = new \fijma\fijdb\MockFijdb('host', 'db', $this->users);
		$m = $this->getMethod('\fijma\fijdb\MockFijdb', 'connect');
		$conn = $m->invoke($db, 'u1');
		$getInsertId = $this->getMethod('\fijma\fijdb\MockFijdb', 'getInsertId');
		$getInsertId->invoke($db, 'u1');
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not access result metadata: argh!
	 */
	public function test_fijdb_get_metadata_error()
	{
		$db = new \fijma\fijdb\MockFijdb('host', 'db', $this->users);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(false);
		$stmt->error = 'argh!';
		$ref = &$stmt;
		$getResults = $this->getMethod('\fijma\fijdb\MockFijdb', 'getResult');
		$getResults->invoke($db, $ref, 'u1');
	}

	public function test_fijdb_results_returns_array_if_no_metadata()
	{
		$db = new \fijma\fijdb\MockFijdb('host', 'db', $this->users);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(true);
		$ref = &$stmt;
		$GLOBALS['ff'] = [];
		$getResults = $this->getMethod('\fijma\fijdb\MockFijdb', 'getResult');
		$result = $getResults->invoke($db, $ref, 'u1');
		$this->assertEquals([], $result);
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb could not bind results: argh!
	 */
	public function test_fijdb_bind_results_error()
	{
		$db = new \fijma\fijdb\MockFijdb('host', 'db', $this->users);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(true);
		$stmt->error = 'argh!';
		$ref = &$stmt;
		$GLOBALS['ff'] = new \fijma\fijdb\MockMysqliField();
		$GLOBALS['br'] = false;
		$getResults = $this->getMethod('\fijma\fijdb\MockFijdb', 'getResult');
		$result = $getResults->invoke($db, $ref, 'u1');
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Fijdb failed to fetch data: argh!
	 */
	public function test_fijdb_fetch_error()
	{
		$db = new \fijma\fijdb\MockFijdb('host', 'db', $this->users);
		$stmt = new \fijma\fijdb\MockMysqliStatement();
		$stmt->setReturn(true);
		$stmt->error = 'argh!';
		$ref = &$stmt;
		$GLOBALS['ff'] = new \fijma\fijdb\MockMysqliField();
		$GLOBALS['br'] = true;
		$getResults = $this->getMethod('\fijma\fijdb\MockFijdb', 'getResult');
		$result = $getResults->invoke($db, $ref, 'u1');
	}



}

?>
