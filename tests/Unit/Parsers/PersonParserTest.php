<?php

declare(strict_types=1);
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Tests\Unit\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Parsers\PersonParser;

class PersonParserTest extends ParserTestBase
{
	protected $testModelClass = Person::class;

	protected $testParserClass = PersonParser::class;

	/**
	 * @var Person
	 */
	protected $testModelInstance;

	public static function createTestInstance()
	{
		$person = new Person();
		$person->name = "Jakub Dubec";
		$person->email = EmailParserTest::createTestInstance();
		$person->links[] = LinkParserTest::createTestInstance();
		$person->name = 'Jakub Dubec';

		return $person;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$person = PersonParser::parse($this->testXmlFile->author);

		$this->assertNotEmpty($person);

		// Primitive attributes
		$this->assertEquals($this->testModelInstance->name, $person->name);

		// Email
		$this->assertEquals($this->testModelInstance->email->id, $person->email->id);
		$this->assertEquals($this->testModelInstance->email->domain, $person->email->domain);

		// Link
		$this->assertEquals($this->testModelInstance->links[0]->type, $person->links[0]->type);
		$this->assertEquals($this->testModelInstance->links[0]->text, $person->links[0]->text);
		$this->assertEquals($this->testModelInstance->links[0]->href, $person->links[0]->href);

		// toArray functions
		$this->assertEquals($this->testModelInstance->toArray(), $person->toArray());
		$this->assertEquals($this->testModelInstance->email->toArray(), $person->email->toArray());
		$this->assertEquals($this->testModelInstance->links[0]->toArray(), $person->links[0]->toArray());
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return PersonParser::toXML($this->testModelInstance, $document);
	}

	/**
	 * @url https://github.com/Sibyx/phpGPX/issues/48
	 */
	public function testEmptyLinks(): void
	{
		$gpx_file = new GpxFile('Test Creator');

		$gpx_file->metadata = new Metadata();
		$gpx_file->metadata->author = new Person();
		$gpx_file->metadata->author->name = "Arthur Dent";

		$this->assertNotNull($gpx_file->toXML()->saveXML());
	}

	/**
	 * Test parsing person with missing optional attributes
	 */
	public function test_parse_person_with_missing_optional_attributes(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<author>
				<name>John Doe</name>
			</author>
		');

		$person = PersonParser::parse($minimalXml);

		$this->assertNotEmpty($person);
		$this->assertEquals('John Doe', $person->name);
		$this->assertNull($person->email);
		// With constructor defaults, missing links becomes empty array
		$this->assertSame([], $person->links);
	}
}
