<?php

declare(strict_types=1);

/**
 * Created            15/02/2017 19:00
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\SerializationHelper;
use phpGPX\Models\Extensions\TrackPointExtension;

/**
 * Class Extensions
 * TODO: http://www.garmin.com/xmlschemas/GpxExtensions/v3
 * @package phpGPX\Models
 */
class Extensions implements Summarizable
{
	/**
	 * GPX Garmin TrackPointExtension v1
	 * @see 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1'
	 */
	public ?TrackPointExtension $trackPointExtension = null;

	/**
	 * @var array
	 */
	public array $unsupported = [];

	/**
	 * Serialize object to array
	 */
	public function toArray(): array
	{
		return [
			'trackpoint' => SerializationHelper::serialize($this->trackPointExtension),
			'unsupported' => $this->unsupported,
		];
	}
}
