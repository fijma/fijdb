<?php

namespace fijma\fijdb;

class MockMysqli
{

	public $insert_id = false;
	public $connect_error = false;
	public $error = 'argh!';
	public function close(){}
}
?>
