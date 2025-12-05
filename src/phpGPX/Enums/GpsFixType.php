<?php

declare(strict_types=1);

/**
 * Created            05/12/24
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Enums;

/**
 * Enum GpsFixType
 * Represents the type of GPS fix according to GPX 1.1 specification.
 * @see http://www.topografix.com/GPX/1/1/#type_fixType
 * @package phpGPX\Enums
 */
enum GpsFixType: string
{
	case NONE = 'none';
	case TWO_D = '2d';
	case THREE_D = '3d';
	case DGPS = 'dgps';
	case PPS = 'pps';
}
