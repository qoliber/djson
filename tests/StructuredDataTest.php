<?php

/**
 * DJson - Dynamic JSON Templating Library
 *
 * @package   Qoliber\DJson
 * @author    Jakub Winkler <jwinkler@qoliber.com>
 * @copyright 2024 Qoliber
 * @license   MIT
 * @link      https://github.com/qoliber/djson
 */

declare(strict_types=1);

namespace Qoliber\DJson\Tests;

use PHPUnit\Framework\TestCase;
use Qoliber\DJson\DJson;

/**
 * Tests for JSON-LD and structured data formats
 *
 * Real-world use cases like Schema.org markup, OpenGraph, etc.
 */
class StructuredDataTest extends TestCase
{
    private DJson $djson;

    protected function setUp(): void
    {
        $this->djson = new DJson();
    }

    // ========================================================================
    // JSON-LD BREADCRUMB TESTS (Using JSON String Templates)
    // ========================================================================

    public function testJsonLdBreadcrumbsAsJsonString(): void
    {
        // Real-world scenario: JSON-LD in script tag
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "BreadcrumbList",
            "itemListElement": {
                "@djson for breadcrumbs as crumb": {
                    "@type": "ListItem",
                    "position": "{{crumb.position}}",
                    "item": {
                        "@id": "{{crumb.url}}",
                        "name": "{{crumb.name}}"
                    }
                }
            }
        }';

        $data = [
            'breadcrumbs' => [
                ['position' => 1, 'url' => 'https://example.com/', 'name' => 'Home'],
                ['position' => 2, 'url' => 'https://example.com/category', 'name' => 'Category'],
                ['position' => 3, 'url' => 'https://example.com/category/product', 'name' => 'Product']
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('https://schema.org/', $result['@context']);
        $this->assertEquals('BreadcrumbList', $result['@type']);
        $this->assertCount(3, $result['itemListElement']);

        $this->assertEquals('ListItem', $result['itemListElement'][0]['@type']);
        $this->assertEquals(1, $result['itemListElement'][0]['position']);
        $this->assertEquals('https://example.com/', $result['itemListElement'][0]['item']['@id']);
        $this->assertEquals('Home', $result['itemListElement'][0]['item']['name']);
    }

    public function testBreadcrumbsAsCompactJsonFromJsonString(): void
    {
        // Real-world: JSON string template (like in <script> tag)
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "BreadcrumbList",
            "itemListElement": {
                "@djson for breadcrumbs as crumb": {
                    "@type": "ListItem",
                    "position": "{{crumb.position}}",
                    "item": {
                        "@id": "{{crumb.url}}",
                        "name": "{{crumb.name}}"
                    }
                }
            }
        }';

        $data = [
            'breadcrumbs' => [
                ['position' => 1, 'url' => 'https://sportano.pl/', 'name' => 'Sportano'],
                ['position' => 2, 'url' => 'https://sportano.pl/mezczyzna', 'name' => 'Mężczyzna']
            ]
        ];

        // Process JSON string template to JSON string output
        $json = $this->djson->processToJson($jsonTemplate, $data);

        // Should be compact (single line)
        $this->assertStringNotContainsString("\n", $json);

        // Should contain the structure
        $this->assertStringContainsString('"@context":"https:\/\/schema.org\/"', $json);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $json);
        $this->assertStringContainsString('"itemListElement":[', $json);

        // Decode and verify
        $decoded = json_decode($json, true);
        $this->assertCount(2, $decoded['itemListElement']);
    }

    public function testSportanoBreadcrumbsExactExample(): void
    {
        // EXACT example from user's Sportano website
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "BreadcrumbList",
            "itemListElement": {
                "@djson for breadcrumbs as crumb": {
                    "@type": "ListItem",
                    "position": "{{crumb.position}}",
                    "item": {
                        "@id": "{{crumb.url}}",
                        "name": "{{crumb.name}}"
                    }
                }
            }
        }';

        $data = [
            'breadcrumbs' => [
                ['position' => 1, 'url' => 'https://sportano.pl/', 'name' => 'Sportano'],
                ['position' => 2, 'url' => 'https://sportano.pl/mezczyzna', 'name' => 'Mężczyzna'],
                ['position' => 3, 'url' => 'https://sportano.pl/mezczyzna/odziez-meska', 'name' => 'Odzież męska'],
                ['position' => 4, 'url' => 'https://sportano.pl/koszulki-t-shirty-i-topy-meskie', 'name' => 'Koszulki, T-shirty i topy męskie']
            ]
        ];

        $json = $this->djson->processToJson($jsonTemplate, $data);

        // Verify it's valid JSON
        $this->assertJson($json);

        // Verify compact format
        $this->assertStringNotContainsString("\n", $json);

        // Verify structure matches expected
        $this->assertStringContainsString('"@context":"https:\/\/schema.org\/"', $json);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $json);

        // Decode and verify all 4 breadcrumbs
        $decoded = json_decode($json, true);
        $this->assertCount(4, $decoded['itemListElement']);
        $this->assertEquals('Sportano', $decoded['itemListElement'][0]['item']['name']);
        $this->assertEquals('Mężczyzna', $decoded['itemListElement'][1]['item']['name']);
        $this->assertEquals('Odzież męska', $decoded['itemListElement'][2]['item']['name']);
        $this->assertEquals('Koszulki, T-shirty i topy męskie', $decoded['itemListElement'][3]['item']['name']);
    }

    // ========================================================================
    // JSON-LD PRODUCT SCHEMA (Using JSON String Templates)
    // ========================================================================

    public function testJsonLdProductAsJsonString(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "{{product.name}}",
            "description": "{{product.description}}",
            "offers": {
                "@type": "Offer",
                "price": "{{product.price}}",
                "priceCurrency": "{{product.currency}}"
            }
        }';

        $data = [
            'product' => [
                'name' => 'Laptop Pro',
                'description' => 'High-performance laptop',
                'price' => 1299.99,
                'currency' => 'USD'
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('Product', $result['@type']);
        $this->assertEquals('Laptop Pro', $result['name']);
        $this->assertEquals('Offer', $result['offers']['@type']);
        $this->assertEquals(1299.99, $result['offers']['price']);
    }

    public function testJsonLdProductWithMultipleOffers(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "{{product.name}}",
            "offers": {
                "@djson for product.offers as offer": {
                    "@type": "Offer",
                    "price": "{{offer.price}}",
                    "priceCurrency": "{{offer.currency}}",
                    "availability": "{{offer.availability}}"
                }
            }
        }';

        $data = [
            'product' => [
                'name' => 'Smartphone',
                'offers' => [
                    ['price' => 699, 'currency' => 'USD', 'availability' => 'InStock'],
                    ['price' => 649, 'currency' => 'EUR', 'availability' => 'InStock']
                ]
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertCount(2, $result['offers']);
        $this->assertEquals('Offer', $result['offers'][0]['@type']);
        $this->assertEquals(699, $result['offers'][0]['price']);
    }

    // ========================================================================
    // JSON-LD ORGANIZATION
    // ========================================================================

    public function testJsonLdOrganization(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "{{org.name}}",
            "url": "{{org.url}}",
            "logo": "{{org.logo}}",
            "contactPoint": {
                "@djson for org.contacts as contact": {
                    "@type": "ContactPoint",
                    "telephone": "{{contact.phone}}",
                    "contactType": "{{contact.type}}"
                }
            }
        }';

        $data = [
            'org' => [
                'name' => 'Example Corp',
                'url' => 'https://example.com',
                'logo' => 'https://example.com/logo.png',
                'contacts' => [
                    ['phone' => '+1-555-0100', 'type' => 'customer service'],
                    ['phone' => '+1-555-0200', 'type' => 'sales']
                ]
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('Organization', $result['@type']);
        $this->assertCount(2, $result['contactPoint']);
        $this->assertEquals('ContactPoint', $result['contactPoint'][0]['@type']);
    }

    // ========================================================================
    // ARRAY OF OBJECTS WITH SPECIAL CHARACTERS
    // ========================================================================

    public function testArrayOfObjectsWithAtSymbol(): void
    {
        $jsonTemplate = '{
            "items": {
                "@djson for items as item": {
                    "@id": "{{item.id}}",
                    "@type": "{{item.type}}",
                    "value": "{{item.value}}"
                }
            }
        }';

        $data = [
            'items' => [
                ['id' => '1', 'type' => 'TypeA', 'value' => 'First'],
                ['id' => '2', 'type' => 'TypeB', 'value' => 'Second']
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertCount(2, $result['items']);
        $this->assertEquals('1', $result['items'][0]['@id']);
        $this->assertEquals('TypeA', $result['items'][0]['@type']);
    }

    public function testNestedArraysOfObjects(): void
    {
        $jsonTemplate = '{
            "categories": {
                "@djson for categories as category": {
                    "name": "{{category.name}}",
                    "products": {
                        "@djson for category.products as product": {
                            "@type": "Product",
                            "name": "{{product.name}}",
                            "price": "{{product.price}}"
                        }
                    }
                }
            }
        }';

        $data = [
            'categories' => [
                [
                    'name' => 'Electronics',
                    'products' => [
                        ['name' => 'Laptop', 'price' => 999],
                        ['name' => 'Mouse', 'price' => 29]
                    ]
                ],
                [
                    'name' => 'Books',
                    'products' => [
                        ['name' => 'PHP Guide', 'price' => 39]
                    ]
                ]
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertCount(2, $result['categories']);
        $this->assertCount(2, $result['categories'][0]['products']);
        $this->assertEquals('Product', $result['categories'][0]['products'][0]['@type']);
        $this->assertEquals('Laptop', $result['categories'][0]['products'][0]['name']);
    }

    // ========================================================================
    // JSON-LD ARTICLE
    // ========================================================================

    public function testJsonLdArticle(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "{{article.title}}",
            "author": {
                "@type": "Person",
                "name": "{{article.author}}"
            },
            "datePublished": "{{article.date}}",
            "articleBody": "{{article.content}}"
        }';

        $data = [
            'article' => [
                'title' => 'How to Use DJson',
                'author' => 'John Doe',
                'date' => '2024-01-15',
                'content' => 'DJson is a powerful templating library...'
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('Article', $result['@type']);
        $this->assertEquals('How to Use DJson', $result['headline']);
        $this->assertEquals('Person', $result['author']['@type']);
        $this->assertEquals('John Doe', $result['author']['name']);
    }

    // ========================================================================
    // REAL-WORLD: E-COMMERCE PRODUCT LIST
    // ========================================================================

    public function testEcommerceProductList(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org",
            "@type": "ItemList",
            "numberOfItems": "{{productCount}}",
            "itemListElement": {
                "@djson for products as product": {
                    "@type": "Product",
                    "position": "{{product.position}}",
                    "name": "{{product.name}}",
                    "offers": {
                        "@type": "Offer",
                        "price": "{{product.price}}",
                        "priceCurrency": "USD",
                        "availability": "https://schema.org/InStock"
                    }
                }
            }
        }';

        $data = [
            'productCount' => 3,
            'products' => [
                ['position' => 1, 'name' => 'Product A', 'price' => 99.99],
                ['position' => 2, 'name' => 'Product B', 'price' => 149.99],
                ['position' => 3, 'name' => 'Product C', 'price' => 199.99]
            ]
        ];

        $json = $this->djson->processToJson($jsonTemplate, $data);
        $decoded = json_decode($json, true);

        $this->assertEquals('ItemList', $decoded['@type']);
        $this->assertEquals(3, $decoded['numberOfItems']);
        $this->assertCount(3, $decoded['itemListElement']);
        $this->assertEquals('Product', $decoded['itemListElement'][0]['@type']);
        $this->assertEquals(99.99, $decoded['itemListElement'][0]['offers']['price']);
    }

    // ========================================================================
    // UNICODE AND SPECIAL CHARACTERS
    // ========================================================================

    public function testJsonLdWithUnicodeCharacters(): void
    {
        $jsonTemplate = '{
            "@context": "https://schema.org/",
            "@type": "BreadcrumbList",
            "itemListElement": {
                "@djson for breadcrumbs as crumb": {
                    "@type": "ListItem",
                    "position": "{{crumb.position}}",
                    "item": {
                        "@id": "{{crumb.url}}",
                        "name": "{{crumb.name}}"
                    }
                }
            }
        }';

        $data = [
            'breadcrumbs' => [
                ['position' => 1, 'url' => 'https://sportano.pl/', 'name' => 'Sportano'],
                ['position' => 2, 'url' => 'https://sportano.pl/mezczyzna', 'name' => 'Mężczyzna'],
                ['position' => 3, 'url' => 'https://sportano.pl/mezczyzna/odziez-meska', 'name' => 'Odzież męska']
            ]
        ];

        $json = $this->djson->processToJson($jsonTemplate, $data);

        // Should contain escaped Unicode
        $this->assertStringContainsString('M\u0119\u017cczyzna', $json);
        $this->assertStringContainsString('Odzie\u017c m\u0119ska', $json);

        // Decode and verify
        $decoded = json_decode($json, true);
        $this->assertEquals('Mężczyzna', $decoded['itemListElement'][1]['item']['name']);
        $this->assertEquals('Odzież męska', $decoded['itemListElement'][2]['item']['name']);
    }
}
