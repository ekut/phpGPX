<?php

declare(strict_types=1);

namespace UnitTests\phpGPX\Helpers;

use InvalidArgumentException;
use phpGPX\Helpers\GpxValidator;
use PHPUnit\Framework\TestCase;

class GpxValidatorTest extends TestCase
{
	// ========================================
	// validateLatitude tests
	// ========================================

	public function test_validate_latitude_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateLatitude(0.0);
		GpxValidator::validateLatitude(45.5);
		GpxValidator::validateLatitude(-45.5);
		GpxValidator::validateLatitude(90.0);  // max boundary
		GpxValidator::validateLatitude(-90.0); // min boundary

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_latitude_rejects_value_above_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('90.1');

		// Act
		GpxValidator::validateLatitude(90.1);
	}

	public function test_validate_latitude_rejects_value_below_minimum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('-90.1');

		// Act
		GpxValidator::validateLatitude(-90.1);
	}

	public function test_validate_latitude_error_message_contains_range_and_value(): void
	{
		// Arrange
		$invalidValue = 95.5;

		try {
			// Act
			GpxValidator::validateLatitude($invalidValue);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString('-90.0', $message);
			$this->assertStringContainsString('90.0', $message);
			$this->assertStringContainsString('95.5', $message);
			$this->assertStringContainsString('WGS84', $message);
		}
	}

	// ========================================
	// validateLongitude tests
	// ========================================

	public function test_validate_longitude_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateLongitude(0.0);
		GpxValidator::validateLongitude(90.5);
		GpxValidator::validateLongitude(-90.5);
		GpxValidator::validateLongitude(-180.0); // min boundary (inclusive)
		GpxValidator::validateLongitude(179.999); // just below max boundary

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_longitude_rejects_value_at_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('exclusive');

		// Act
		GpxValidator::validateLongitude(180.0); // exclusive boundary
	}

	public function test_validate_longitude_rejects_value_above_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('180.1');

		// Act
		GpxValidator::validateLongitude(180.1);
	}

	public function test_validate_longitude_rejects_value_below_minimum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('-180.1');

		// Act
		GpxValidator::validateLongitude(-180.1);
	}

	public function test_validate_longitude_error_message_contains_range_and_value(): void
	{
		// Arrange
		$invalidValue = 185.5;

		try {
			// Act
			GpxValidator::validateLongitude($invalidValue);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString('-180.0', $message);
			$this->assertStringContainsString('180.0', $message);
			$this->assertStringContainsString('185.5', $message);
			$this->assertStringContainsString('inclusive', $message);
			$this->assertStringContainsString('exclusive', $message);
			$this->assertStringContainsString('WGS84', $message);
		}
	}

	// ========================================
	// validateDegrees tests
	// ========================================

	public function test_validate_degrees_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateDegrees(0.0);   // min boundary (inclusive)
		GpxValidator::validateDegrees(45.5);
		GpxValidator::validateDegrees(180.0);
		GpxValidator::validateDegrees(270.5);
		GpxValidator::validateDegrees(359.999); // just below max boundary

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_degrees_rejects_value_at_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0.0');
		$this->expectExceptionMessage('360.0');
		$this->expectExceptionMessage('exclusive');

		// Act
		GpxValidator::validateDegrees(360.0); // exclusive boundary
	}

	public function test_validate_degrees_rejects_value_above_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0.0');
		$this->expectExceptionMessage('360.0');
		$this->expectExceptionMessage('360.1');

		// Act
		GpxValidator::validateDegrees(360.1);
	}

	public function test_validate_degrees_rejects_negative_value(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0.0');
		$this->expectExceptionMessage('360.0');
		$this->expectExceptionMessage('-0.1');

		// Act
		GpxValidator::validateDegrees(-0.1);
	}

	public function test_validate_degrees_error_message_contains_range_and_value(): void
	{
		// Arrange
		$invalidValue = 365.5;

		try {
			// Act
			GpxValidator::validateDegrees($invalidValue);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString('0.0', $message);
			$this->assertStringContainsString('360.0', $message);
			$this->assertStringContainsString('365.5', $message);
			$this->assertStringContainsString('inclusive', $message);
			$this->assertStringContainsString('exclusive', $message);
		}
	}

	// ========================================
	// validateDgpsStation tests
	// ========================================

	public function test_validate_dgps_station_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateDgpsStation(0);    // min boundary
		GpxValidator::validateDgpsStation(512);
		GpxValidator::validateDgpsStation(1023); // max boundary

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_dgps_station_rejects_negative_value(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0');
		$this->expectExceptionMessage('1023');
		$this->expectExceptionMessage('-1');

		// Act
		GpxValidator::validateDgpsStation(-1);
	}

	public function test_validate_dgps_station_rejects_value_above_maximum(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0');
		$this->expectExceptionMessage('1023');
		$this->expectExceptionMessage('1024');

		// Act
		GpxValidator::validateDgpsStation(1024);
	}

	public function test_validate_dgps_station_error_message_contains_range_and_value(): void
	{
		// Arrange
		$invalidValue = 1500;

		try {
			// Act
			GpxValidator::validateDgpsStation($invalidValue);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString('0', $message);
			$this->assertStringContainsString('1023', $message);
			$this->assertStringContainsString('1500', $message);
			$this->assertStringContainsString('DGPS station ID', $message);
		}
	}

	// ========================================
	// validateNonNegativeInteger tests
	// ========================================

	public function test_validate_non_negative_integer_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateNonNegativeInteger(0, 'Test field');
		GpxValidator::validateNonNegativeInteger(1, 'Test field');
		GpxValidator::validateNonNegativeInteger(100, 'Test field');
		GpxValidator::validateNonNegativeInteger(999999, 'Test field');

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_non_negative_integer_rejects_negative_value(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Satellite count');
		$this->expectExceptionMessage('non-negative');
		$this->expectExceptionMessage('-1');

		// Act
		GpxValidator::validateNonNegativeInteger(-1, 'Satellite count');
	}

	public function test_validate_non_negative_integer_error_message_contains_field_name_and_value(): void
	{
		// Arrange
		$invalidValue = -5;
		$fieldName = 'Number of satellites';

		try {
			// Act
			GpxValidator::validateNonNegativeInteger($invalidValue, $fieldName);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString($fieldName, $message);
			$this->assertStringContainsString('-5', $message);
			$this->assertStringContainsString('non-negative', $message);
		}
	}

	// ========================================
	// validateNonEmptyString tests
	// ========================================

	public function test_validate_non_empty_string_accepts_valid_values(): void
	{
		// Arrange & Act & Assert - should not throw
		GpxValidator::validateNonEmptyString('valid string', 'Test field');
		GpxValidator::validateNonEmptyString('a', 'Test field');
		GpxValidator::validateNonEmptyString('  text with spaces  ', 'Test field');
		GpxValidator::validateNonEmptyString('123', 'Test field');

		$this->assertTrue(true); // If we get here, validation passed
	}

	public function test_validate_non_empty_string_rejects_empty_string(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Link href');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');
		$this->expectExceptionMessage('GPX 1.1 schema');

		// Act
		GpxValidator::validateNonEmptyString('', 'Link href');
	}

	public function test_validate_non_empty_string_rejects_whitespace_only_string(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Copyright author');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		GpxValidator::validateNonEmptyString('   ', 'Copyright author');
	}

	public function test_validate_non_empty_string_rejects_tab_only_string(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Email id');

		// Act
		GpxValidator::validateNonEmptyString("\t\t", 'Email id');
	}

	public function test_validate_non_empty_string_rejects_newline_only_string(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Creator');

		// Act
		GpxValidator::validateNonEmptyString("\n\n", 'Creator');
	}

	public function test_validate_non_empty_string_error_message_contains_field_name(): void
	{
		// Arrange
		$fieldName = 'GPX creator attribute';

		try {
			// Act
			GpxValidator::validateNonEmptyString('', $fieldName);
			$this->fail('Expected InvalidArgumentException was not thrown');
		} catch (InvalidArgumentException $e) {
			// Assert
			$message = $e->getMessage();
			$this->assertStringContainsString($fieldName, $message);
			$this->assertStringContainsString('required', $message);
			$this->assertStringContainsString('cannot be empty', $message);
			$this->assertStringContainsString('GPX 1.1 schema', $message);
		}
	}

	// ========================================
	// Constants tests
	// ========================================

	public function test_constants_have_correct_values(): void
	{
		// Assert coordinate ranges
		$this->assertEquals(-90.0, GpxValidator::MIN_LATITUDE);
		$this->assertEquals(90.0, GpxValidator::MAX_LATITUDE);
		$this->assertEquals(-180.0, GpxValidator::MIN_LONGITUDE);
		$this->assertEquals(180.0, GpxValidator::MAX_LONGITUDE);

		// Assert degrees range
		$this->assertEquals(0.0, GpxValidator::MIN_DEGREES);
		$this->assertEquals(360.0, GpxValidator::MAX_DEGREES);

		// Assert DGPS station range
		$this->assertEquals(0, GpxValidator::MIN_DGPS_STATION);
		$this->assertEquals(1023, GpxValidator::MAX_DGPS_STATION);
	}
}
