<?php

declare(strict_types=1);

/**
 * Created            05/12/24
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Enums;

/**
 * Enum FileFormat
 * Represents the output format for GPX file serialization.
 * @package phpGPX\Enums
 */
enum FileFormat: string
{
	case JSON = 'json';
	case XML = 'xml';
}
