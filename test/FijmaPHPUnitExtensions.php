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

	/**
	 * Make the protected/private property accessible.
	 */
	public static function getProperty($className, $propertyName)
	{
		$class = new \ReflectionClass($className);
		$property = $class->getProperty($propertyName);
		$property->setAccessible(true);
		return $property;
	}

	/**
	 * A function to bypass PHPUnit's default error handling.
	 * Usage: set_error_handler([$this, 'bypassError']);
	 */
	public function bypassError($errno, $errstr, $errfile, $errline, $errcontext)
	{
		// do nothing, we're just avoiding PHPUnit's error handling behaviour.
	}


}
