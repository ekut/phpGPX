<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 18:21
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DateTime;
use phpGPX\Helpers\BoundsCalculator;
use phpGPX\Helpers\SerializationHelper;
use phpGPX\phpGPX;

/**
 * Class Track
 * Represents a track - an ordered list of points describing a path.
 * @see https://www.topografix.com/GPX/1/1/#type_trkType
 * @package phpGPX\Models
 */
class Track extends Collection
{
	/**
	 * Array of Track segments
	 * @var Segment[]
	 */
	public $segments = [];

	/**
	 * Return all points in collection.
	 * @return Point[]
	 */
	public function getPoints(): array
	{
		/** @var Point[] $points */
		$points = [];

		foreach ($this->segments as $segment) {
			$points = array_merge($points, $segment->points);
		}

		if (phpGPX::$SORT_BY_TIMESTAMP && !empty($points) && $points[0]->time !== null) {
			usort($points, ['phpGPX\Helpers\DateTimeHelper', 'comparePointsByTimestamp']);
		}

		return $points;
	}

	/**
	 * Serialize object to array
	 * @return array<string, mixed>
	 */
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
			'trkseg' => SerializationHelper::serialize($this->segments),
			'stats' => SerializationHelper::serialize($this->stats),
		];
	}

	/**
  * Recalculate stats objects.
  */
	public function recalculateStats(): void
	{
		if (empty($this->stats)) {
			$this->stats = new Stats();
		}

		$this->stats->reset();

		if (empty($this->segments)) {
			return;
		}

		$segmentsCount = count($this->segments);

		$firstSegment = null;
		$firstPoint = null;

		// Identify first Segment/Point
		for ($s = 0; $s < $segmentsCount; $s++) {
			$pointCount = count($this->segments[$s]->points);
			for ($p = 0; $p < $pointCount; $p++) {
				if (is_null($firstPoint)) {
					$firstPoint = &$this->segments[$s]->points[$p];
					$firstSegment = &$this->segments[$s];
					break;
				}
			}
		}

		if (empty($firstPoint)) {
			return;
		}

		$lastSegment = end($this->segments);
		if ($lastSegment === false) {
			return;
		}
		
		$lastPoint = end($lastSegment->points);
		if ($lastPoint === false) {
			return;
		}

		$this->stats->startedAt = $firstPoint->time;
		$this->stats->startedAtCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];
		$this->stats->finishedAt = $lastPoint->time;
		$this->stats->finishedAtCoords = ["lat" => $lastPoint->latitude, "lng" => $lastPoint->longitude];
		$this->stats->minAltitude = $firstPoint->elevation;
		$this->stats->minAltitudeCoords = ["lat" => $firstPoint->latitude, "lng" => $firstPoint->longitude];
		$this->stats->cumulativeElevationGain = 0.0;
		$this->stats->cumulativeElevationLoss = 0.0;

		for ($s = 0; $s < $segmentsCount; $s++) {
			$this->segments[$s]->recalculateStats();

			// Add null check for segment stats
			$segmentStats = $this->segments[$s]->stats;
			if ($segmentStats === null) {
				continue;
			}

			$this->stats->cumulativeElevationGain += $segmentStats->cumulativeElevationGain ?? 0.0;
			$this->stats->cumulativeElevationLoss += $segmentStats->cumulativeElevationLoss ?? 0.0;

			$this->stats->distance += $segmentStats->distance;
			$this->stats->realDistance += $segmentStats->realDistance;

			if ($this->stats->minAltitude === null) {
				$this->stats->minAltitude = $segmentStats->minAltitude;
				$this->stats->minAltitudeCoords = $segmentStats->minAltitudeCoords;
			}
			if ($segmentStats->maxAltitude !== null && ($this->stats->maxAltitude === null || $this->stats->maxAltitude < $segmentStats->maxAltitude)) {
				$this->stats->maxAltitude = $segmentStats->maxAltitude;
				$this->stats->maxAltitudeCoords = $segmentStats->maxAltitudeCoords;
			}
			if ($segmentStats->minAltitude !== null && ($this->stats->minAltitude === null || $this->stats->minAltitude > $segmentStats->minAltitude)) {
				$this->stats->minAltitude = $segmentStats->minAltitude;
				$this->stats->minAltitudeCoords = $segmentStats->minAltitudeCoords;
			}
		}

		if (($firstPoint->time instanceof DateTime) && ($lastPoint->time instanceof DateTime)) {
			$this->stats->duration = abs($lastPoint->time->getTimestamp() - $firstPoint->time->getTimestamp());

			if ($this->stats->duration !== 0 && $this->stats->distance > 0) {
				$this->stats->averageSpeed = $this->stats->distance / $this->stats->duration;
			}

			if ($this->stats->distance > 0 && $this->stats->duration !== 0) {
				$distanceInKm = $this->stats->distance / 1000;
				if ($distanceInKm > 0) {
					$this->stats->averagePace = $this->stats->duration / $distanceInKm;
				}
			}
		}

		[$northWest, $southEast] = BoundsCalculator::calculate($this->getPoints());
		$this->stats->bounds = [$northWest, $southEast];
	}
}
