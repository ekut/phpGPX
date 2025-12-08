<?php

declare(strict_types=1);

/**
 * DistanceCalculator.php
 *
 * @author Jens Hassler
 * @author Jakub Dubec
 * @since  07/2018
 * @version 2.0
 */

namespace phpGPX\Helpers;

use Closure;
use phpGPX\Models\Point;
use phpGPX\phpGPX;

final class DistanceCalculator
{
	/**
	 * DistanceCalculator constructor.
	 * @param Point[] $points
	 */
	public function __construct(private array $points)
	{
	}

	public function getRawDistance(): float
	{
		/** @var Closure(Point, Point): float $strategy */
		$strategy = GeoHelper::getRawDistance(...);

		return $this->calculate($strategy);
	}

	public function getRealDistance(): float
	{
		/** @var Closure(Point, Point): float $strategy */
		$strategy = GeoHelper::getRealDistance(...);

		return $this->calculate($strategy);
	}

	/**
	 * Calculate distance using the provided strategy.
	 * @param callable(Point, Point): float $strategy
	 */
	private function calculate(callable $strategy): float
	{
		$distance = 0;

		$pointCount = count($this->points);

		/** @var Point|null $lastConsideredPoint */
		$lastConsideredPoint = null;

		for ($p = 0; $p < $pointCount; $p++) {
			$curPoint = $this->points[$p];

			// skip the first point
			if ($p === 0) {
				$lastConsideredPoint = $curPoint;
				continue;
			}

			// calculate the delta from current point to last considered point
			assert($lastConsideredPoint !== null);
			$curPoint->difference = call_user_func($strategy, $lastConsideredPoint, $curPoint);

			// if smoothing is applied we only consider points with a delta above the threshold (e.g. 2 meters)
			if (phpGPX::$APPLY_DISTANCE_SMOOTHING) {
				$differenceFromLastConsideredPoint = call_user_func($strategy, $curPoint, $lastConsideredPoint);

				if ($differenceFromLastConsideredPoint > phpGPX::$DISTANCE_SMOOTHING_THRESHOLD) {
					// Explicit cast to handle strict binary operands mode
					$distance += (float) $differenceFromLastConsideredPoint;
					$lastConsideredPoint = $curPoint;
				}
			}

			// if smoothing is not applied we consider every point
			else {
				// Explicit cast to handle strict binary operands mode
				$distance += (float) $curPoint->difference;
				$lastConsideredPoint = $curPoint;
			}

			$curPoint->distance = $distance;
		}

		return $distance;
	}
}
