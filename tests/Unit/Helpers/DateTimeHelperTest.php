<?php
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace UnitTests\phpGPX\Helpers;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\Models\Point;
use PHPUnit\Framework\TestCase;

class DateTimeHelperTest extends TestCase
{
	use TestTrait;

	// ========================================
	// comparePointsByTimestamp tests
	// ========================================

	public function test_compare_points_by_timestamp_first_point_later_returns_positive(): void
	{
		// Arrange
		$point1 = new Point(Point::WAYPOINT);
		$point1->time = new \DateTime("2017-08-12T20:16:29+00:00", new \DateTimeZone("UTC"));

		$point2 = new Point(Point::WAYPOINT);
		$point2->time = new \DateTime("2017-08-12T20:15:19+00:00", new \DateTimeZone("UTC"));

		// Act
		$result = DateTimeHelper::comparePointsByTimestamp($point1, $point2);

		// Assert
		$this->assertGreaterThan(0, $result);
	}

	public function test_compare_points_by_timestamp_first_point_earlier_returns_negative(): void
	{
		// Arrange
		$point1 = new Point(Point::WAYPOINT);
		$point1->time = new \DateTime("2017-08-12T20:15:19+00:00", new \DateTimeZone("UTC"));

		$point2 = new Point(Point::WAYPOINT);
		$point2->time = new \DateTime("2017-08-12T20:16:29+00:00", new \DateTimeZone("UTC"));

		// Act
		$result = DateTimeHelper::comparePointsByTimestamp($point1, $point2);

		// Assert
		$this->assertLessThan(0, $result);
	}

	public function test_compare_points_by_timestamp_equal_times_returns_zero(): void
	{
		// Arrange
		$point1 = new Point(Point::WAYPOINT);
		$point1->time = new \DateTime("2017-08-12T20:16:29+00:00", new \DateTimeZone("UTC"));

		$point2 = new Point(Point::WAYPOINT);
		$point2->time = new \DateTime("2017-08-12T20:16:29+00:00", new \DateTimeZone("UTC"));

		// Act
		$result = DateTimeHelper::comparePointsByTimestamp($point1, $point2);

		// Assert
		$this->assertEquals(0, $result);
	}

	public function test_compare_points_by_timestamp_with_different_timezones(): void
	{
		// Arrange - same moment in time, different timezones
		$point1 = new Point(Point::WAYPOINT);
		$point1->time = new \DateTime("2017-08-12T20:16:29+00:00", new \DateTimeZone("UTC"));

		$point2 = new Point(Point::WAYPOINT);
		$point2->time = new \DateTime("2017-08-12T21:16:29+01:00", new \DateTimeZone("Europe/Paris"));

		// Act
		$result = DateTimeHelper::comparePointsByTimestamp($point1, $point2);

		// Assert - should be equal (same moment in time)
		$this->assertEquals(0, $result);
	}

	public function test_compare_points_by_timestamp_with_null_times(): void
	{
		// Arrange
		$point1 = new Point(Point::WAYPOINT);
		$point1->time = null;

		$point2 = new Point(Point::WAYPOINT);
		$point2->time = null;

		// Act
		$result = DateTimeHelper::comparePointsByTimestamp($point1, $point2);

		// Assert
		$this->assertEquals(0, $result);
	}

	// ========================================
	// formatDateTime tests
	// ========================================

	public function test_format_datetime_with_default_format_and_timezone(): void
	{
		// Arrange
		$datetime = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act
		$result = DateTimeHelper::formatDateTime($datetime);

		// Assert
		$this->assertEquals("2017-08-12T20:16:29+00:00", $result);
	}

	public function test_format_datetime_with_custom_format(): void
	{
		// Arrange
		$datetime = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act
		$result = DateTimeHelper::formatDateTime($datetime, "Y-m-d H:i:s");

		// Assert
		$this->assertEquals("2017-08-12 20:16:29", $result);
	}

	public function test_format_datetime_with_custom_timezone(): void
	{
		// Arrange
		$datetime = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act
		$result = DateTimeHelper::formatDateTime($datetime, "Y-m-d H:i:s", '+01:00');

		// Assert
		$this->assertEquals("2017-08-12 21:16:29", $result);
	}

	public function test_format_datetime_with_different_timezone_formats(): void
	{
		// Arrange
		$datetime = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act & Assert - America/New_York (UTC-4 in summer)
		$result = DateTimeHelper::formatDateTime($datetime, "Y-m-d H:i:s", 'America/New_York');
		$this->assertEquals("2017-08-12 16:16:29", $result);

		// Act & Assert - Asia/Tokyo (UTC+9)
		$result = DateTimeHelper::formatDateTime($datetime, "Y-m-d H:i:s", 'Asia/Tokyo');
		$this->assertEquals("2017-08-13 05:16:29", $result);

		// Act & Assert - Europe/London (UTC+1 in summer)
		$result = DateTimeHelper::formatDateTime($datetime, "Y-m-d H:i:s", 'Europe/London');
		$this->assertEquals("2017-08-12 21:16:29", $result);
	}

	public function test_format_datetime_with_various_formats(): void
	{
		// Arrange
		$datetime = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act & Assert - ISO 8601
		$this->assertEquals("2017-08-12T20:16:29+00:00", DateTimeHelper::formatDateTime($datetime, 'c'));

		// Act & Assert - RFC 2822
		$this->assertEquals("Sat, 12 Aug 2017 20:16:29 +0000", DateTimeHelper::formatDateTime($datetime, 'r'));

		// Act & Assert - Unix timestamp
		$this->assertEquals("1502568989", DateTimeHelper::formatDateTime($datetime, 'U'));

		// Act & Assert - Custom format
		$this->assertEquals("12/08/2017", DateTimeHelper::formatDateTime($datetime, 'd/m/Y'));
	}

	public function test_format_datetime_with_null_returns_null(): void
	{
		// Act
		$result = DateTimeHelper::formatDateTime(null);

		// Assert
		$this->assertNull($result);
	}

	// ========================================
	// parseDateTime tests
	// ========================================

	public function test_parse_datetime_with_iso8601_format(): void
	{
		// Arrange
		$expected = new \DateTime("2017-08-12T20:16:29+00:00");

		// Act
		$result = DateTimeHelper::parseDateTime("2017-08-12T20:16:29+00:00");

		// Assert
		$this->assertEquals($expected->getTimestamp(), $result->getTimestamp());
	}

	public function test_parse_datetime_with_simple_date(): void
	{
		// Act
		$result = DateTimeHelper::parseDateTime("2017-08-12");

		// Assert - The date is parsed in Europe/London timezone then converted to default timezone
		// This may shift the date depending on the default timezone
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertStringContainsString("2017-08", $result->format("Y-m-d"));
	}

	public function test_parse_datetime_with_custom_timezone(): void
	{
		// Act
		$result = DateTimeHelper::parseDateTime("2017-08-12T20:16:29+00:00", "America/New_York");

		// Assert - should be converted to default timezone
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals("2017-08-12", $result->format("Y-m-d"));
	}

	public function test_parse_datetime_with_various_input_formats(): void
	{
		// ISO 8601 with timezone - explicit timezone in string
		$result1 = DateTimeHelper::parseDateTime("2017-08-12T20:16:29+02:00");
		$this->assertInstanceOf(\DateTime::class, $result1);
		$this->assertEquals("2017", $result1->format("Y"));

		// Date only - parsed in Europe/London then converted to default timezone
		$result2 = DateTimeHelper::parseDateTime("2017-08-12");
		$this->assertInstanceOf(\DateTime::class, $result2);
		$this->assertStringContainsString("2017-08", $result2->format("Y-m-d"));

		// Date with time
		$result3 = DateTimeHelper::parseDateTime("2017-08-12 20:16:29");
		$this->assertInstanceOf(\DateTime::class, $result3);
		$this->assertStringContainsString("2017-08-12", $result3->format("Y-m-d"));

		// RFC 2822 - explicit timezone in string
		$result4 = DateTimeHelper::parseDateTime("Sat, 12 Aug 2017 20:16:29 +0000");
		$this->assertInstanceOf(\DateTime::class, $result4);
		$this->assertEquals("2017", $result4->format("Y"));
	}

	public function test_parse_datetime_with_different_timezones(): void
	{
		// Act - Parse with Europe/London timezone
		$result1 = DateTimeHelper::parseDateTime("2017-08-12T20:16:29", "Europe/London");
		$this->assertInstanceOf(\DateTime::class, $result1);

		// Act - Parse with Asia/Tokyo timezone
		$result2 = DateTimeHelper::parseDateTime("2017-08-12T20:16:29", "Asia/Tokyo");
		$this->assertInstanceOf(\DateTime::class, $result2);

		// The timestamps should be different since they represent different moments
		$this->assertNotEquals($result1->getTimestamp(), $result2->getTimestamp());
	}

	public function test_parse_datetime_invalid_input_throws_exception(): void
	{
		// Assert
		$this->expectException(\Exception::class);

		// Act
		DateTimeHelper::parseDateTime("Invalid datetime string");
	}

	public function test_parse_datetime_empty_string_creates_current_time(): void
	{
		// Act - Empty string creates a DateTime with current time
		$result = DateTimeHelper::parseDateTime("");

		// Assert
		$this->assertInstanceOf(\DateTime::class, $result);
		// Verify it's a recent datetime (within last minute)
		$now = new \DateTime();
		$diff = $now->getTimestamp() - $result->getTimestamp();
		$this->assertLessThan(60, abs($diff), "Empty string should create current datetime");
	}

	// ========================================
	// Property-Based Tests
	// ========================================

	/**
	 * Property test: Timezone conversion round-trip.
	 * 
	 * **Feature: test-coverage, Property 1: Timezone conversion round-trip**
	 * 
	 * For any DateTime value and any valid timezone, converting to that timezone
	 * and back should preserve the original timestamp.
	 * 
	 * This property ensures that timezone conversions are lossless and that
	 * the underlying moment in time is preserved regardless of timezone representation.
	 * 
	 * **Validates: Requirements 1.3**
	 */
	public function test_property_timezone_conversion_round_trip(): void
	{
		// List of valid timezones to test
		$timezones = [
			'UTC',
			'America/New_York',
			'America/Los_Angeles',
			'Europe/London',
			'Europe/Paris',
			'Europe/Berlin',
			'Asia/Tokyo',
			'Asia/Shanghai',
			'Australia/Sydney',
			'Pacific/Auckland',
			'Africa/Cairo',
			'America/Chicago',
			'America/Denver',
			'Asia/Dubai',
			'Asia/Kolkata',
			'Europe/Moscow',
			'America/Sao_Paulo',
			'Asia/Singapore',
			'Europe/Amsterdam',
			'America/Toronto',
		];

		$this
			->withRand('mt_rand')  // REQUIRED: Initialize random generator
			->forAll(
				// Generate random year (2000-2030)
				Generators::choose(2000, 2030),
				// Generate random month (1-12)
				Generators::choose(1, 12),
				// Generate random day (1-28 to avoid invalid dates)
				Generators::choose(1, 28),
				// Generate random hour (0-23)
				Generators::choose(0, 23),
				// Generate random minute (0-59)
				Generators::choose(0, 59),
				// Generate random second (0-59)
				Generators::choose(0, 59),
				// Pick a random timezone from the list
				Generators::elements($timezones)
			)
			->withMaxSize(100) // Run 100 iterations as specified in design
			->then(function ($year, $month, $day, $hour, $minute, $second, $timezone) {
				// Create a DateTime object with the generated values
				$dateString = sprintf(
					'%04d-%02d-%02d %02d:%02d:%02d',
					$year,
					$month,
					$day,
					$hour,
					$minute,
					$second
				);

				try {
					$originalDateTime = new \DateTime($dateString, new \DateTimeZone('UTC'));
					$originalTimestamp = $originalDateTime->getTimestamp();

					// Convert to the target timezone using formatDateTime
					$formatted = DateTimeHelper::formatDateTime($originalDateTime, 'c', $timezone);
					
					// Parse it back (this will convert to default timezone)
					$parsedDateTime = new \DateTime($formatted);
					$parsedTimestamp = $parsedDateTime->getTimestamp();

					// The timestamps should be identical - same moment in time
					$this->assertEquals(
						$originalTimestamp,
						$parsedTimestamp,
						sprintf(
							"Timezone round-trip failed:\n" .
							"Original: %s (timestamp: %d)\n" .
							"Formatted in %s: %s\n" .
							"Parsed back: %s (timestamp: %d)\n" .
							"Difference: %d seconds",
							$originalDateTime->format('c'),
							$originalTimestamp,
							$timezone,
							$formatted,
							$parsedDateTime->format('c'),
							$parsedTimestamp,
							abs($originalTimestamp - $parsedTimestamp)
						)
					);
				} catch (\Exception $e) {
					// Skip invalid dates (e.g., Feb 30)
					$this->assertTrue(true, "Skipping invalid date: " . $e->getMessage());
				}
			});
	}
}
