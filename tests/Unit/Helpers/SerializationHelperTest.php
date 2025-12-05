<?php

declare(strict_types=1);
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace UnitTests\phpGPX\Helpers;

use phpGPX\Helpers\SerializationHelper;
use phpGPX\Models\Summarizable;
use PHPUnit\Framework\TestCase;

class SerializationHelperTest extends TestCase
{
	public function testIntegerOrNull(): void
	{
		$this->assertNull(SerializationHelper::integerOrNull(""));
		$this->assertNull(SerializationHelper::integerOrNull(null));
		$this->assertNull(SerializationHelper::integerOrNull("BLA"));
		$this->assertIsInt(SerializationHelper::integerOrNull(5));
		$this->assertIsInt(SerializationHelper::integerOrNull("5"));
	}

	public function testFloatOrNull(): void
	{
		$this->assertNull(SerializationHelper::floatOrNull(""));
		$this->assertNull(SerializationHelper::floatOrNull(null));
		$this->assertNull(SerializationHelper::floatOrNull("BLA"));
		$this->assertIsFloat(SerializationHelper::floatOrNull(5.6));
		$this->assertIsFloat(SerializationHelper::floatOrNull(5));
		$this->assertIsFloat(SerializationHelper::floatOrNull("5.6"));
		$this->assertIsFloat(SerializationHelper::floatOrNull("5"));
	}

	public function testStringOrNull(): void
	{
		$this->assertNull(SerializationHelper::stringOrNull(null));
		$this->assertIsString(SerializationHelper::stringOrNull(""));
		$this->assertIsString(SerializationHelper::stringOrNull("Bla bla"));
	}

	/**
	 * @dataProvider dataProviderFilterNotNull
	 */
	public function testFilterNotNull($expected, $actual): void
	{
		$this->assertEquals($expected, SerializationHelper::filterNotNull($actual));
	}

	public function dataProviderFilterNotNull()
	{
		return [
			'numeric 1' => [
				[],
				[null],
			],
			'numeric 2' => [
				[],
				[null, [null]],
			],
			'numeric 3' => [
				[1 => 1],
				[null, 1],
			],
			'numeric 4' => [
				[1 => 1, 3 => 2],
				[null, 1, null, 2, null],
			],
			'numeric 5' => [
				[1 => 1, 3 => 2, 5 => [0 => 3, 2 => 4], 6 => 5],
				[null, 1, null, 2, null, [3, null, 4], 5, null],
			],
			'associative 1' => [
				[],
				["foo" => null],
			],
			'associative 2' => [
				[],
				["foo" => null, ["bar" => null]],
			],
			'associative 3' => [
				["bar" => 1],
				["foo" => null, "bar" => 1],
			],
			'associative 4' => [
				["bar" => 1, "caw" => 2],
				["foo" => null, "bar" => 1, "baz" => null, "caw" => 2, "doo" => null],
			],
			'associative 5' => [
				["bar" => 1, "caw" => 2, "ere" => ["foo" => 3, "baz" => 4], "moo" => 5],
				["foo" => null, "bar" => 1, "baz" => null, "caw" => 2, "doo" => null, "ere" => ["foo" => 3, "bar" => null, "baz" => 4], "moo" => 5, "boo" => null],
			],
		];
	}

	public function testSerializeWithSingleSummarizableObject(): void
	{
		// Arrange
		$mockObject = $this->createMock(Summarizable::class);
		$mockObject->expects($this->once())
			->method('toArray')
			->willReturn(['id' => 1, 'name' => 'Test']);

		// Act
		$result = SerializationHelper::serialize($mockObject);

		// Assert
		$this->assertEquals(['id' => 1, 'name' => 'Test'], $result);
	}

	public function testSerializeWithArrayOfSummarizableObjects(): void
	{
		// Arrange
		$mockObject1 = $this->createMock(Summarizable::class);
		$mockObject1->expects($this->once())
			->method('toArray')
			->willReturn(['id' => 1, 'name' => 'First']);

		$mockObject2 = $this->createMock(Summarizable::class);
		$mockObject2->expects($this->once())
			->method('toArray')
			->willReturn(['id' => 2, 'name' => 'Second']);

		$mockObject3 = $this->createMock(Summarizable::class);
		$mockObject3->expects($this->once())
			->method('toArray')
			->willReturn(['id' => 3, 'name' => 'Third']);

		$objects = [$mockObject1, $mockObject2, $mockObject3];

		// Act
		$result = SerializationHelper::serialize($objects);

		// Assert
		$expected = [
			['id' => 1, 'name' => 'First'],
			['id' => 2, 'name' => 'Second'],
			['id' => 3, 'name' => 'Third'],
		];
		$this->assertEquals($expected, $result);
	}

	public function testSerializeWithNullObject(): void
	{
		// Act
		$result = SerializationHelper::serialize(null);

		// Assert
		$this->assertNull($result);
	}

	public function testSerializeWithEmptyArray(): void
	{
		// Act
		$result = SerializationHelper::serialize([]);

		// Assert
		$this->assertEquals([], $result);
	}
}
