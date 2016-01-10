<?php

namespace fijma\fijdb;

class MockMysqliStatement
{

	/**
	 * Set the return value for the next function call.
	 */
	private $r;

	public function setReturn($r)
	{
		$this->r = $r;
	}

	public $error;

	public function bind_param()
	{
		return $this->r;
	}

	public function execute()
	{
		return $this->r;
	}

	public function close()
	{
	}

}
?>
