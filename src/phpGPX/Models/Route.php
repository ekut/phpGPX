<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 18:21
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DateTime;
use Override;
use phpGPX\Helpers\DistanceCalculator;
use phpGPX\Helpers\ElevationGainLossCalculator;
use phpGPX\Helpers\SerializationHelper;
use phpGPX\phpGPX;

/**
 * Class Route
 * Represents a route - an ordered list of waypoints representing a series of turn points leading to a destination.
 * @see https://www.topografix.com/GPX/1/1/#type_rteType
 * @package phpGPX\Models
 */
final class Route extends Collection
{
	/**
	 * A list of route points.
	 * An original GPX 1.1 attribute.
	 * @var Point[]
	 */
	public $points = [];

	/**
	 * Return all points in collection.
	 * @return Point[]
	 */
	#[Override]
	public function getPoints(): array
	{
		/** @var Point[] $points */
		$points = [];

		$points = array_merge($points, $this->points);

		if (phpGPX::$SORT_BY_TIMESTAMP && $points !== [] && $points[0]->time !== null) {
			usort($points, ['phpGPX\Helpers\DateTimeHelper', 'comparePointsByTimestamp']);
		}

		return $points;
	}

	/**
	 * Serialize object to array
	 * @return array<string, mixed>
	 */
	#[Override]
	public function toArray(): array
	{
		return [
			'name' => SerializationHelper::stringOrNull($this->name),
			'cmt' => SerializationHelper::stringOrNull($this->comment),
			'desc' => SerializationHelper::stringOrNull($this->description),
			'src' => SerializationHelper::stringOrNull($this->source),
			'link' => SerializationHelper::serialize($this->links),
			'number' => SerializationHelper::integerOrNull($this->number),
			'type' => SerializationHelper::stringOrNull($this->type),
			'extensions' => SerializationHelper::serialize($this->extensions),
			'rtep' => SerializationHelper::serialize($this->points),
			'stats' => SerializationHelper::serialize($this->stats),
		];
	}

	/**
  * Recalculate stats objects.
  */
	#[Override]
	public function recalculateStats(): void
	{
		if (!$this->stats instanceof \phpGPX\Models\Stats) {
			$this->stats = new Stats();
		}

		$this->stats->reset();

		if ($this->points === []) {
			return;
		}

		$pointCount = count($this->points);

		$firstPoint = $this->points[0];
		$lastPoint = end($this->points);

		$this->stats->startedAt = $firstPoint->time;
		$this->stats->startedAtCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];
		$this->stats->finishedAt = $lastPoint->time;
		$this->stats->finishedAtCoords = ["lat" => $lastPoint->latitude, "lng" => $lastPoint->longitude];
		$this->stats->minAltitude = $firstPoint->elevation;
		$this->stats->minAltitudeCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];

		[$this->stats->cumulativeElevationGain, $this->stats->cumulativeElevationLoss] =
			ElevationGainLossCalculator::calculate($this->getPoints());

		$calculator = new DistanceCalculator($this->getPoints());
		$this->stats->distance = $calculator->getRawDistance();
		$this->stats->realDistance = $calculator->getRealDistance();

		for ($p = 0; $p < $pointCount; $p++) {
			if ((phpGPX::$IGNORE_ELEVATION_0 === false || $this->points[$p]->elevation > 0) && $this->stats->minAltitude > $this->points[$p]->elevation) {
				$this->stats->minAltitude = $this->points[$p]->elevation;
				$this->stats->minAltitudeCoords = ["lat" => $this->points[$p]->latitude, "lng" => $this->points[$p]->longitude];
			}

			if ($this->stats->maxAltitude < $this->points[$p]->elevation) {
				$this->stats->maxAltitude = $this->points[$p]->elevation;
				$this->stats->maxAltitudeCoords = ["lat" => $this->points[$p]->latitude, "lng" => $this->points[$p]->longitude];
			}

			if ($this->stats->minAltitude > $this->points[$p]->elevation) {
				$this->stats->minAltitude = $this->points[$p]->elevation;
				$this->stats->minAltitudeCoords = ["lat" => $this->points[$p]->latitude, "lng" => $this->points[$p]->longitude];
			}
		}

		if (($firstPoint->time instanceof DateTime) && ($lastPoint->time instanceof DateTime)) {
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
	}
}
