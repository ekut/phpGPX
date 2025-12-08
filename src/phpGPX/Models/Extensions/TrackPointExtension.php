<?php

declare(strict_types=1);

/**
 * Created            26/08/16 17:05
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models\Extensions;

use phpGPX\Helpers\SerializationHelper;

/**
 * Class TrackPointExtension
 * Extension version: v2
 * Based on namespace: http://www.garmin.com/xmlschemas/TrackPointExtensionv2.xsd
 * @package phpGPX\Models\Extensions
 * @psalm-api
 */
final class TrackPointExtension extends AbstractExtension
{
	public const EXTENSION_V1_NAMESPACE = 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1';
	public const EXTENSION_V1_NAMESPACE_XSD = 'http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd';

	public const EXTENSION_NAMESPACE = 'http://www.garmin.com/xmlschemas/TrackPointExtension/v2';
	public const EXTENSION_NAMESPACE_XSD = 'http://www.garmin.com/xmlschemas/TrackPointExtensionv2.xsd';

	public const EXTENSION_NAME = 'TrackPointExtension';
	public const EXTENSION_NAMESPACE_PREFIX = 'gpxtpx';

	/**
	 * Average temperature value measured in degrees Celsius.
	 */
	public ?float $aTemp = null;

	/**
	 * Average temperature value measured in degrees Celsius.
	 * @deprecated use TrackPointExtension::$aTemp instead. Will be removed in v1.0
	 * @see TrackPointExtension::$aTemp
	 * @api
	 * @psalm-api
	 */
	public ?float $avgTemperature = null;

	/**
	 * Water temperature value measured in degrees Celsius.
	 */
	public ?float $wTemp = null;

	/**
	 * Depth in meters.
	 */
	public ?float $depth = null;

	/**
	 * Heart rate in beats per minute.
	 * @deprecated since v1.0RC3, use attribute TrackPointExtension::$hr instead, will be removed in v1.0
	 * @see TrackPointExtension::$hr
	 * @api
	 * @psalm-api
	 */
	public ?float $heartRate = null;

	/**
	 * Heart rate in beats per minute.
	 * @since v1.0RC3
	 */
	public ?float $hr = null;

	/**
	 * Cadence in revolutions per minute.
	 * @deprecated since v1.0RC3, use attribute TrackPointExtension::$cad instead, will be removed in v1.0
	 * @see TrackPointExtension::$cad
	 * @api
	 * @psalm-api
	 */
	public ?float $cadence = null;

	/**
	 * Cadence in revolutions per minute.
	 */
	public ?float $cad = null;

	/**
	 * Speed in meters per second.
	 */
	public ?float $speed = null;

	/**
	 * Course. This type contains an angle measured in degrees in a clockwise direction from the true north line.
	 */
	public ?int $course = null;

	/**
	 * Bearing. This type contains an angle measured in degrees in a clockwise direction from the true north line.
	 */
	public ?int $bearing = null;

	/**
	 * TrackPointExtension constructor.
	 */
	public function __construct()
	{
		parent::__construct(self::EXTENSION_NAMESPACE, self::EXTENSION_NAME);
	}

	/**
	 * Serialize object to array
	 * @return array{aTemp: float|null, wTemp: float|null, depth: float|null, hr: float|null, cad: float|null, speed: float|null, course: int|null, bearing: int|null}
	 */
	#[\Override]
	public function toArray(): array
	{
		return [
			'aTemp' => SerializationHelper::floatOrNull($this->aTemp),
			'wTemp' => SerializationHelper::floatOrNull($this->wTemp),
			'depth' => SerializationHelper::floatOrNull($this->depth),
			'hr' => SerializationHelper::floatOrNull($this->hr),
			'cad' => SerializationHelper::floatOrNull($this->cad),
			'speed' => SerializationHelper::floatOrNull($this->speed),
			'course' => SerializationHelper::integerOrNull($this->course),
			'bearing' => SerializationHelper::integerOrNull($this->bearing),
		];
	}
}
