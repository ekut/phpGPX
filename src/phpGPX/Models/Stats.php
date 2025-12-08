<?php

declare(strict_types=1);

/**
 * Created            30/08/16 17:12
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DateTime;
use Override;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\phpGPX;

/**
 * Class Stats
 * @package phpGPX\Models
 */
final class Stats implements Summarizable
{
	/**
	 * Distance in meters (m)
	 */
	public float $distance = 0;

	/**
	 * Distance in meters (m) including elevation loss/gain
	 */
	public float $realDistance = 0;

	/**
	 * Average speed in meters per second (m/s)
	 */
	public ?float $averageSpeed = null;

	/**
	 * Average pace in seconds per kilometer (s/km)
	 */
	public ?float $averagePace = null;

	/**
	 * Minimal altitude in meters (m)
	 */
	public ?float $minAltitude = null;

	/**
	 * Minimal altitude coordinate
	 * @var array{lat: float, lng: float}|null
	 */
	public ?array $minAltitudeCoords = null;

	/**
	 * Maximal altitude in meters (m)
	 */
	public ?float $maxAltitude = null;

	/**
	 * Maximal altitude coordinate
	 * @var array{lat: float, lng: float}|null
	 */
	public ?array $maxAltitudeCoords = null;

	/**
	 * Cumulative elevation gain in meters (m)
	 */
	public ?float $cumulativeElevationGain = null;

	/**
	 * Cumulative elevation loss in meters (m)
	 */
	public ?float $cumulativeElevationLoss = null;

	/**
	 * Started time
	 */
	public ?DateTime $startedAt = null;

	/**
	 * startedAt coordinate
	 * @var array{lat: float, lng: float}|null
	 */
	public ?array $startedAtCoords = null;

	/**
	 * Ending time
	 */
	public ?DateTime $finishedAt = null;

	/**
	 * finishedAt coordinate
	 * @var array{lat: float, lng: float}|null
	 */
	public ?array $finishedAtCoords = null;

	/**
	 * Duration is seconds
	 */
	public ?int $duration = null;

	/**
	 * An array of two points representing
	 * the most northwestern and the most
	 * southeastern points of a segment
	 * @var array<int, mixed>|null
	 */
	public ?array $bounds = null;

	/**
	 * Reset all stats
	 */
	public function reset(): void
	{
		$this->distance = 0;
		$this->realDistance = 0;
		$this->averageSpeed = null;
		$this->averagePace = null;
		$this->minAltitude = null;
		$this->maxAltitude = null;
		$this->minAltitudeCoords = null;
		$this->maxAltitudeCoords = null;
		$this->cumulativeElevationGain = null;
		$this->cumulativeElevationLoss = null;
		$this->startedAt = null;
		$this->startedAtCoords = null;
		$this->finishedAt = null;
		$this->finishedAtCoords = null;
		$this->duration = null;
		$this->bounds = null;
	}

	/**
	 * Serialize object to array
	 * @return array<string, mixed>
	 */
	#[Override]
	public function toArray(): array
	{
		return [
			'distance' => $this->distance,
			'realDistance' => $this->realDistance,
			'avgSpeed' => (float)$this->averageSpeed,
			'avgPace' => (float)$this->averagePace,
			'minAltitude' => (float)$this->minAltitude,
			'minAltitudeCoords' => $this->minAltitudeCoords,
			'maxAltitude' => (float)$this->maxAltitude,
			'maxAltitudeCoords' => $this->maxAltitudeCoords,
			'cumulativeElevationGain' => (float)$this->cumulativeElevationGain,
			'cumulativeElevationLoss' => (float)$this->cumulativeElevationLoss,
			'startedAt' => DateTimeHelper::formatDateTime($this->startedAt, phpGPX::$DATETIME_FORMAT, phpGPX::$DATETIME_TIMEZONE_OUTPUT),
			'startedAtCoords' => $this->startedAtCoords,
			'finishedAt' => DateTimeHelper::formatDateTime($this->finishedAt, phpGPX::$DATETIME_FORMAT, phpGPX::$DATETIME_TIMEZONE_OUTPUT),
			'finishedAtCoords' => $this->finishedAtCoords,
			'duration' => (float)$this->duration,
			'bounds' => $this->bounds,
		];
	}
}
