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

	public function result_metadata()
	{
		if($this->r) {
			return new \fijma\fijdb\MockMysqliResult();
		} else {
			$this->r;
		}
	}

	public function bind_result()
	{
		return $GLOBALS['br'];
	}

	public function fetch()
	{
		return false;
	}
}
?>
