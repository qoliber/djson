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

class RenderModeTest extends TestCase
{
    public function testDefaultRenderModeIsCompact(): void
    {
        $djson = new DJson();

        $this->assertEquals(DJson::RENDER_MODE_COMPACT, $djson->getRenderMode());
    }

    public function testConstructorWithDebugMode(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);

        $this->assertEquals(DJson::RENDER_MODE_DEBUG, $djson->getRenderMode());
    }

    public function testConstructorWithCompactMode(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_COMPACT);

        $this->assertEquals(DJson::RENDER_MODE_COMPACT, $djson->getRenderMode());
    }

    public function testSetRenderModeToDebug(): void
    {
        $djson = new DJson();
        $result = $djson->setRenderMode(DJson::RENDER_MODE_DEBUG);

        $this->assertSame($djson, $result); // Test fluent interface
        $this->assertEquals(DJson::RENDER_MODE_DEBUG, $djson->getRenderMode());
    }

    public function testSetRenderModeToCompact(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);
        $result = $djson->setRenderMode(DJson::RENDER_MODE_COMPACT);

        $this->assertSame($djson, $result); // Test fluent interface
        $this->assertEquals(DJson::RENDER_MODE_COMPACT, $djson->getRenderMode());
    }

    public function testSetRenderModeWithInvalidModeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid render mode 'invalid'");

        $djson = new DJson();
        $djson->setRenderMode('invalid');
    }

    public function testConstructorWithInvalidModeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid render mode 'bad'");

        new DJson('bad');
    }

    public function testCompactModeOutputsSingleLineJson(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_COMPACT);

        $template = [
            'user' => [
                'name' => '{{name}}',
                'age' => '{{age}}'
            ]
        ];

        $data = ['name' => 'John', 'age' => 30];
        $json = $djson->processToJson($template, $data);

        // Compact JSON should be single line (no newlines)
        $this->assertStringNotContainsString("\n", $json);
        $this->assertJson($json);

        $expected = '{"user":{"name":"John","age":30}}';
        $this->assertEquals($expected, $json);
    }

    public function testDebugModeOutputsPrettyJson(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);

        $template = [
            'user' => [
                'name' => '{{name}}',
                'age' => '{{age}}'
            ]
        ];

        $data = ['name' => 'John', 'age' => 30];
        $json = $djson->processToJson($template, $data);

        // Pretty JSON should have newlines and indentation
        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString("    ", $json); // Check for indentation
        $this->assertJson($json);

        // Verify structure
        $decoded = json_decode($json, true);
        $this->assertEquals('John', $decoded['user']['name']);
        $this->assertEquals(30, $decoded['user']['age']);
    }

    public function testProcessToJsonWithExplicitFlagsOverridesRenderMode(): void
    {
        // Create with DEBUG mode
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);

        $template = ['test' => '{{value}}'];
        $data = ['value' => 'hello'];

        // But use explicit compact flags
        $json = $djson->processToJson($template, $data, JSON_THROW_ON_ERROR);

        // Should be compact (single line) because explicit flags override render mode
        $this->assertStringNotContainsString("\n", $json);
        $this->assertEquals('{"test":"hello"}', $json);
    }

    public function testProcessToJsonWithExplicitPrettyFlagsInCompactMode(): void
    {
        // Create with COMPACT mode
        $djson = new DJson(DJson::RENDER_MODE_COMPACT);

        $template = ['test' => '{{value}}'];
        $data = ['value' => 'hello'];

        // But use explicit pretty print flags
        $json = $djson->processToJson($template, $data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        // Should be pretty (multiline) because explicit flags override render mode
        $this->assertStringContainsString("\n", $json);
    }

    public function testProcessFileToJsonUsesCompactMode(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_COMPACT);

        // Create a temporary template file
        $templatePath = sys_get_temp_dir() . '/djson_test_compact_' . uniqid() . '.json';
        file_put_contents($templatePath, json_encode(['message' => '{{msg}}']));

        try {
            $json = $djson->processFileToJson($templatePath, ['msg' => 'Hello World']);

            $this->assertStringNotContainsString("\n", $json);
            $this->assertEquals('{"message":"Hello World"}', $json);
        } finally {
            @unlink($templatePath);
        }
    }

    public function testProcessFileToJsonUsesDebugMode(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);

        // Create a temporary template file
        $templatePath = sys_get_temp_dir() . '/djson_test_debug_' . uniqid() . '.json';
        file_put_contents($templatePath, json_encode(['message' => '{{msg}}']));

        try {
            $json = $djson->processFileToJson($templatePath, ['msg' => 'Hello World']);

            $this->assertStringContainsString("\n", $json);
            $this->assertStringContainsString("    ", $json); // Check for indentation

            $decoded = json_decode($json, true);
            $this->assertEquals('Hello World', $decoded['message']);
        } finally {
            @unlink($templatePath);
        }
    }

    public function testFluentInterfaceChaining(): void
    {
        $djson = new DJson();

        $template = ['value' => '{{data}}'];
        $data = ['data' => 'test'];

        // Test fluent chaining
        $json = $djson->setRenderMode(DJson::RENDER_MODE_DEBUG)->processToJson($template, $data);

        $this->assertStringContainsString("\n", $json);
        $this->assertEquals(DJson::RENDER_MODE_DEBUG, $djson->getRenderMode());
    }

    public function testSwitchingModesBetweenCalls(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_COMPACT);

        $template = ['test' => '{{value}}'];
        $data = ['value' => 'hello'];

        // First call - compact
        $compactJson = $djson->processToJson($template, $data);
        $this->assertStringNotContainsString("\n", $compactJson);

        // Switch to debug
        $djson->setRenderMode(DJson::RENDER_MODE_DEBUG);
        $prettyJson = $djson->processToJson($template, $data);
        $this->assertStringContainsString("\n", $prettyJson);

        // Switch back to compact
        $djson->setRenderMode(DJson::RENDER_MODE_COMPACT);
        $compactJson2 = $djson->processToJson($template, $data);
        $this->assertStringNotContainsString("\n", $compactJson2);

        // All should have same data, just different formatting
        $this->assertEquals(
            json_decode($compactJson, true),
            json_decode($prettyJson, true)
        );
        $this->assertEquals($compactJson, $compactJson2);
    }

    public function testComplexTemplateWithDebugMode(): void
    {
        $djson = new DJson(DJson::RENDER_MODE_DEBUG);

        $template = [
            'users' => [
                '@djson for users as user' => [
                    'name' => '{{user.name}}',
                    'email' => '{{user.email}}',
                    '@djson if user.active' => [
                        'status' => 'active'
                    ]
                ]
            ]
        ];

        $data = [
            'users' => [
                ['name' => 'John', 'email' => 'john@example.com', 'active' => true],
                ['name' => 'Jane', 'email' => 'jane@example.com', 'active' => false]
            ]
        ];

        $json = $djson->processToJson($template, $data);

        // Should be pretty printed
        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString("    ", $json);

        // Should have correct data
        $decoded = json_decode($json, true);
        $this->assertCount(2, $decoded['users']);
        $this->assertEquals('active', $decoded['users'][0]['status']);
        $this->assertArrayNotHasKey('status', $decoded['users'][1]);
    }
}
