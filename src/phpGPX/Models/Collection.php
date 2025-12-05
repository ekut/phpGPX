<?php

declare(strict_types=1);

/**
 * Created            26/08/16 14:21
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

/**
 * Class Collection
 * @package phpGPX\Models
 */
abstract class Collection implements Summarizable, StatsCalculator
{
	/**
	 * GPS name of route / track.
	 * An original GPX 1.1 attribute.
	 */
	public ?string $name = null;

	/**
	 * GPS comment for route.
	 * An original GPX 1.1 attribute.
	 */
	public ?string $comment = null;

	/**
	 * Text description of route/track for user. Not sent to GPS.
	 * An original GPX 1.1 attribute.
	 */
	public ?string $description = null;

	/**
	 * Source of data. Included to give user some idea of reliability and accuracy of data.
	 * An original GPX 1.1 attribute.
	 */
	public ?string $source = null;

	/**
	 * Links to external information about the route/track.
	 * An original GPX 1.1 attribute.
	 * @var Link[]
	 */
	public array $links = [];

	/**
	 * GPS route/track number.
	 * An original GPX 1.1 attribute.
	 */
	public ?int $number = null;

	/**
	 * Type (classification) of route/track.
	 * An original GPX 1.1 attribute.
	 */
	public ?string $type = null;

	/**
	 * You can add extend GPX by adding your own elements from another schema here.
	 * An original GPX 1.1 attribute.
	 */
	public ?Extensions $extensions = null;

	/**
	 * Objects contains calculated statistics for collection.
	 */
	public ?Stats $stats = null;

	/**
	 * Return all points in collection.
	 * @return Point[]
	 */
	abstract public function getPoints(): array;
}
