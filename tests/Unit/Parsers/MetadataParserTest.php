<?php

declare(strict_types=1);
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Tests\Unit\Parsers;

use DateTime;
use DOMDocument;
use DOMElement;
use phpGPX\Models\Bounds;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Parsers\MetadataParser;

class MetadataParserTest extends ParserTestBase
{
	protected $testModelClass = Metadata::class;

	protected $testParserClass = MetadataParser::class;

	/**
	 * @var Metadata
	 */
	protected $testModelInstance;

	public static function createTestInstance()
	{
		$metadata = new Metadata();
		$metadata->name = "Test Track";
		$metadata->description = "A test track for unit testing";
		$metadata->author = PersonParserTest::createTestInstance();
		$metadata->copyright = CopyrightParserTest::createTestInstance();

		$link = new Link("https://example.com");
		$link->text = "Example Link";
		$link->type = "text/html";
		$metadata->links[] = $link;

		$metadata->time = new DateTime('2017-02-16T22:00:00Z');
		$metadata->keywords = "test, gpx, track";

		$metadata->bounds = new Bounds(54.0, 9.0, 55.0, 10.0);

		return $metadata;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$metadata = MetadataParser::parse($this->testXmlFile->metadata);

		$this->assertNotEmpty($metadata);

		// Primitive attributes
		$this->assertEquals($this->testModelInstance->name, $metadata->name);
		$this->assertEquals($this->testModelInstance->description, $metadata->description);
		$this->assertEquals($this->testModelInstance->keywords, $metadata->keywords);

		// Author
		$this->assertNotNull($metadata->author);
		$this->assertEquals($this->testModelInstance->author->name, $metadata->author->name);
		$this->assertEquals($this->testModelInstance->author->email->id, $metadata->author->email->id);
		$this->assertEquals($this->testModelInstance->author->email->domain, $metadata->author->email->domain);

		// Copyright
		$this->assertNotNull($metadata->copyright);
		$this->assertEquals($this->testModelInstance->copyright->author, $metadata->copyright->author);
		$this->assertEquals($this->testModelInstance->copyright->year, $metadata->copyright->year);
		$this->assertEquals($this->testModelInstance->copyright->license, $metadata->copyright->license);

		// Links
		$this->assertNotEmpty($metadata->links);
		$this->assertEquals($this->testModelInstance->links[0]->href, $metadata->links[0]->href);
		$this->assertEquals($this->testModelInstance->links[0]->text, $metadata->links[0]->text);
		$this->assertEquals($this->testModelInstance->links[0]->type, $metadata->links[0]->type);

		// Time
		$this->assertNotNull($metadata->time);
		$this->assertEquals($this->testModelInstance->time->format('Y-m-d\TH:i:s'), $metadata->time->format('Y-m-d\TH:i:s'));

		// Bounds
		$this->assertNotNull($metadata->bounds);
		$this->assertEquals($this->testModelInstance->bounds->minLatitude, $metadata->bounds->minLatitude);
		$this->assertEquals($this->testModelInstance->bounds->maxLatitude, $metadata->bounds->maxLatitude);
		$this->assertEquals($this->testModelInstance->bounds->minLongitude, $metadata->bounds->minLongitude);
		$this->assertEquals($this->testModelInstance->bounds->maxLongitude, $metadata->bounds->maxLongitude);

		// toArray function
		$this->assertEquals($this->testModelInstance->toArray(), $metadata->toArray());
	}

	/**
	 * Test parsing metadata with missing optional fields
	 */
	public function test_parse_metadata_with_missing_optional_fields(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<metadata>
				<name>Minimal Metadata</name>
			</metadata>
		');

		$metadata = MetadataParser::parse($minimalXml);

		$this->assertNotEmpty($metadata);
		$this->assertEquals('Minimal Metadata', $metadata->name);
		$this->assertNull($metadata->description);
		$this->assertNull($metadata->author);
		$this->assertNull($metadata->copyright);
		$this->assertNull($metadata->links);
		$this->assertNull($metadata->time);
		$this->assertNull($metadata->keywords);
		$this->assertNull($metadata->bounds);
		$this->assertNull($metadata->extensions);
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return MetadataParser::toXML($this->testModelInstance, $document);
	}
}
