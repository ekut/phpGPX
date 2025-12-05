<?php
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Tests\Unit\Parsers;

use phpGPX\Models\Copyright;
use phpGPX\Parsers\CopyrightParser;

class CopyrightParserTest extends AbstractParserTest
{
	protected $testModelClass = Copyright::class;
	protected $testParserClass = CopyrightParser::class;

	/**
	 * @var Copyright
	 */
	protected $testModelInstance;

	public static function createTestInstance()
	{
		$copyright = new Copyright(
			"Jakub Dubec",
			'2017',
			"https://github.com/Sibyx/phpGPX/blob/master/LICENSE"
		);

		return $copyright;
	}

	protected function setUp(): void
    {
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse()
	{
		$copyright = CopyrightParser::parse($this->testXmlFile->copyright);

		$this->assertNotEmpty($copyright);

		$this->assertEquals($this->testModelInstance->author, $copyright->author);
		$this->assertEquals($this->testModelInstance->license, $copyright->license);
		$this->assertEquals($this->testModelInstance->year, $copyright->year);

		$this->assertEquals($this->testModelInstance->toArray(), $copyright->toArray());
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @param \DOMDocument $document
	 * @return \DOMElement
	 */
	protected function convertToXML(\DOMDocument $document)
	{
		return CopyrightParser::toXML($this->testModelInstance, $document);
	}

	/**
	 * Test parsing copyright with missing optional attributes
	 */
	public function test_parse_copyright_with_missing_optional_attributes(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<copyright author="John Doe"/>
		');

		$copyright = CopyrightParser::parse($minimalXml);

		$this->assertNotEmpty($copyright);
		$this->assertEquals('John Doe', $copyright->author);
		$this->assertNull($copyright->year);
		$this->assertNull($copyright->license);
	}
}
