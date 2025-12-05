<?php

declare(strict_types=1);

/**
 * Created            05/12/24
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Enums;

/**
 * Enum PointType
 * Represents the type of a GPS point (waypoint, track point, or route point).
 * @package phpGPX\Enums
 */
enum PointType: string
{
	case WAYPOINT = 'waypoint';
	case TRACKPOINT = 'track';
	case ROUTEPOINT = 'route';
}
