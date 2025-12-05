<?php

declare(strict_types=1);
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Tests\Unit\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Link;
use phpGPX\Parsers\LinkParser;

class LinkParserTest extends ParserTestBase
{
	protected $testModelClass = Link::class;

	protected $testParserClass = LinkParser::class;

	/**
	 * @var Link
	 */
	protected $testModelInstance;

	/**
	 * @return Link
	 */
	public static function createTestInstance()
	{
		$link = new Link("https://jakubdubec.me");
		$link->text = "Portfolio";
		$link->type = "text/html";

		return $link;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$links = LinkParser::parse($this->testXmlFile->link);

		$this->assertNotEmpty($links);

		$link = $links[0];

		$this->assertEquals($this->testModelInstance->href, $link->href);
		$this->assertEquals($this->testModelInstance->text, $link->text);
		$this->assertEquals($this->testModelInstance->type, $link->type);

		$this->assertEquals($this->testModelInstance->toArray(), $link->toArray());
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return LinkParser::toXML($this->testModelInstance, $document);
	}

	/**
	 * Test parsing link with missing optional attributes
	 */
	public function test_parse_link_with_missing_optional_attributes(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<document>
				<link href="https://example.com"/>
			</document>
		');

		$links = LinkParser::parse($minimalXml->link);

		$this->assertNotEmpty($links);
		$this->assertEquals('https://example.com', $links[0]->href);
		$this->assertNull($links[0]->text);
		$this->assertNull($links[0]->type);
	}
}
