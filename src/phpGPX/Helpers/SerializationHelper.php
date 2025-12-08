<?php

declare(strict_types=1);

/**
 * Created            14/02/2017 18:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Helpers;

use phpGPX\Models\Summarizable;

/**
 * Class SerializationHelper
 * Contains basic serialization helpers used in summary() methods.
 * @package phpGPX\Helpers
 */
abstract class SerializationHelper
{
	/**
	 * Returns integer or null.
	 */
	public static function integerOrNull(mixed $value): ?int
	{
		return is_numeric($value) ? (int) $value : null;
	}

	/**
	 * Returns float or null.
	 */
	public static function floatOrNull(mixed $value): ?float
	{
		return is_numeric($value) ? (float) $value : null;
	}

	/**
	 * Returns string or null
	 */
	public static function stringOrNull(mixed $value): ?string
	{
		return is_string($value) ? $value : null;
	}

	/**
	 * Recursively traverse Summarizable objects and returns their array representation according summary() method.
	 * @param Summarizable|array<Summarizable>|null $object
	 * @return array<mixed>|null
	 */
	public static function serialize(Summarizable|array|null $object): ?array
	{
		if (is_array($object)) {
			$result = [];
			foreach ($object as $record) {
				// Add type check before calling toArray()
				if ($record instanceof Summarizable) {
					$result[] = $record->toArray();
				}
			}

			return $result;
		}

		return $object instanceof \phpGPX\Models\Summarizable ? $object->toArray() : null;
	}

	/**
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function filterNotNull(array $array): array
	{
		foreach ($array as &$item) {
			if (!is_array($item)) {
				continue;
			}

			$item = self::filterNotNull($item);
		}

		return array_filter($array, fn ($item): bool => $item !== null && (!is_array($item) || count($item)));
	}
}
