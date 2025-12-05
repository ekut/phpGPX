<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use DOMDocument;
use Eris\Generators;
use Eris\TestTrait;
use InvalidArgumentException;
use phpGPX\Models\Link;
use phpGPX\Parsers\LinkParser;
use phpGPX\Tests\Support\TestCase;
use TypeError;

/**
 * Unit tests for Link model.
 * Tests Link creation, property storage, and serialization.
 */
final class LinkTest extends TestCase
{
	use TestTrait;

	/**
	 * Test Link creation with required href.
	 * Requirements: 3.1
	 */
	public function test_link_can_be_created_with_href(): void
	{
		// Arrange & Act
		$link = new Link('https://example.com');

		// Assert
		$this->assertInstanceOf(Link::class, $link);
		$this->assertEquals('https://example.com', $link->href);
	}

	/**
	 * Test Link construction without href throws TypeError.
	 * Requirements: 3.1
	 */
	public function test_link_construction_without_href_throws_type_error(): void
	{
		// Assert
		$this->expectException(TypeError::class);

		// Act
		new Link();
	}

	/**
	 * Test Link construction with empty href throws InvalidArgumentException.
	 * Requirements: 3.2, 9.4
	 */
	public function test_link_construction_with_empty_href_throws_exception(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Link href attribute');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new Link('');
	}

	/**
	 * Test Link construction with whitespace-only href throws InvalidArgumentException.
	 * Requirements: 3.2, 9.4
	 */
	public function test_link_construction_with_whitespace_only_href_throws_exception(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Link href attribute');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new Link('   ');
	}

	/**
	 * Test Link error message contains GPX 1.1 schema reference.
	 * Requirements: 9.4
	 */
	public function test_link_error_message_contains_schema_reference(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('GPX 1.1 schema');

		// Act
		new Link('');
	}

	/**
	 * Test Link creation with all properties.
	 * Requirements: 3.1, 3.2
	 */
	public function test_link_stores_all_properties_correctly(): void
	{
		// Arrange & Act
		$link = new Link(
			'https://example.com/photo.jpg',
			'Example Photo',
			'image/jpeg',
		);

		// Assert
		$this->assertEquals('https://example.com/photo.jpg', $link->href);
		$this->assertEquals('Example Photo', $link->text);
		$this->assertEquals('image/jpeg', $link->type);
	}

	/**
	 * Test Link with optional fields as null.
	 * Requirements: 3.1, 3.2
	 */
	public function test_link_handles_optional_fields_as_null(): void
	{
		// Arrange & Act
		$link = new Link('https://example.com');

		// Assert
		$this->assertNull($link->text);
		$this->assertNull($link->type);
	}

	/**
	 * Test Link serialization to array.
	 * Requirements: 3.3
	 */
	public function test_link_serializes_to_array_correctly(): void
	{
		// Arrange
		$link = new Link(
			'https://example.com',
			'Example Link',
			'text/html',
		);

		// Act
		$array = $link->toArray();

		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('https://example.com', $array['href']);
		$this->assertEquals('Example Link', $array['text']);
		$this->assertEquals('text/html', $array['type']);
	}

	/**
	 * Test Link serialization to array with null values.
	 * Requirements: 3.3
	 */
	public function test_link_serializes_null_values_correctly(): void
	{
		// Arrange
		$link = new Link('https://example.com');

		// Act
		$array = $link->toArray();

		// Assert
		$this->assertNull($array['text']);
		$this->assertNull($array['type']);
	}

	/**
	 * Test Link serialization to XML.
	 * Requirements: 3.3
	 */
	public function test_link_serializes_to_xml_correctly(): void
	{
		// Arrange
		$link = new Link(
			'https://example.com/photo.jpg',
			'Example Photo',
			'image/jpeg',
		);

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = LinkParser::toXML($link, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<link', $xml);
		$this->assertStringContainsString('href="https://example.com/photo.jpg"', $xml);
		$this->assertStringContainsString('<text>Example Photo</text>', $xml);
		$this->assertStringContainsString('<type>image/jpeg</type>', $xml);
	}

	/**
	 * Test Link serialization to XML with only href.
	 * Requirements: 3.3
	 */
	public function test_link_serializes_to_xml_with_only_href(): void
	{
		// Arrange
		$link = new Link('https://example.com');

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = LinkParser::toXML($link, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<link', $xml);
		$this->assertStringContainsString('href="https://example.com"', $xml);
		$this->assertStringNotContainsString('<text>', $xml);
		$this->assertStringNotContainsString('<type>', $xml);
	}

	/**
	 * Test Link with various MIME types.
	 * Requirements: 3.1, 3.2
	 */
	public function test_link_handles_various_mime_types(): void
	{
		// Arrange & Act - Image
		$imageLink = new Link(
			'https://example.com/photo.jpg',
			null,
			'image/jpeg',
		);

		// Assert
		$this->assertEquals('image/jpeg', $imageLink->type);

		// Arrange & Act - Video
		$videoLink = new Link(
			'https://example.com/video.mp4',
			null,
			'video/mp4',
		);

		// Assert
		$this->assertEquals('video/mp4', $videoLink->type);

		// Arrange & Act - HTML
		$htmlLink = new Link(
			'https://example.com/page.html',
			null,
			'text/html',
		);

		// Assert
		$this->assertEquals('text/html', $htmlLink->type);
	}

	/**
	 * Test Link with different URL schemes.
	 * Requirements: 3.1, 3.2
	 */
	public function test_link_handles_different_url_schemes(): void
	{
		// Arrange & Act - HTTPS
		$httpsLink = new Link('https://example.com');
		$this->assertEquals('https://example.com', $httpsLink->href);

		// Arrange & Act - HTTP
		$httpLink = new Link('http://example.com');
		$this->assertEquals('http://example.com', $httpLink->href);

		// Arrange & Act - FTP
		$ftpLink = new Link('ftp://example.com/file.gpx');
		$this->assertEquals('ftp://example.com/file.gpx', $ftpLink->href);
	}

	/**
	 * Test Link with special characters in text.
	 * Requirements: 3.1, 3.2
	 */
	public function test_link_handles_special_characters_in_text(): void
	{
		// Arrange & Act
		$link = new Link(
			'https://example.com',
			'Photo & Video <Collection>',
		);

		// Assert
		$this->assertEquals('Photo & Video <Collection>', $link->text);
	}

	/**
	 * Property test: Link requires href.
	 *
	 * **Feature: gpx-schema-compliance, Property 9: Link requires href**
	 * **Validates: Requirements 3.1**
	 */
	public function test_property_link_requires_href(): void
	{
		// Test that TypeError is thrown when href parameter is missing
		// This is enforced by PHP's type system, so we test it directly

		try {
			// @phpstan-ignore-next-line - Intentionally calling with wrong number of arguments
			new Link();
			$this->fail('Expected TypeError when creating Link without href');
		} catch (TypeError $e) {
			// Expected - href is required
			$this->assertStringContainsString('Link::__construct()', $e->getMessage());
		}
	}

	/**
	 * Property test: Link href validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 10: Link href validation**
	 * **Validates: Requirements 3.2**
	 */
	public function test_property_link_href_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements(['', '   ', "\t", "\n", "  \t\n  "]),
			)
			->withMaxSize(100)
			->then(function ($emptyHref): void {
				// Test that empty or whitespace-only href throws InvalidArgumentException
				try {
					new Link($emptyHref);
					$this->fail('Expected InvalidArgumentException for empty/whitespace href: ' . json_encode($emptyHref));
				} catch (InvalidArgumentException $e) {
					// Verify error message contains required information
					$this->assertStringContainsString('Link href attribute', $e->getMessage());
					$this->assertStringContainsString('required', $e->getMessage());
					$this->assertStringContainsString('cannot be empty', $e->getMessage());
					$this->assertStringContainsString('GPX 1.1 schema', $e->getMessage());
				}
			});
	}

	/**
	 * Property test: Valid href values are accepted.
	 *
	 * **Feature: gpx-schema-compliance, Property 10: Link href validation**
	 * **Validates: Requirements 3.2**
	 */
	public function test_property_valid_href_values_accepted(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					'https://example.com',
					'http://example.com/path',
					'ftp://ftp.example.com/file.gpx',
					'https://example.com/photo.jpg',
					'http://example.com/page.html?param=value',
					'https://example.com/path/to/resource#anchor',
					'file:///path/to/local/file.gpx',
					'a',  // Single character is valid
					'https://example.com/path with spaces',  // Spaces are technically valid in URLs
				]),
			)
			->withMaxSize(100)
			->then(function ($validHref): void {
				// Test that valid href values are accepted
				$link = new Link($validHref);
				$this->assertEquals($validHref, $link->href);
			});
	}
}
