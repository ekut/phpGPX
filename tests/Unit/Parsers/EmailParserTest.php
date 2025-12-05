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
		return new Email("jakub.dubec", "gmail.com");
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
	 * Test parsing email with missing domain attribute throws exception
	 * Requirements: 5.2
	 */
	public function test_parse_email_with_missing_domain_throws_exception(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<email id="user"/>
		');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email domain attribute');

		EmailParser::parse($minimalXml);
	}

	/**
	 * Test parsing email with missing id attribute throws exception
	 * Requirements: 5.2
	 */
	public function test_parse_email_with_missing_id_throws_exception(): void
	{
		$minimalXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
			<email domain="example.com"/>
		');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email id attribute');

		EmailParser::parse($minimalXml);
	}
}
