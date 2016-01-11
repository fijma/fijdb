<?php

namespace fijma\fijdb;

class MockMysqliResult
{

	public function fetch_fields()
	{
		return $GLOBALS['ff'];
	}

	public function close(){}
}
?>
