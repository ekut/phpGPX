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
 * You can add extend GPX by adding your own elements from another schema here.
 * @see https://www.topografix.com/GPX/1/1/#type_extensionsType
 * @package phpGPX\Models
 * @todo http://www.garmin.com/xmlschemas/GpxExtensions/v3
 */
class Extensions implements Summarizable
{
	/**
	 * GPX Garmin TrackPointExtension v1
	 * @see 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1'
	 */
	public ?TrackPointExtension $trackPointExtension = null;

	/**
	 * @var array<string, mixed>
	 */
	public array $unsupported = [];

	/**
	 * Serialize object to array
	 * @return array{trackpoint: array<int|string, mixed>|null, unsupported: array<string, mixed>}
	 */
	public function toArray(): array
	{
		return [
			'trackpoint' => SerializationHelper::serialize($this->trackPointExtension),
			'unsupported' => $this->unsupported,
		];
	}
}
