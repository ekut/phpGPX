<?php

declare(strict_types=1);

namespace phpGPX\Helpers;

use InvalidArgumentException;

/**
 * Validation helper for GPX 1.1 schema compliance.
 *
 * Provides validation methods for all constrained values in the GPX 1.1 specification,
 * including coordinate ranges, degrees, DGPS station IDs, and required strings.
 */
final class GpxValidator
{
	// Coordinate ranges (WGS84 datum)
	public const MIN_LATITUDE = -90.0;
	public const MAX_LATITUDE = 90.0;
	public const MIN_LONGITUDE = -180.0;
	public const MAX_LONGITUDE = 180.0;  // exclusive

	// Degrees range (for magnetic variation, bearing, etc.)
	public const MIN_DEGREES = 0.0;
	public const MAX_DEGREES = 360.0;  // exclusive

	// DGPS station ID range
	public const MIN_DGPS_STATION = 0;
	public const MAX_DGPS_STATION = 1023;

	/**
	 * Validate latitude is within WGS84 datum range.
	 *
	 * @param float $latitude The latitude value to validate
	 * @throws InvalidArgumentException If latitude is outside [-90.0, 90.0] range
	 */
	public static function validateLatitude(float $latitude): void
	{
		if ($latitude < self::MIN_LATITUDE || $latitude > self::MAX_LATITUDE) {
			throw new InvalidArgumentException(
				sprintf(
					'Latitude must be between %f and %f degrees (WGS84 datum). Got: %f',
					self::MIN_LATITUDE,
					self::MAX_LATITUDE,
					$latitude,
				),
			);
		}
	}

	/**
	 * Validate longitude is within WGS84 datum range.
	 *
	 * @param float $longitude The longitude value to validate
	 * @throws InvalidArgumentException If longitude is outside [-180.0, 180.0) range
	 */
	public static function validateLongitude(float $longitude): void
	{
		if ($longitude < self::MIN_LONGITUDE || $longitude >= self::MAX_LONGITUDE) {
			throw new InvalidArgumentException(
				sprintf(
					'Longitude must be between %f (inclusive) and %f (exclusive) degrees (WGS84 datum). Got: %f',
					self::MIN_LONGITUDE,
					self::MAX_LONGITUDE,
					$longitude,
				),
			);
		}
	}

	/**
	 * Validate degrees value (for magnetic variation, bearing, etc.).
	 *
	 * @param float $degrees The degrees value to validate
	 * @throws InvalidArgumentException If degrees is outside [0.0, 360.0) range
	 */
	public static function validateDegrees(float $degrees): void
	{
		if ($degrees < self::MIN_DEGREES || $degrees >= self::MAX_DEGREES) {
			throw new InvalidArgumentException(
				sprintf(
					'Degrees must be between %f (inclusive) and %f (exclusive). Got: %f',
					self::MIN_DEGREES,
					self::MAX_DEGREES,
					$degrees,
				),
			);
		}
	}

	/**
	 * Validate DGPS station ID is within valid range.
	 *
	 * @param int $stationId The DGPS station ID to validate
	 * @throws InvalidArgumentException If station ID is outside [0, 1023] range
	 */
	public static function validateDgpsStation(int $stationId): void
	{
		if ($stationId < self::MIN_DGPS_STATION || $stationId > self::MAX_DGPS_STATION) {
			throw new InvalidArgumentException(
				sprintf(
					'DGPS station ID must be between %d and %d (inclusive). Got: %d',
					self::MIN_DGPS_STATION,
					self::MAX_DGPS_STATION,
					$stationId,
				),
			);
		}
	}

	/**
	 * Validate integer value is non-negative.
	 *
	 * @param int $value The integer value to validate
	 * @param string $fieldName The name of the field being validated (for error message)
	 * @throws InvalidArgumentException If value is negative
	 */
	public static function validateNonNegativeInteger(int $value, string $fieldName): void
	{
		if ($value < 0) {
			throw new InvalidArgumentException(
				sprintf(
					'%s must be non-negative. Got: %d',
					$fieldName,
					$value,
				),
			);
		}
	}

	/**
	 * Validate string is non-empty (not empty or whitespace-only).
	 *
	 * @param string $value The string value to validate
	 * @param string $fieldName The name of the field being validated (for error message)
	 * @throws InvalidArgumentException If string is empty or whitespace-only
	 */
	public static function validateNonEmptyString(string $value, string $fieldName): void
	{
		if (trim($value) === '') {
			throw new InvalidArgumentException(
				sprintf(
					'%s is required and cannot be empty (GPX 1.1 schema)',
					$fieldName,
				),
			);
		}
	}
}
