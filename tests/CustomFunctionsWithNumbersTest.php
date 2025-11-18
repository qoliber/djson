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
 * Tests for custom functions with numbers in their names
 *
 * This test suite verifies that custom functions can have digits in their names.
 * Previously, function names like "base64", "md5", "sha256" would fail due to
 * regex pattern that only allowed letters, underscores, and pipes.
 */
class CustomFunctionsWithNumbersTest extends TestCase
{
    private DJson $djson;

    protected function setUp(): void
    {
        $this->djson = new DJson();
    }

    public function testCustomFunctionWithNumberAtEnd(): void
    {
        $this->djson->registerFunction('test1', function ($value) {
            return "TEST1:" . $value;
        });

        $template = '{
            "result": "@djson test1 {{value}}"
        }';

        $data = ['value' => 'hello'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals('TEST1:hello', $result['result']);
    }

    public function testCustomFunctionWithNumberInMiddle(): void
    {
        $this->djson->registerFunction('base64encode', function ($value) {
            return base64_encode((string)$value);
        });

        $template = '{
            "encoded": "@djson base64encode {{value}}"
        }';

        $data = ['value' => 'hello world'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(base64_encode('hello world'), $result['encoded']);
    }

    public function testCustomFunctionBase64(): void
    {
        $this->djson->registerFunction('base64', function ($value) {
            return base64_encode((string)$value);
        });

        $template = '{
            "encoded": "@djson base64 {{email}}"
        }';

        $data = ['email' => 'test@example.com'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(base64_encode('test@example.com'), $result['encoded']);
    }

    public function testCustomFunctionMd5(): void
    {
        $this->djson->registerFunction('md5hash', function ($value) {
            return md5((string)$value);
        });

        $template = '{
            "hash": "@djson md5hash {{value}}"
        }';

        $data = ['value' => 'password123'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(md5('password123'), $result['hash']);
    }

    public function testCustomFunctionSha256(): void
    {
        $this->djson->registerFunction('sha256', function ($value) {
            return hash('sha256', (string)$value);
        });

        $template = '{
            "hash": "@djson sha256 {{value}}"
        }';

        $data = ['value' => 'secure'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(hash('sha256', 'secure'), $result['hash']);
    }

    public function testMultipleFunctionsWithNumbers(): void
    {
        $this->djson->registerFunction('encode64', function ($value) {
            return base64_encode((string)$value);
        });

        $this->djson->registerFunction('hash256', function ($value) {
            return hash('sha256', (string)$value);
        });

        $this->djson->registerFunction('format2decimals', function ($value) {
            return number_format((float)$value, 2);
        });

        $template = '{
            "encoded": "@djson encode64 {{text}}",
            "hashed": "@djson hash256 {{text}}",
            "formatted": "@djson format2decimals {{price}}"
        }';

        $data = [
            'text' => 'hello',
            'price' => 99.999
        ];

        $result = $this->djson->process($template, $data);

        $this->assertEquals(base64_encode('hello'), $result['encoded']);
        $this->assertEquals(hash('sha256', 'hello'), $result['hashed']);
        $this->assertEquals('100.00', $result['formatted']);
    }

    public function testFunctionWithNumbersInLoop(): void
    {
        $this->djson->registerFunction('hash1', function ($value) {
            return md5((string)$value);
        });

        $template = '{
            "users": {
                "@djson for users as user": {
                    "name": "{{user.name}}",
                    "emailHash": "@djson hash1 {{user.email}}"
                }
            }
        }';

        $data = [
            'users' => [
                ['name' => 'John', 'email' => 'john@example.com'],
                ['name' => 'Jane', 'email' => 'jane@example.com']
            ]
        ];

        $result = $this->djson->process($template, $data);

        $this->assertCount(2, $result['users']);
        $this->assertEquals(md5('john@example.com'), $result['users'][0]['emailHash']);
        $this->assertEquals(md5('jane@example.com'), $result['users'][1]['emailHash']);
    }

    public function testFunctionWithNumbersChained(): void
    {
        $this->djson->registerFunction('reverse1', function ($value) {
            return strrev((string)$value);
        });

        $template = '{
            "result": "@djson upper|reverse1 {{value}}"
        }';

        $data = ['value' => 'hello'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals('OLLEH', $result['result']);
    }

    public function testFunctionWithNumbersAndParameters(): void
    {
        $this->djson->registerFunction('pad10', function ($value, $char = '0') {
            return str_pad((string)$value, 10, $char, STR_PAD_LEFT);
        });

        $template = '{
            "paddedDefault": "@djson pad10 {{value}}",
            "paddedCustom": "@djson pad10 {{value}} *"
        }';

        $data = ['value' => '123'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals('0000000123', $result['paddedDefault']);
        $this->assertEquals('*******123', $result['paddedCustom']);
    }

    public function testFunctionWithMultipleDigits(): void
    {
        $this->djson->registerFunction('sha512hash', function ($value) {
            return hash('sha512', (string)$value);
        });

        $template = '{
            "hash": "@djson sha512hash {{value}}"
        }';

        $data = ['value' => 'test'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(hash('sha512', 'test'), $result['hash']);
    }

    public function testFunctionNameWithMixedLettersAndNumbers(): void
    {
        $this->djson->registerFunction('abc123xyz', function ($value) {
            return strtoupper((string)$value);
        });

        $template = '{
            "result": "@djson abc123xyz {{value}}"
        }';

        $data = ['value' => 'hello'];
        $result = $this->djson->process($template, $data);

        $this->assertEquals('HELLO', $result['result']);
    }

    public function testFunctionWithNumberAtBeginning(): void
    {
        // Function names starting with numbers are valid in our system
        $this->djson->registerFunction('3times', function ($value) {
            return (int)$value * 3;
        });

        $template = '{
            "result": "@djson 3times {{value}}"
        }';

        $data = ['value' => 5];
        $result = $this->djson->process($template, $data);

        $this->assertEquals(15, $result['result']);
    }

    public function testRealWorldUseCase_Gravatar(): void
    {
        $this->djson->registerFunction('gravatar200', function ($email) {
            $hash = md5(strtolower(trim($email)));
            return "https://www.gravatar.com/avatar/$hash?s=200";
        });

        $template = '{
            "user": {
                "email": "{{user.email}}",
                "avatar": "@djson gravatar200 {{user.email}}"
            }
        }';

        $data = [
            'user' => ['email' => 'john@example.com']
        ];

        $result = $this->djson->process($template, $data);

        $expectedHash = md5('john@example.com');
        $this->assertEquals("https://www.gravatar.com/avatar/$expectedHash?s=200", $result['user']['avatar']);
    }
}
