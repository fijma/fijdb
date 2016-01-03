<?php

class FijmaConstraintIdenticality extends PHPUnit_Framework_Constraint
{
	private $expected;

	public function __construct($expected)
	{
		$this->expected = $expected;
	}

	protected function matches($actual)
	{
		return $actual === $this->expected;
	}

	public function toString()
	{
		return 'is identical to ' . print_r($this->expected, TRUE);
	}
}
?>
