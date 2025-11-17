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

class JsonStringAndScalarArrayTest extends TestCase
{
    private DJson $djson;

    protected function setUp(): void
    {
        $this->djson = new DJson();
    }

    // ========================================================================
    // JSON STRING INPUT TESTS
    // ========================================================================

    public function testProcessWithJsonStringInput(): void
    {
        $jsonTemplate = '{
            "name": "{{name}}",
            "age": "{{age}}"
        }';

        $data = ['name' => 'John', 'age' => 30];
        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('John', $result['name']);
        $this->assertEquals(30, $result['age']);
    }

    public function testProcessJsonStringWithNestedObjects(): void
    {
        $jsonTemplate = '{
            "user": {
                "name": "{{user.name}}",
                "email": "{{user.email}}"
            }
        }';

        $data = ['user' => ['name' => 'Jane', 'email' => 'jane@example.com']];
        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals('Jane', $result['user']['name']);
        $this->assertEquals('jane@example.com', $result['user']['email']);
    }

    public function testProcessJsonStringWithDirectives(): void
    {
        $jsonTemplate = '{
            "products": {
                "@djson for products as product": {
                    "name": "{{product.name}}",
                    "price": "{{product.price}}"
                }
            }
        }';

        $data = [
            'products' => [
                ['name' => 'Laptop', 'price' => 999],
                ['name' => 'Mouse', 'price' => 29]
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertCount(2, $result['products']);
        $this->assertEquals('Laptop', $result['products'][0]['name']);
        $this->assertEquals(999, $result['products'][0]['price']);
    }

    // ========================================================================
    // SCALAR ARRAY TESTS
    // ========================================================================

    public function testScalarArrayInTemplate(): void
    {
        $template = [
            'name' => 'John',
            'age' => 30,
            'cars' => ['Ford', 'BMW', 'Fiat']
        ];

        $result = $this->djson->process($template, []);

        $this->assertEquals('John', $result['name']);
        $this->assertEquals(30, $result['age']);
        $this->assertIsArray($result['cars']);
        $this->assertEquals(['Ford', 'BMW', 'Fiat'], $result['cars']);
    }

    public function testScalarArrayFromContext(): void
    {
        $template = [
            'name' => '{{name}}',
            'cars' => '{{cars}}'
        ];

        $data = [
            'name' => 'John',
            'cars' => ['Ford', 'BMW', 'Fiat']
        ];

        $result = $this->djson->process($template, $data);

        $this->assertEquals('John', $result['name']);
        $this->assertEquals(['Ford', 'BMW', 'Fiat'], $result['cars']);
    }

    public function testScalarArrayOfNumbers(): void
    {
        $template = [
            'scores' => '{{scores}}'
        ];

        $data = [
            'scores' => [95, 87, 92, 78, 100]
        ];

        $result = $this->djson->process($template, $data);

        $this->assertEquals([95, 87, 92, 78, 100], $result['scores']);
    }

    public function testScalarArrayOfBooleans(): void
    {
        $template = [
            'flags' => '{{flags}}'
        ];

        $data = [
            'flags' => [true, false, true, true, false]
        ];

        $result = $this->djson->process($template, $data);

        $this->assertEquals([true, false, true, true, false], $result['flags']);
    }

    public function testMixedScalarArray(): void
    {
        $template = [
            'mixed' => '{{items}}'
        ];

        $data = [
            'items' => ['string', 42, true, null, 3.14]
        ];

        $result = $this->djson->process($template, $data);

        $this->assertEquals(['string', 42, true, null, 3.14], $result['mixed']);
    }

    // ========================================================================
    // LOOP OVER SCALAR ARRAYS
    // ========================================================================

    public function testLoopOverScalarArray(): void
    {
        $template = [
            'cars' => [
                '@djson for cars as car' => [
                    'brand' => '{{car}}'
                ]
            ]
        ];

        $data = ['cars' => ['Ford', 'BMW', 'Fiat']];
        $result = $this->djson->process($template, $data);

        $this->assertCount(3, $result['cars']);
        $this->assertEquals('Ford', $result['cars'][0]['brand']);
        $this->assertEquals('BMW', $result['cars'][1]['brand']);
        $this->assertEquals('Fiat', $result['cars'][2]['brand']);
    }

    public function testLoopOverScalarArrayWithIndex(): void
    {
        $template = [
            'items' => [
                '@djson for items as item' => [
                    'index' => '{{_index}}',
                    'value' => '{{item}}'
                ]
            ]
        ];

        $data = ['items' => ['Apple', 'Banana', 'Cherry']];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(0, $result['items'][0]['index']);
        $this->assertEquals('Apple', $result['items'][0]['value']);
        $this->assertEquals(1, $result['items'][1]['index']);
        $this->assertEquals('Banana', $result['items'][1]['value']);
    }

    public function testLoopOverScalarNumberArray(): void
    {
        $template = [
            'numbers' => [
                '@djson for numbers as num' => [
                    'value' => '{{num}}',
                    'doubled' => '{{num}}'
                ]
            ]
        ];

        $data = ['numbers' => [1, 2, 3, 4, 5]];
        $result = $this->djson->process($template, $data);

        $this->assertCount(5, $result['numbers']);
        $this->assertEquals(3, $result['numbers'][2]['value']);
    }

    // ========================================================================
    // PROCESS_TO_JSON TESTS
    // ========================================================================

    public function testProcessToJsonWithScalarArrays(): void
    {
        $template = [
            'name' => '{{name}}',
            'cars' => '{{cars}}'
        ];

        $data = [
            'name' => 'John',
            'cars' => ['Ford', 'BMW', 'Fiat']
        ];

        $json = $this->djson->processToJson($template, $data);

        $this->assertJson($json);
        $this->assertStringContainsString('"cars":["Ford","BMW","Fiat"]', $json);

        $decoded = json_decode($json, true);
        $this->assertEquals(['Ford', 'BMW', 'Fiat'], $decoded['cars']);
    }

    public function testProcessToJsonWithNestedScalarArrays(): void
    {
        $template = [
            'person' => [
                'name' => '{{name}}',
                'hobbies' => '{{hobbies}}'
            ]
        ];

        $data = [
            'name' => 'Alice',
            'hobbies' => ['Reading', 'Coding', 'Gaming']
        ];

        $json = $this->djson->processToJson($template, $data);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(['Reading', 'Coding', 'Gaming'], $decoded['person']['hobbies']);
    }

    public function testProcessToJsonWithLoopedScalarArray(): void
    {
        $template = [
            'brands' => [
                '@djson for cars as car' => '{{car}}'
            ]
        ];

        $data = ['cars' => ['Ford', 'BMW', 'Fiat']];
        $json = $this->djson->processToJson($template, $data);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(['Ford', 'BMW', 'Fiat'], $decoded['brands']);
    }

    // ========================================================================
    // COMBINED TESTS
    // ========================================================================

    public function testComplexJsonWithScalarAndObjectArrays(): void
    {
        $jsonTemplate = '{
            "name": "{{name}}",
            "age": "{{age}}",
            "cars": "{{cars}}",
            "friends": {
                "@djson for friends as friend": {
                    "name": "{{friend.name}}",
                    "age": "{{friend.age}}"
                }
            }
        }';

        $data = [
            'name' => 'John',
            'age' => 30,
            'cars' => ['Ford', 'BMW', 'Fiat'],
            'friends' => [
                ['name' => 'Alice', 'age' => 28],
                ['name' => 'Bob', 'age' => 32]
            ]
        ];

        $result = $this->djson->process($jsonTemplate, $data);

        $this->assertEquals(['Ford', 'BMW', 'Fiat'], $result['cars']);
        $this->assertCount(2, $result['friends']);
        $this->assertEquals('Alice', $result['friends'][0]['name']);
    }

    public function testJsonStringInputAndJsonOutput(): void
    {
        $jsonTemplate = '{
            "user": {
                "name": "{{name}}",
                "cars": "{{cars}}"
            }
        }';

        $data = [
            'name' => 'John',
            'cars' => ['Ford', 'BMW']
        ];

        // Process JSON string input
        $result = $this->djson->process($jsonTemplate, $data);

        // Output as JSON string
        $jsonOutput = $this->djson->processToJson($result, []);

        $this->assertJson($jsonOutput);
        $decoded = json_decode($jsonOutput, true);
        $this->assertEquals(['Ford', 'BMW'], $decoded['user']['cars']);
    }

    public function testEmptyScalarArray(): void
    {
        $template = [
            'items' => '{{items}}'
        ];

        $data = ['items' => []];
        $result = $this->djson->process($template, $data);

        $this->assertEquals([], $result['items']);
    }

    public function testScalarArrayWithNullValues(): void
    {
        $template = [
            'values' => '{{values}}'
        ];

        $data = ['values' => ['a', null, 'b', null, 'c']];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(['a', null, 'b', null, 'c'], $result['values']);
    }
}
