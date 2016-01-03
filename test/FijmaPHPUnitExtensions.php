<?php

require_once 'FijmaConstraintIdenticality.php';

abstract class FijmaPHPUnitExtensions extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * Assert object instances are identical.
	 */
	public static function assertIdentical($expected, $actual, $message = '')
	{
		self::assertThat($actual, self::getIdenticalityConstraint($expected), $message);
	}

	public static function getIdenticalityConstraint($expected)
	{
		return new FijmaConstraintIdenticality($expected);
	}

	/**
	 * Make the protected/private method accessible.
	 */
	public static function getMethod($className, $methodName)
	{
		$class = new \ReflectionClass($className);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}
}
