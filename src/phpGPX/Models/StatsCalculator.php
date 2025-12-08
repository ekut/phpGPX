<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 18:36
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

interface StatsCalculator
{
	/**
	 * Recalculate stats objects.
	 */
	public function recalculateStats(): void;

	/**
	 * Return all points in collection.
	 * @return Point[]
	 */
	public function getPoints();
}
