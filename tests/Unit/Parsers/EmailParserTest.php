<?php
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Tests\Unit\Parsers;

use phpGPX\Models\Email;
use phpGPX\Parsers\EmailParser;

class EmailParserTest extends AbstractParserTest
{
	protected $testModelClass = Email::class;
	protected $testParserClass = EmailParser::class;

	/**
	 * @var Email
	 */
	protected $testModelInstance;

	public static function createTestInstance()
	{
		$email = new Email();

		$email->id = "jakub.dubec";
		$email->domain = "gmail.com";

		return $email;
	}

	protected function setUp(): void
    {
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}


	public function testParse()
	{
		$email = EmailParser::parse($this->testXmlFile->email);

		$this->assertNotEmpty($email);

		$this->assertEquals($this->testModelInstance->id, $email->id);
		$this->assertEquals($this->testModelInstance->domain, $email->domain);

		$this->assertEquals($this->testModelInstance->toArray(), $email->toArray());
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param \DOMDocument $document
	 * @return \DOMElement
	 */
	protected function convertToXML(\DOMDocument $document)
	{
		return EmailParser::toXML($this->testModelInstance, $document);
	}

	/**
	 * Test parsing email with missing optional attributes
	 */
	public function test_parse_email_with_missing_optional_attributes(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<email id="user"/>
		');

		$email = EmailParser::parse($minimalXml);

		$this->assertNotEmpty($email);
		$this->assertEquals('user', $email->id);
		// With constructor defaults, missing domain becomes empty string
		$this->assertSame('', $email->domain);
	}
}
