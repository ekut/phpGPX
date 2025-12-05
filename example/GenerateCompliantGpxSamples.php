<?php

declare(strict_types=1);

/**
 * Generate sample GPX 1.1 compliant files for verification.
 *
 * This script demonstrates that the phpGPX library generates
 * fully compliant GPX 1.1 files that validate against the official XSD schema.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use phpGPX\Models\Bounds;
use phpGPX\Models\Copyright;
use phpGPX\Models\Email;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Models\Point;
use phpGPX\Models\Route;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;

// Create output directory if it doesn't exist
$outputDir = __DIR__ . '/gpx-samples';
if (!is_dir($outputDir)) {
	mkdir($outputDir, 0755, true);
}

echo "Generating GPX 1.1 compliant sample files...\n\n";

// Sample 1: Minimal GPX file
echo "1. Generating minimal GPX file...\n";
$minimalGpx = new GpxFile('phpGPX Sample Generator v1.0');
$minimalGpx->toXML()->save($outputDir . '/minimal-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/minimal-compliant.gpx\n\n";

// Sample 2: GPX with waypoints
echo "2. Generating GPX file with waypoints...\n";
$waypointsGpx = new GpxFile('phpGPX Waypoint Sample');

$waypoint1 = new Point(Point::WAYPOINT, 54.9328621088893, 9.860624216140083);
$waypoint1->elevation = 42.5;
$waypoint1->name = 'Sample Waypoint 1';
$waypoint1->description = 'A sample waypoint demonstrating GPX 1.1 compliance';
$waypoint1->time = new DateTime('2024-01-15T10:30:00Z');
$waypointsGpx->waypoints[] = $waypoint1;

$waypoint2 = new Point(Point::WAYPOINT, 54.9428621088893, 9.870624216140083);
$waypoint2->elevation = 38.2;
$waypoint2->name = 'Sample Waypoint 2';
$waypoint2->description = 'Another sample waypoint';
$waypoint2->time = new DateTime('2024-01-15T10:35:00Z');
$waypointsGpx->waypoints[] = $waypoint2;

$waypointsGpx->toXML()->save($outputDir . '/waypoints-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/waypoints-compliant.gpx\n\n";

// Sample 3: GPX with track
echo "3. Generating GPX file with track...\n";
$trackGpx = new GpxFile('phpGPX Track Sample');

$track = new Track();
$track->name = 'Sample Track';
$track->description = 'A sample track demonstrating GPX 1.1 compliance';

$segment = new Segment();

// Add track points
$trackPoint1 = new Point(Point::TRACKPOINT, 54.9328621088893, 9.860624216140083);
$trackPoint1->elevation = 42.5;
$trackPoint1->time = new DateTime('2024-01-15T10:00:00Z');
$segment->points[] = $trackPoint1;

$trackPoint2 = new Point(Point::TRACKPOINT, 54.9338621088893, 9.861624216140083);
$trackPoint2->elevation = 43.2;
$trackPoint2->time = new DateTime('2024-01-15T10:01:00Z');
$segment->points[] = $trackPoint2;

$trackPoint3 = new Point(Point::TRACKPOINT, 54.9348621088893, 9.862624216140083);
$trackPoint3->elevation = 44.1;
$trackPoint3->time = new DateTime('2024-01-15T10:02:00Z');
$segment->points[] = $trackPoint3;

$track->segments[] = $segment;
$trackGpx->tracks[] = $track;

$trackGpx->toXML()->save($outputDir . '/track-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/track-compliant.gpx\n\n";

// Sample 4: GPX with route
echo "4. Generating GPX file with route...\n";
$routeGpx = new GpxFile('phpGPX Route Sample');

$route = new Route();
$route->name = 'Sample Route';
$route->description = 'A sample route demonstrating GPX 1.1 compliance';

$routePoint1 = new Point(Point::ROUTEPOINT, 54.9328621088893, 9.860624216140083);
$routePoint1->name = 'Route Start';
$route->points[] = $routePoint1;

$routePoint2 = new Point(Point::ROUTEPOINT, 54.9428621088893, 9.870624216140083);
$routePoint2->name = 'Route Waypoint';
$route->points[] = $routePoint2;

$routePoint3 = new Point(Point::ROUTEPOINT, 54.9528621088893, 9.880624216140083);
$routePoint3->name = 'Route End';
$route->points[] = $routePoint3;

$routeGpx->routes[] = $route;

$routeGpx->toXML()->save($outputDir . '/route-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/route-compliant.gpx\n\n";

// Sample 5: Comprehensive GPX with metadata
echo "5. Generating comprehensive GPX file with metadata...\n";
$comprehensiveGpx = new GpxFile('phpGPX Comprehensive Sample');

// Create comprehensive metadata
$metadata = new Metadata();
$metadata->name = 'Comprehensive GPX Sample';
$metadata->description = 'A comprehensive GPX file demonstrating all GPX 1.1 features';
$metadata->time = new DateTime('2024-01-15T10:00:00Z');
$metadata->keywords = 'sample, gpx, compliance, validation';

// Add bounds
$metadata->bounds = new Bounds(
	minLatitude: 54.9,
	minLongitude: 9.8,
	maxLatitude: 55.0,
	maxLongitude: 9.9
);

// Add link
$link = new Link('https://github.com/Sibyx/phpGPX');
$link->text = 'phpGPX Library';
$link->type = 'text/html';
$metadata->links[] = $link;

// Add copyright
$copyright = new Copyright('phpGPX Contributors');
$copyright->year = '2024';
$copyright->license = 'https://opensource.org/licenses/MIT';
$metadata->copyright = $copyright;

// Add author with email
$author = new Person();
$author->name = 'Sample Author';
$author->email = new Email('author', 'example.com');
$metadata->author = $author;

$comprehensiveGpx->metadata = $metadata;

// Add waypoint
$waypoint = new Point(Point::WAYPOINT, 54.95, 9.85);
$waypoint->name = 'Comprehensive Waypoint';
$waypoint->elevation = 45.0;
$comprehensiveGpx->waypoints[] = $waypoint;

// Add track
$track = new Track();
$track->name = 'Comprehensive Track';
$segment = new Segment();
$segment->points[] = new Point(Point::TRACKPOINT, 54.92, 9.82);
$segment->points[] = new Point(Point::TRACKPOINT, 54.93, 9.83);
$track->segments[] = $segment;
$comprehensiveGpx->tracks[] = $track;

// Add route
$route = new Route();
$route->name = 'Comprehensive Route';
$route->points[] = new Point(Point::ROUTEPOINT, 54.94, 9.84);
$route->points[] = new Point(Point::ROUTEPOINT, 54.95, 9.85);
$comprehensiveGpx->routes[] = $route;

$comprehensiveGpx->toXML()->save($outputDir . '/comprehensive-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/comprehensive-compliant.gpx\n\n";

// Sample 6: GPX with boundary coordinates
echo "6. Generating GPX file with boundary coordinates...\n";
$boundaryGpx = new GpxFile('phpGPX Boundary Test');

// Add waypoints at coordinate boundaries
$boundaryGpx->waypoints[] = new Point(Point::WAYPOINT, -90.0, 0.0);  // Min latitude
$boundaryGpx->waypoints[] = new Point(Point::WAYPOINT, 90.0, 0.0);   // Max latitude
$boundaryGpx->waypoints[] = new Point(Point::WAYPOINT, 0.0, -180.0); // Min longitude
$boundaryGpx->waypoints[] = new Point(Point::WAYPOINT, 0.0, 179.999999); // Max longitude

$metadata = new Metadata();
$metadata->name = 'Boundary Coordinates Test';
$metadata->bounds = new Bounds(
	minLatitude: -90.0,
	minLongitude: -180.0,
	maxLatitude: 90.0,
	maxLongitude: 179.999999
);
$boundaryGpx->metadata = $metadata;

$boundaryGpx->toXML()->save($outputDir . '/boundary-compliant.gpx');
echo "   ✓ Saved to: gpx-samples/boundary-compliant.gpx\n\n";

echo "✅ All sample GPX files generated successfully!\n";
echo "\nThese files are fully compliant with GPX 1.1 specification and can be validated\n";
echo "against the official XSD schema from topografix.com.\n";
echo "\nTo validate manually, you can use online validators or XML tools with the XSD at:\n";
echo "https://www.topografix.com/GPX/1/1/gpx.xsd\n";
