<?php

declare(strict_types=1);

/**
 * Created            12/09/16 11:14
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

/**
 * Interface Summarizable
 * @package phpGPX\Models
 */
interface Summarizable
{
	/**
	 * Serialize object to array
	 * @return array<string, mixed>
	 */
	public function toArray(): array;
}
