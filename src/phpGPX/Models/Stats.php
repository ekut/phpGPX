<?php
/**
 * Created            30/08/16 17:12
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\DateTimeHelper;
use phpGPX\phpGPX;

/**
 * Class Stats
 * @package phpGPX\Models
 */
class Stats implements Summarizable
{

	/**
	 * Distance in meters (m)
	 * @var float
	 */
	public $distance = 0;

	/**
	 * Distance in meters (m) including elevation loss/gain
	 * @var float
	 */
	public $realDistance = 0;

	/**
	 * Average speed in meters per second (m/s)
	 * @var float
	 */
	public $averageSpeed;

	/**
	 * Average pace in seconds per kilometer (s/km)
	 * @var float
	 */
	public $averagePace;

	/**
	 * Minimal altitude in meters (m)
	 * @var int
	 */
	public $minAltitude;

	/**
	 * Minimal altitude coordinate
	 * @var [float,float]
	 */
	public $minAltitudeCoords;

	/**
	 * Maximal altitude in meters (m)
	 * @var int
	 */
	public $maxAltitude;

	/**
	 * Maximal altitude coordinate
	 * @var [float,float]
	 */
	public $maxAltitudeCoords;

	/**
	 * Cumulative elevation gain in meters (m)
	 * @var int
	 */
	public $cumulativeElevationGain;

	/**
	 * Cumulative elevation loss in meters (m)
	 * @var int
	 */
	public $cumulativeElevationLoss;

	/**
	 * Started time
	 * @var \DateTime
	 */
	public $startedAt;

	/**
	 * startedAt coordinate
	 * @var [float,float]
	 */
	public $startedAtCoords;

	/**
	 * Ending time
	 * @var \DateTime
	 */
	public $finishedAt;

	/**
	 * finishedAt coordinate
	 * @var [float,float]
	 */
	public $finishedAtCoords;

	/**
	 * Duration is seconds
	 * @var int
	 */
	public $duration;

	/**
	 * An array of two points representing
	 * the most northwestern and the most
	 * southeastern points of a segment
	 * @var array
	 */
	public $bounds = [];

	/**
	 * Reset all stats
	 */
	public function reset(): void
	{
		$this->distance = null;
		$this->realDistance = null;
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
		$this->bounds = null;
	}

	/**
  * Serialize object to array
  */
 public function toArray(): array
	{
		return [
			'distance' => (float)$this->distance,
			'realDistance' => (float)$this->realDistance,
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
			'bounds' => $this->bounds
		];
	}
}
