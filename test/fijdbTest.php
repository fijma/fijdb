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
	public function test_fijdb_rejects_invalid_user_array()
	{
		$users = array();
		$db = new Fijdb('host', 'dbname', $users);
	}
}

?>
