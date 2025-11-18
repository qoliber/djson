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
 * Security tests for DJson
 *
 * Verifies that dangerous function names are blocked to prevent code execution,
 * system command injection, and filesystem access vulnerabilities.
 */
class SecurityTest extends TestCase
{
    private DJson $djson;

    protected function setUp(): void
    {
        $this->djson = new DJson();
    }

    // ========================================================================
    // Code Execution Functions - Should be blocked
    // ========================================================================

    public function testBlocksEvalFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot register function 'eval'");
        $this->expectExceptionMessage("dangerous pattern 'eval'");

        $this->djson->registerFunction('eval', fn($code) => eval($code));
    }

    public function testBlocksAssertFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'assert'");

        $this->djson->registerFunction('assert', fn($code) => assert($code));
    }

    public function testBlocksCallUserFunc(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'call_user_func'");

        $this->djson->registerFunction('call_user_func', fn($fn) => call_user_func($fn));
    }

    // ========================================================================
    // System Command Functions - Should be blocked
    // ========================================================================

    public function testBlocksExecFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('exec', fn($cmd) => exec($cmd));
    }

    public function testBlocksShellExecFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Will match 'exec' first since patterns are checked in order
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('shell_exec', fn($cmd) => shell_exec($cmd));
    }

    public function testBlocksSystemFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'system'");

        $this->djson->registerFunction('system', fn($cmd) => system($cmd));
    }

    public function testBlocksPassthruFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'passthru'");

        $this->djson->registerFunction('passthru', fn($cmd) => passthru($cmd));
    }

    public function testBlocksPopenFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'popen'");

        $this->djson->registerFunction('popen', fn($cmd, $mode) => popen($cmd, $mode));
    }

    public function testBlocksProcOpenFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'proc_open'");

        $this->djson->registerFunction('proc_open', fn($cmd) => proc_open($cmd, [], $pipes));
    }

    // ========================================================================
    // Filesystem Functions - Should be blocked
    // ========================================================================

    public function testBlocksFileGetContents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'file_get_contents'");

        $this->djson->registerFunction('file_get_contents', fn($path) => file_get_contents($path));
    }

    public function testBlocksFilePutContents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'file_put_contents'");

        $this->djson->registerFunction('file_put_contents', fn($path, $data) => file_put_contents($path, $data));
    }

    public function testBlocksUnlinkFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'unlink'");

        $this->djson->registerFunction('unlink', fn($path) => unlink($path));
    }

    public function testBlocksChmodFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'chmod'");

        $this->djson->registerFunction('chmod', fn($path, $mode) => chmod($path, $mode));
    }

    public function testBlocksRenameFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'rename'");

        $this->djson->registerFunction('rename', fn($old, $new) => rename($old, $new));
    }

    // ========================================================================
    // Include Functions - Should be blocked
    // ========================================================================

    public function testBlocksIncludeFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'include'");

        $this->djson->registerFunction('include', fn($path) => include($path));
    }

    public function testBlocksRequireFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'require'");

        $this->djson->registerFunction('require', fn($path) => require($path));
    }

    // ========================================================================
    // Serialization Functions - Should be blocked
    // ========================================================================

    public function testBlocksUnserializeFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'unserialize'");

        $this->djson->registerFunction('unserialize', fn($data) => unserialize($data));
    }

    // ========================================================================
    // Pattern Detection in Function Names
    // ========================================================================

    public function testBlocksDangerousPatternInMiddleOfName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('myExecFunction', fn($cmd) => shell_exec($cmd));
    }

    public function testBlocksDangerousPatternAtEnd(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'eval'");

        $this->djson->registerFunction('customEval', fn($code) => eval($code));
    }

    public function testBlocksCaseInsensitiveDangerousPattern(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('EXEC', fn($cmd) => shell_exec($cmd));
    }

    // ========================================================================
    // Safe Functions - Should work
    // ========================================================================

    public function testAllowsSafeFunctionNames(): void
    {
        // These should all work fine - no dangerous patterns
        $this->djson->registerFunction('safe', fn($v) => strtoupper($v));
        $this->djson->registerFunction('calculate', fn($v) => $v * 2);
        $this->djson->registerFunction('process_data', fn($v) => $v + 10);
        $this->djson->registerFunction('format_output', fn($v) => strtolower($v));

        $this->assertTrue(true); // If we got here, no exceptions were thrown
    }

    public function testAllowsHashFunctions(): void
    {
        // Hash functions are safe
        $this->djson->registerFunction('md5hash', fn($v) => md5($v));
        $this->djson->registerFunction('sha256', fn($v) => hash('sha256', $v));

        $template = '{
            "md5": "@djson md5hash {{value}}",
            "sha": "@djson sha256 {{value}}"
        }';

        $result = $this->djson->process($template, ['value' => 'test']);

        $this->assertEquals(md5('test'), $result['md5']);
        $this->assertEquals(hash('sha256', 'test'), $result['sha']);
    }

    // ========================================================================
    // "Unsafe" Function Registration - Actually Also Blocked (Troll Mode!)
    // ========================================================================

    public function testRegisterUnsafeFunctionAlsoThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Sorry matey, no unsafe functions allowed");
        $this->expectExceptionMessage("Security is mandatory, not optional");

        // PLOT TWIST: This ALSO throws an exception! No escape hatch!
        $this->djson->registerUnsafeFunction('exec_custom', fn($cmd) => "SIMULATED: $cmd");
    }

    public function testRegisterUnsafeFunctionBlocksEval(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("no unsafe functions allowed");

        $this->djson->registerUnsafeFunction('eval', fn($v) => "SAFE_EVAL: $v");
    }

    public function testRegisterUnsafeFunctionBlocksShellExec(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("no unsafe functions allowed");

        $this->djson->registerUnsafeFunction('shell_exec', fn($v) => "SAFE_SHELL: $v");
    }

    // ========================================================================
    // Error Message Quality
    // ========================================================================

    public function testErrorMessageMentionsUnsafeFunctionOption(): void
    {
        try {
            $this->djson->registerFunction('exec', fn($cmd) => exec($cmd));
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            // Error message mentions registerUnsafeFunction() (but it's a trap!)
            $this->assertStringContainsString('registerUnsafeFunction()', $e->getMessage());
            $this->assertStringContainsString('understand the security risks', $e->getMessage());
        }
    }

    public function testUnsafeFunctionErrorMessageIsHelpful(): void
    {
        try {
            $this->djson->registerUnsafeFunction('exec', fn($cmd) => exec($cmd));
            $this->fail('Expected exception was not thrown');
        } catch (\BadMethodCallException $e) {
            $this->assertStringContainsString('Security is mandatory', $e->getMessage());
            $this->assertStringContainsString('OUTSIDE the template system', $e->getMessage());
        }
    }

    // ========================================================================
    // Database Functions - Should be blocked
    // ========================================================================

    public function testBlocksMysqliFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'mysqli'");

        $this->djson->registerFunction('mysqli_query', fn($q) => "SAFE: $q");
    }

    public function testBlocksMysqlFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'mysql_'");

        $this->djson->registerFunction('mysql_query', fn($q) => "SAFE: $q");
    }

    public function testBlocksPostgreSQLFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'pg_'");

        $this->djson->registerFunction('pg_query', fn($q) => "SAFE: $q");
    }

    public function testBlocksSQLiteFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'sqlite'");

        $this->djson->registerFunction('sqlite_query', fn($q) => "SAFE: $q");
    }

    public function testBlocksPDOFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Will match 'exec' pattern first
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('pdo_exec', fn($q) => "SAFE: $q");
    }

    public function testBlocksPDOName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'pdo'");

        $this->djson->registerFunction('pdo_connect', fn() => "SAFE");
    }

    public function testBlocksODBCFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Will match 'exec' pattern first
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('odbc_exec', fn($q) => "SAFE: $q");
    }

    public function testBlocksODBCName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'odbc_'");

        $this->djson->registerFunction('odbc_connect', fn() => "SAFE");
    }

    public function testBlocksSQLServerFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'sqlsrv_'");

        $this->djson->registerFunction('sqlsrv_query', fn($q) => "SAFE: $q");
    }

    public function testBlocksOracleFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // Will match 'exec' pattern first
        $this->expectExceptionMessage("dangerous pattern 'exec'");

        $this->djson->registerFunction('oci_execute', fn($q) => "SAFE: $q");
    }

    public function testBlocksOracleName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("dangerous pattern 'oci_'");

        $this->djson->registerFunction('oci_connect', fn() => "SAFE");
    }

    // ========================================================================
    // Reflection-Based Content Validation - Should detect dangerous code
    // ========================================================================

    public function testBlocksCallableWithEvalInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'eval'");

        $this->djson->registerFunction('process_code', function ($code) {
            return eval("return $code;");
        });
    }

    public function testBlocksCallableWithShellExecInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern");

        $this->djson->registerFunction('run_command', function ($cmd) {
            return shell_exec($cmd);
        });
    }

    public function testBlocksCallableWithSystemInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'system'");

        $this->djson->registerFunction('process_command', function ($cmd) {
            return system($cmd);
        });
    }

    public function testBlocksCallableWithFileGetContentsInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'file_get_contents'");

        $this->djson->registerFunction('read_file', function ($path) {
            return file_get_contents($path);
        });
    }

    public function testBlocksCallableWithMysqliInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'mysqli'");

        $this->djson->registerFunction('query_database', function ($query) {
            $conn = mysqli_connect('localhost', 'user', 'pass');
            return mysqli_query($conn, $query);
        });
    }

    public function testBlocksCallableWithPgQueryInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'pg_'");

        $this->djson->registerFunction('postgres_query', function ($query) {
            return pg_query($query);
        });
    }

    public function testBlocksCallableWithUnserializeInside(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("callable's source code contains a call to prohibited function pattern 'unserialize'");

        $this->djson->registerFunction('deserialize', function ($data) {
            return unserialize($data);
        });
    }

    // ========================================================================
    // Real-World Safe Use Cases
    // ========================================================================

    public function testRealWorldSafeFunctionsWork(): void
    {
        // Common safe custom functions
        $this->djson->registerFunction('currency', function ($value, $symbol = '$') {
            return $symbol . number_format((float)$value, 2);
        });

        $this->djson->registerFunction('truncate', function ($text, $length = 50) {
            return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
        });

        $this->djson->registerFunction('gravatar', function ($email) {
            return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email)));
        });

        $template = '{
            "price": "@djson currency {{price}} €",
            "excerpt": "@djson truncate {{text}} 20",
            "avatar": "@djson gravatar {{email}}"
        }';

        $result = $this->djson->process($template, [
            'price' => 99.99,
            'text' => 'This is a very long text that needs truncating',
            'email' => 'test@example.com'
        ]);

        $this->assertEquals('€99.99', $result['price']);
        $this->assertEquals('This is a very long ...', $result['excerpt']); // Note: space before ellipsis at position 20
        $this->assertStringStartsWith('https://www.gravatar.com/avatar/', $result['avatar']);
    }
}
