<?php

declare(strict_types=1);
/**
 * Created            26/08/16 15:26
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DateTime;
use Override;
use phpGPX\Helpers\BoundsCalculator;
use phpGPX\Helpers\DistanceCalculator;
use phpGPX\Helpers\ElevationGainLossCalculator;
use phpGPX\Helpers\SerializationHelper;
use phpGPX\phpGPX;

/**
 * Class Segment
 * A Track Segment holds a list of Track Points which are logically connected in order.
 * To represent a single GPS track where GPS reception was lost, or the GPS receiver was turned off,
 * start a new Track Segment for each continuous span of track data.
 * @see https://www.topografix.com/GPX/1/1/#type_trksegType
 * @package phpGPX\Models
 */
final class Segment implements Summarizable, StatsCalculator
{
	/**
	 * Array of segment points
	 * @var Point[]
	 */
	public $points = [];

	/**
	 * You can add extend GPX by adding your own elements from another schema here.
	 * @var Extensions|null
	 */
	public $extensions;

	/**
	 * @var Stats|null
	 */
	public $stats;

	/**
	 * Serialize object to array
	 * @return array{points: array<int|string, mixed>|null, extensions: array<int|string, mixed>|null, stats: array<int|string, mixed>|null}
	 */
	#[Override]
	public function toArray(): array
	{
		return [
			'points' => SerializationHelper::serialize($this->points),
			'extensions' => SerializationHelper::serialize($this->extensions),
			'stats' => SerializationHelper::serialize($this->stats),
		];
	}

	/**
	 * Return all points in collection.
	 * @return Point[]
	 */
	#[Override]
	public function getPoints(): array
	{
		return $this->points;
	}

	/**
  * Recalculate stats objects.
  */
	#[Override]
	public function recalculateStats(): void
	{
		if (empty($this->stats)) {
			$this->stats = new Stats();
		}

		$count = count($this->points);
		$this->stats->reset();

		if ($this->points === []) {
			return;
		}

		$firstPoint = $this->points[0];
		$lastPoint = end($this->points);

		$this->stats->startedAt = $firstPoint->time;
		$this->stats->startedAtCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];
		$this->stats->finishedAt = $lastPoint->time;
		$this->stats->finishedAtCoords = ["lat" => $lastPoint->latitude, "lng" => $lastPoint->longitude];
		$this->stats->minAltitude = $firstPoint->elevation;
		$this->stats->minAltitudeCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];

		[$this->stats->cumulativeElevationGain, $this->stats->cumulativeElevationLoss] =
			ElevationGainLossCalculator::calculate($this->points);

		$calculator = new DistanceCalculator($this->points);
		$this->stats->distance = $calculator->getRawDistance();
		$this->stats->realDistance = $calculator->getRealDistance();

		for ($i = 0; $i < $count; $i++) {
			if ($this->stats->maxAltitude < $this->points[$i]->elevation) {
				$this->stats->maxAltitude = $this->points[$i]->elevation;
				$this->stats->maxAltitudeCoords = ["lat" => $this->points[$i]->latitude, "lng" => $this->points[$i]->longitude];
			}

			if ((phpGPX::$IGNORE_ELEVATION_0 === false || $this->points[$i]->elevation > 0) && $this->stats->minAltitude > $this->points[$i]->elevation) {
				$this->stats->minAltitude = $this->points[$i]->elevation;
				$this->stats->minAltitudeCoords = ["lat" => $this->points[$i]->latitude, "lng" => $this->points[$i]->longitude];
			}
		}

		if ($firstPoint->time instanceof DateTime && $lastPoint->time instanceof DateTime) {
			$this->stats->duration = $lastPoint->time->getTimestamp() - $firstPoint->time->getTimestamp();

			if ($this->stats->duration !== 0 && $this->stats->distance > 0) {
				$this->stats->averageSpeed = $this->stats->distance / (float) $this->stats->duration;
			}

			if ($this->stats->distance > 0 && $this->stats->duration !== 0) {
				$distanceInKm = $this->stats->distance / 1000.0;
				if ($distanceInKm > 0) {
					$this->stats->averagePace = (float) $this->stats->duration / $distanceInKm;
				}
			}
		}

		[$northWest, $southEast] = BoundsCalculator::calculate($this->points);
		$this->stats->bounds = [$northWest, $southEast];
	}
}
