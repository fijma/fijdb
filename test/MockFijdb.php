<?php

namespace fijma\fijdb;

use fijma\fijdb\Fijdb;
use fijma\fijdb\MockMysqli;

class MockFijdb extends Fijdb
{
	protected function &connect($user)
	{
		$this->conn[$user] = new MockMysqli();
		return $this->conn[$user];
	}
}

?>
