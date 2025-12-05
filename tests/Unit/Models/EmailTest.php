<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Models\Email;
use phpGPX\Parsers\EmailParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Email model.
 * Tests Email creation, property storage, and serialization.
 */
final class EmailTest extends TestCase
{
	use TestTrait;
	/**
	 * Test Email creation with id and domain.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_can_be_created_with_id_and_domain(): void
	{
		// Arrange & Act
		$email = new Email('john.doe', 'example.com');
		
		// Assert
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals('john.doe', $email->id);
		$this->assertEquals('example.com', $email->domain);
	}

	/**
	 * Test Email stores both parts correctly.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_stores_both_parts_correctly(): void
	{
		// Arrange & Act
		$email = new Email('test.user', 'mail.example.org');
		
		// Assert
		$this->assertEquals('test.user', $email->id);
		$this->assertEquals('mail.example.org', $email->domain);
	}

	/**
	 * Test Email construction without parameters throws TypeError.
	 * Requirements: 5.1
	 */
	public function test_email_construction_without_parameters_throws_type_error(): void
	{
		// Assert
		$this->expectException(\TypeError::class);
		
		// Act
		new Email();
	}

	/**
	 * Test Email construction with empty id throws InvalidArgumentException.
	 * Requirements: 5.2, 9.4
	 */
	public function test_email_construction_with_empty_id_throws_exception(): void
	{
		// Assert
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email id attribute');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');
		
		// Act
		new Email('', 'example.com');
	}

	/**
	 * Test Email construction with empty domain throws InvalidArgumentException.
	 * Requirements: 5.2, 9.4
	 */
	public function test_email_construction_with_empty_domain_throws_exception(): void
	{
		// Assert
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email domain attribute');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');
		
		// Act
		new Email('john.doe', '');
	}

	/**
	 * Test Email construction with whitespace-only id throws InvalidArgumentException.
	 * Requirements: 5.2, 9.4
	 */
	public function test_email_construction_with_whitespace_only_id_throws_exception(): void
	{
		// Assert
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email id attribute');
		
		// Act
		new Email('   ', 'example.com');
	}

	/**
	 * Test Email construction with whitespace-only domain throws InvalidArgumentException.
	 * Requirements: 5.2, 9.4
	 */
	public function test_email_construction_with_whitespace_only_domain_throws_exception(): void
	{
		// Assert
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Email domain attribute');
		
		// Act
		new Email('john.doe', '   ');
	}

	/**
	 * Test Email serialization to array.
	 * Requirements: 5.3
	 */
	public function test_email_serializes_to_array_correctly(): void
	{
		// Arrange
		$email = new Email('john.doe', 'example.com');
		
		// Act
		$array = $email->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('john.doe', $array['id']);
		$this->assertEquals('example.com', $array['domain']);
	}



	/**
	 * Test Email serialization to XML.
	 * Requirements: 5.3
	 */
	public function test_email_serializes_to_xml_correctly(): void
	{
		// Arrange
		$email = new Email('john.doe', 'example.com');
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = EmailParser::toXML($email, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<email', $xml);
		$this->assertStringContainsString('id="john.doe"', $xml);
		$this->assertStringContainsString('domain="example.com"', $xml);
	}



	/**
	 * Test Email with various id formats.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_handles_various_id_formats(): void
	{
		// Arrange & Act - Simple id
		$email1 = new Email('user', 'example.com');
		$this->assertEquals('user', $email1->id);
		
		// Arrange & Act - Id with dot
		$email2 = new Email('first.last', 'example.com');
		$this->assertEquals('first.last', $email2->id);
		
		// Arrange & Act - Id with plus
		$email3 = new Email('user+tag', 'example.com');
		$this->assertEquals('user+tag', $email3->id);
		
		// Arrange & Act - Id with underscore
		$email4 = new Email('user_name', 'example.com');
		$this->assertEquals('user_name', $email4->id);
	}

	/**
	 * Test Email with various domain formats.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_handles_various_domain_formats(): void
	{
		// Arrange & Act - Simple domain
		$email1 = new Email('user', 'example.com');
		$this->assertEquals('example.com', $email1->domain);
		
		// Arrange & Act - Subdomain
		$email2 = new Email('user', 'mail.example.com');
		$this->assertEquals('mail.example.com', $email2->domain);
		
		// Arrange & Act - Country code TLD
		$email3 = new Email('user', 'example.co.uk');
		$this->assertEquals('example.co.uk', $email3->domain);
		
		// Arrange & Act - New TLD
		$email4 = new Email('user', 'example.tech');
		$this->assertEquals('example.tech', $email4->domain);
	}

	/**
	 * Test Email represents complete email address.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_parts_represent_complete_address(): void
	{
		// Arrange
		$email = new Email('contact', 'company.com');
		
		// Act - Reconstruct email address
		$fullEmail = $email->id . '@' . $email->domain;
		
		// Assert
		$this->assertEquals('contact@company.com', $fullEmail);
	}

	/**
	 * Test Email with special characters.
	 * Requirements: 5.1, 5.2
	 */
	public function test_email_handles_special_characters(): void
	{
		// Arrange & Act
		$email = new Email('user.name+tag', 'sub-domain.example.com');
		
		// Assert
		$this->assertEquals('user.name+tag', $email->id);
		$this->assertEquals('sub-domain.example.com', $email->domain);
	}

	/**
	 * Property test: Email requires id and domain.
	 * 
	 * **Feature: gpx-schema-compliance, Property 15: Email requires id and domain**
	 * **Validates: Requirements 5.1**
	 */
	public function test_property_email_requires_id_and_domain(): void
	{
		// Test that TypeError is thrown when parameters are missing
		$typeErrorThrown = false;
		
		try {
			new Email();
		} catch (\TypeError $e) {
			$typeErrorThrown = true;
		}
		
		$this->assertTrue($typeErrorThrown, 'Email construction without parameters should throw TypeError');
	}

	/**
	 * Property test: Email validation.
	 * 
	 * **Feature: gpx-schema-compliance, Property 16: Email validation**
	 * **Validates: Requirements 5.2**
	 */
	public function test_property_email_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements(['', '   ', "\t", "\n", "  \t\n  "]), // whitespace-only strings
				Generators::string() // valid domain
			)
			->withMaxSize(100)
			->then(function ($emptyId, $domain) {
				// Filter to ensure domain is non-empty
				if (trim($domain) === '') {
					$domain = 'example.com';
				}
				
				// Test empty/whitespace-only id throws exception
				try {
					new Email($emptyId, $domain);
					$this->fail('Expected InvalidArgumentException for empty/whitespace id');
				} catch (\InvalidArgumentException $e) {
					$this->assertStringContainsString('Email id attribute', $e->getMessage());
					$this->assertStringContainsString('required', $e->getMessage());
					$this->assertStringContainsString('cannot be empty', $e->getMessage());
				}
			});
		
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::string(), // valid id
				Generators::elements(['', '   ', "\t", "\n", "  \t\n  "]) // whitespace-only strings
			)
			->withMaxSize(100)
			->then(function ($id, $emptyDomain) {
				// Filter to ensure id is non-empty
				if (trim($id) === '') {
					$id = 'user';
				}
				
				// Test empty/whitespace-only domain throws exception
				try {
					new Email($id, $emptyDomain);
					$this->fail('Expected InvalidArgumentException for empty/whitespace domain');
				} catch (\InvalidArgumentException $e) {
					$this->assertStringContainsString('Email domain attribute', $e->getMessage());
					$this->assertStringContainsString('required', $e->getMessage());
					$this->assertStringContainsString('cannot be empty', $e->getMessage());
				}
			});
		
		// Test that valid non-empty strings are accepted
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::string(),
				Generators::string()
			)
			->withMaxSize(100)
			->then(function ($id, $domain) {
				// Filter to ensure both are non-empty
				if (trim($id) === '') {
					$id = 'user';
				}
				if (trim($domain) === '') {
					$domain = 'example.com';
				}
				
				// Should not throw exception
				$email = new Email($id, $domain);
				$this->assertEquals($id, $email->id);
				$this->assertEquals($domain, $email->domain);
			});
	}
}
