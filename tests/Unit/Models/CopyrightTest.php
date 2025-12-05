<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Copyright;
use phpGPX\Parsers\CopyrightParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Copyright model.
 * Tests Copyright creation, property storage, and serialization.
 */
final class CopyrightTest extends TestCase
{
	/**
	 * Test Copyright creation with author.
	 * Requirements: 2.2
	 */
	public function test_copyright_can_be_created_with_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		
		// Assert
		$this->assertInstanceOf(Copyright::class, $copyright);
		$this->assertEquals('John Doe', $copyright->author);
	}

	/**
	 * Test Copyright stores all properties correctly.
	 * Requirements: 2.2
	 */
	public function test_copyright_stores_all_properties_correctly(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		$copyright->year = '2024';
		$copyright->license = 'https://creativecommons.org/licenses/by/4.0/';
		
		// Assert
		$this->assertEquals('John Doe', $copyright->author);
		$this->assertEquals('2024', $copyright->year);
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $copyright->license);
	}

	/**
	 * Test Copyright with optional fields as null.
	 * Requirements: 2.2
	 */
	public function test_copyright_handles_optional_fields_as_null(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		
		// Assert
		$this->assertNull($copyright->year);
		$this->assertNull($copyright->license);
	}

	/**
	 * Test Copyright initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_copyright_initialization_has_null_values(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		
		// Assert - with constructor promotion, author has empty string default
		$this->assertSame('', $copyright->author);
		$this->assertNull($copyright->year);
		$this->assertNull($copyright->license);
	}

	/**
	 * Test Copyright serialization to array.
	 * Requirements: 2.7
	 */
	public function test_copyright_serializes_to_array_correctly(): void
	{
		// Arrange
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		$copyright->year = '2024';
		$copyright->license = 'https://creativecommons.org/licenses/by/4.0/';
		
		// Act
		$array = $copyright->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('John Doe', $array['author']);
		$this->assertEquals('2024', $array['year']);
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $array['license']);
	}

	/**
	 * Test Copyright serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_copyright_serializes_null_values_correctly(): void
	{
		// Arrange
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		
		// Act
		$array = $copyright->toArray();
		
		// Assert
		$this->assertNull($array['year']);
		$this->assertNull($array['license']);
	}

	/**
	 * Test Copyright serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_copyright_serializes_to_xml_correctly(): void
	{
		// Arrange
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		$copyright->year = '2024';
		$copyright->license = 'https://creativecommons.org/licenses/by/4.0/';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<copyright', $xml);
		$this->assertStringContainsString('author="John Doe"', $xml);
		$this->assertStringContainsString('<year>2024</year>', $xml);
		$this->assertStringContainsString('<license>https://creativecommons.org/licenses/by/4.0/</license>', $xml);
	}

	/**
	 * Test Copyright serialization to XML with only author.
	 * Requirements: 2.5
	 */
	public function test_copyright_serializes_to_xml_with_only_author(): void
	{
		// Arrange
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<copyright', $xml);
		$this->assertStringContainsString('author="John Doe"', $xml);
		$this->assertStringNotContainsString('<year>', $xml);
		$this->assertStringNotContainsString('<license>', $xml);
	}

	/**
	 * Test Copyright with various year formats.
	 * Requirements: 2.2
	 */
	public function test_copyright_handles_various_year_formats(): void
	{
		// Arrange & Act - Single year
		$copyright1 = new Copyright();
		$copyright1->author = 'John Doe';
		$copyright1->year = '2024';
		$this->assertEquals('2024', $copyright1->year);
		
		// Arrange & Act - Year range
		$copyright2 = new Copyright();
		$copyright2->author = 'John Doe';
		$copyright2->year = '2020-2024';
		$this->assertEquals('2020-2024', $copyright2->year);
		
		// Arrange & Act - Multiple years
		$copyright3 = new Copyright();
		$copyright3->author = 'John Doe';
		$copyright3->year = '2020, 2022, 2024';
		$this->assertEquals('2020, 2022, 2024', $copyright3->year);
	}

	/**
	 * Test Copyright with various license URLs.
	 * Requirements: 2.2
	 */
	public function test_copyright_handles_various_license_urls(): void
	{
		// Arrange & Act - Creative Commons
		$copyright1 = new Copyright();
		$copyright1->author = 'John Doe';
		$copyright1->license = 'https://creativecommons.org/licenses/by/4.0/';
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $copyright1->license);
		
		// Arrange & Act - MIT License
		$copyright2 = new Copyright();
		$copyright2->author = 'Jane Smith';
		$copyright2->license = 'https://opensource.org/licenses/MIT';
		$this->assertEquals('https://opensource.org/licenses/MIT', $copyright2->license);
		
		// Arrange & Act - GPL
		$copyright3 = new Copyright();
		$copyright3->author = 'Bob Johnson';
		$copyright3->license = 'https://www.gnu.org/licenses/gpl-3.0.html';
		$this->assertEquals('https://www.gnu.org/licenses/gpl-3.0.html', $copyright3->license);
	}

	/**
	 * Test Copyright with organization as author.
	 * Requirements: 2.2
	 */
	public function test_copyright_can_have_organization_as_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'Acme Corporation';
		$copyright->year = '2024';
		
		// Assert
		$this->assertEquals('Acme Corporation', $copyright->author);
	}

	/**
	 * Test Copyright with special characters in author.
	 * Requirements: 2.2
	 */
	public function test_copyright_handles_special_characters_in_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'José García & Associates';
		
		// Assert
		$this->assertEquals('José García & Associates', $copyright->author);
	}

	/**
	 * Test Copyright with current year.
	 * Requirements: 2.2
	 */
	public function test_copyright_with_current_year(): void
	{
		// Arrange
		$currentYear = date('Y');
		
		// Act
		$copyright = new Copyright();
		$copyright->author = 'John Doe';
		$copyright->year = $currentYear;
		
		// Assert
		$this->assertEquals($currentYear, $copyright->year);
	}

	/**
	 * Test Copyright with historical year.
	 * Requirements: 2.2
	 */
	public function test_copyright_with_historical_year(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'Historical Society';
		$copyright->year = '1995';
		
		// Assert
		$this->assertEquals('1995', $copyright->year);
	}

	/**
	 * Test Copyright with public domain license.
	 * Requirements: 2.2
	 */
	public function test_copyright_with_public_domain_license(): void
	{
		// Arrange & Act
		$copyright = new Copyright();
		$copyright->author = 'Public Domain Contributor';
		$copyright->license = 'https://creativecommons.org/publicdomain/zero/1.0/';
		
		// Assert
		$this->assertEquals('https://creativecommons.org/publicdomain/zero/1.0/', $copyright->license);
	}

	/**
	 * Test Copyright serialization preserves all data.
	 * Requirements: 2.5, 2.7
	 */
	public function test_copyright_serialization_preserves_all_data(): void
	{
		// Arrange
		$copyright = new Copyright();
		$copyright->author = 'Test Author';
		$copyright->year = '2024';
		$copyright->license = 'https://example.com/license';
		
		// Act - Serialize to array
		$array = $copyright->toArray();
		
		// Assert - All data preserved in array
		$this->assertEquals('Test Author', $array['author']);
		$this->assertEquals('2024', $array['year']);
		$this->assertEquals('https://example.com/license', $array['license']);
		
		// Act - Serialize to XML
		$document = new \DOMDocument('1.0', 'UTF-8');
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert - All data preserved in XML
		$this->assertStringContainsString('author="Test Author"', $xml);
		$this->assertStringContainsString('<year>2024</year>', $xml);
		$this->assertStringContainsString('<license>https://example.com/license</license>', $xml);
	}
}
