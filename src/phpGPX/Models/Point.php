<?php

declare(strict_types=1);

/**
 * Created            26/08/16 14:22
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DateTime;
use InvalidArgumentException;
use Override;
use phpGPX\Enums\PointType;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\Helpers\GpxValidator;
use phpGPX\Helpers\SerializationHelper;
use phpGPX\phpGPX;

/**
 * Class Point
 * GPX point representation according to GPX 1.1 specification.
 * @see https://www.topografix.com/GPX/1/1/#type_wptType
 * @package phpGPX\Models
 * @psalm-api
 */
final class Point implements Summarizable
{
	// Legacy constants for backward compatibility - use PointType enum instead
	public const string WAYPOINT = 'waypoint';

	public const string TRACKPOINT = 'track';

	public const string ROUTEPOINT = 'route';

	/**
	 * The latitude of the point. Decimal degrees, WGS84 datum.
	 * Original GPX 1.1 attribute.
	 */
	public float $latitude;

	/**
	 * The longitude of the point. Decimal degrees, WGS84 datum.
	 * Original GPX 1.1 attribute.
	 */
	public float $longitude;

	/**
	 * Elevation (in meters) of the point.
	 * Original GPX 1.1 attribute.
	 */
	public ?float $elevation = null;

	/**
	 * Creation/modification timestamp for element. Date and time in are in Univeral Coordinated Time (UTC), not local time!
	 * Fractional seconds are allowed for millisecond timing in tracklogs.
	 */
	public ?DateTime $time = null;

	/**
	 * Magnetic variation (in degrees) at the point
	 * Original GPX 1.1 attribute.
	 */
	public ?float $magVar = null;

	/**
	 * Height (in meters) of geoid (mean sea level) above WGS84 earth ellipsoid. As defined in NMEA GGA message.
	 * Original GPX 1.1 attribute.
	 */
	public ?float $geoidHeight = null;

	/**
	 * The GPS name of the waypoint. This field will be transferred to and from the GPS.
	 * GPX does not place restrictions on the length of this field or the characters contained in it.
	 * It is up to the receiving application to validate the field before sending it to the GPS.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $name = null;

	/**
	 * GPS waypoint comment. Sent to GPS as comment.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $comment = null;

	/**
	 * A text description of the element. Holds additional information about the element intended for the user, not the GPS.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $description = null;

	/**
	 * Source of data. Included to give user some idea of reliability and accuracy of data. "Garmin eTrex", "USGS quad Boston North", e.g.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $source = null;

	/**
	 * Link to additional information about the waypoint.
	 * Original GPX 1.1 attribute.
	 * @var Link[]
	 */
	public array $links = [];

	/**
	 * Text of GPS symbol name. For interchange with other programs, use the exact spelling of the symbol as displayed on the GPS.
	 * If the GPS abbreviates words, spell them out.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $symbol = null;

	/**
	 * Type (classification) of the waypoint.
	 * Original GPX 1.1 attribute.
	 */
	public ?string $type = null;

	/**
	 * Type of GPS fix. none means GPS had no fix. To signify "the fix info is unknown, leave out fixType entirely. pps = military signal used
	 * Possible values: {'none'|'2d'|'3d'|'dgps'|'pps'}
	 * Original GPX 1.1 attribute.
	 * @see https://www.topografix.com/GPX/1/1/#type_fixType
	 */
	public ?string $fix = null;

	/**
	 * Number of satellites used to calculate the GPX fix. Always positive value.
	 * Original GPX 1.1 attribute.
	 */
	public ?int $satellitesNumber = null;

	/**
	 * Horizontal dilution of precision.
	 * Original GPX 1.1 attribute.
	 */
	public ?float $hdop = null;

	/**
	 * Vertical dilution of precision.
	 * Original GPX 1.1 attribute.
	 */
	public ?float $vdop = null;

	/**
	 * Position dilution of precision.
	 * Original GPX 1.1 attribute
	 */
	public ?float $pdop = null;

	/**
	 * Number of seconds since last DGPS update.
	 * Original GPX 1.1 attribute.
	 */
	public ?int $ageOfGpsData = null;

	/**
	 * ID of DGPS station used in differential correction.
	 * Original GPX 1.1 attribute.
	 * @see https://www.topografix.com/GPX/1/1/#type_dgpsStationType
	 */
	public ?int $dgpsid = null;

	/**
	 * Difference in in distance (in meters) between last point.
	 * Value is created by phpGPX library.
	 */
	public ?float $difference = null;

	/**
	 * Distance from collection start in meters.
	 * Value is created by phpGPX library.
	 */
	public ?float $distance = null;

	/**
	 * Objects stores GPX extensions from another namespaces.
	 */
	public ?Extensions $extensions = null;

	/**
	 * Type of the point (parent collation type (ROUTE|WAYPOINT|TRACK))
	 */
	private readonly PointType $pointType;

	/**
	 * Point constructor.
	 *
	 * @param PointType|string $pointType Type of the point (WAYPOINT, TRACKPOINT, or ROUTEPOINT)
	 * @param float $latitude Latitude in decimal degrees (WGS84 datum), must be between -90.0 and 90.0
	 * @param float $longitude Longitude in decimal degrees (WGS84 datum), must be between -180.0 (inclusive) and 180.0 (exclusive)
	 * @throws InvalidArgumentException If latitude or longitude is outside valid range
	 */
	public function __construct(PointType|string $pointType, float $latitude, float $longitude)
	{
		// Support both enum and legacy string values for backward compatibility
		$this->pointType = $pointType instanceof PointType ? $pointType : PointType::from($pointType);

		// Validate coordinates using GpxValidator
		GpxValidator::validateLatitude($latitude);
		GpxValidator::validateLongitude($longitude);

		// Set validated coordinates
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	/**
	 * Return point type (ROUTE|TRACK|WAYPOINT)
	 */
	public function getPointType(): PointType
	{
		return $this->pointType;
	}

	/**
	 * Set magnetic variation value with validation.
	 *
	 * @param float|null $magVar Magnetic variation in degrees, must be between 0.0 (inclusive) and 360.0 (exclusive)
	 * @throws InvalidArgumentException If magnetic variation is outside valid range
	 * @api
	 * @psalm-api
	 */
	public function setMagVar(?float $magVar): void
	{
		if ($magVar !== null) {
			GpxValidator::validateDegrees($magVar);
		}

		$this->magVar = $magVar;
	}

	/**
	 * Set DGPS station ID with validation.
	 *
	 * @param int|null $dgpsId DGPS station ID, must be between 0 and 1023 (inclusive)
	 * @throws InvalidArgumentException If DGPS station ID is outside valid range
	 * @api
	 * @psalm-api
	 */
	public function setDgpsId(?int $dgpsId): void
	{
		if ($dgpsId !== null) {
			GpxValidator::validateDgpsStation($dgpsId);
		}

		$this->dgpsid = $dgpsId;
	}

	/**
	 * Set satellite count with validation.
	 *
	 * @param int|null $sat Number of satellites, must be non-negative
	 * @throws InvalidArgumentException If satellite count is negative
	 * @api
	 * @psalm-api
	 */
	public function setSat(?int $sat): void
	{
		if ($sat !== null) {
			GpxValidator::validateNonNegativeInteger($sat, 'Satellite count');
		}

		$this->satellitesNumber = $sat;
	}

	/**
	 * Serialize object to array
	 * @return array<string, mixed>
	 */
	#[Override]
	public function toArray(): array
	{
		return [
			'lat' => $this->latitude,
			'lon' => $this->longitude,
			'ele' => SerializationHelper::floatOrNull($this->elevation),
			'time' => DateTimeHelper::formatDateTime($this->time, phpGPX::$DATETIME_FORMAT, phpGPX::$DATETIME_TIMEZONE_OUTPUT),
			'magvar' => SerializationHelper::floatOrNull($this->magVar),
			'geoidheight' => SerializationHelper::floatOrNull($this->geoidHeight),
			'name' => SerializationHelper::stringOrNull($this->name),
			'cmt' => SerializationHelper::stringOrNull($this->comment),
			'desc' => SerializationHelper::stringOrNull($this->description),
			'src' => SerializationHelper::stringOrNull($this->source),
			'link' => SerializationHelper::serialize($this->links),
			'sym' => SerializationHelper::stringOrNull($this->symbol),
			'type' => SerializationHelper::stringOrNull($this->type),
			'fix' => SerializationHelper::stringOrNull($this->fix),
			'sat' => SerializationHelper::integerOrNull($this->satellitesNumber),
			'hdop' => SerializationHelper::floatOrNull($this->hdop),
			'vdop' => SerializationHelper::floatOrNull($this->vdop),
			'pdop' => SerializationHelper::floatOrNull($this->pdop),
			'ageofdgpsdata' => SerializationHelper::floatOrNull($this->ageOfGpsData),
			'dgpsid' => SerializationHelper::integerOrNull($this->dgpsid),
			'difference' => SerializationHelper::floatOrNull($this->difference),
			'distance' => SerializationHelper::floatOrNull($this->distance),
			'extensions' => SerializationHelper::serialize($this->extensions),
		];
	}
}
