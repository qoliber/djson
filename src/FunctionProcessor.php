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

namespace Qoliber\DJson;

/**
 * Function processor for template functions
 *
 * Handles registration and execution of template functions with parameter parsing.
 */
class FunctionProcessor
{
    /** @var array<string, callable> */
    private array $functions = [];

    /**
     * Dangerous function name patterns that pose security risks
     * These patterns detect functions that could execute code, access filesystem, or run system commands
     */
    private const DANGEROUS_PATTERNS = [
        // Code execution
        'eval', 'assert', 'call_user_func', 'call_user_func_array',
        // System commands
        'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open', 'pcntl_exec',
        // Filesystem operations
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'unlink', 'rmdir',
        'chmod', 'chown', 'rename', 'copy', 'mkdir',
        // Includes
        'include', 'require', 'include_once', 'require_once',
        // Serialization (can lead to object injection)
        'unserialize',
        // Reflection (can access private methods)
        'reflection',
        // Database functions - MySQL
        'mysqli', 'mysql_',
        // Database functions - PostgreSQL
        'pg_',
        // Database functions - SQLite
        'sqlite',
        // Database functions - PDO
        'pdo',
        // Database functions - ODBC
        'odbc_',
        // Database functions - SQL Server
        'sqlsrv_',
        // Database functions - Oracle
        'oci_'
    ];

    public function __construct()
    {
        $this->registerBuiltInFunctions();
    }

    /**
     * Register a custom function with security validation
     *
     * @param string $name Function name
     * @param callable $handler Function handler
     * @return void
     * @throws \InvalidArgumentException If function name contains dangerous patterns or callable contains dangerous code
     */
    public function register(string $name, callable $handler): void
    {
        $this->validateFunctionSafety($name);
        $this->validateCallableContent($name, $handler);
        $this->functions[$name] = $handler;
    }

    /**
     * Validate that a function name doesn't contain dangerous patterns
     *
     * @param string $name Function name to validate
     * @return void
     * @throws \InvalidArgumentException If function name is potentially dangerous
     */
    private function validateFunctionSafety(string $name): void
    {
        $nameLower = strtolower($name);

        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (str_contains($nameLower, $pattern)) {
                throw new \InvalidArgumentException(
                    "Cannot register function '$name': Function name contains potentially dangerous pattern '$pattern'. " .
                    "This library blocks registration of functions that could execute code, run system commands, " .
                    "or perform dangerous filesystem operations. If you absolutely need this functionality and " .
                    "understand the security risks, use registerUnsafeFunction() instead."
                );
            }
        }
    }

    /**
     * Validate that callable content doesn't contain dangerous code
     * Uses PHP Reflection to inspect the callable source code for prohibited function calls
     *
     * @param string $name Function name (for error messages)
     * @param callable $handler Callable to validate
     * @return void
     * @throws \InvalidArgumentException If callable contains dangerous code patterns
     */
    private function validateCallableContent(string $name, callable $handler): void
    {
        try {
            $reflection = null;

            if ($handler instanceof \Closure) {
                $reflection = new \ReflectionFunction($handler);
            } elseif (is_array($handler)) {
                $reflection = new \ReflectionMethod($handler[0], $handler[1]);
            } elseif (is_string($handler) && function_exists($handler)) {
                $reflection = new \ReflectionFunction($handler);
            } elseif (is_object($handler) && method_exists($handler, '__invoke')) {
                $reflection = new \ReflectionMethod($handler, '__invoke');
            }

            if ($reflection === null) {
                return;
            }

            $filename = $reflection->getFileName();
            $startLine = $reflection->getStartLine();
            $endLine = $reflection->getEndLine();

            if ($filename === false || $startLine === false || $endLine === false) {
                return;
            }

            $source = file($filename);
            if ($source === false) {
                return;
            }

            $functionSource = implode('', array_slice($source, $startLine - 1, $endLine - $startLine + 1));
            $functionSourceLower = strtolower($functionSource);

            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                $escapedPattern = preg_quote($pattern, '/');

                // Match pattern at word boundary, optionally followed by underscore and more word chars, then parenthesis
                // This handles: eval(), mysqli_connect(), pg_query(), etc.
                $regex = '/\b' . $escapedPattern . '[\w_]*\s*\(/i';

                if (preg_match($regex, $functionSourceLower)) {
                    throw new \InvalidArgumentException(
                        "Cannot register function '$name': The callable's source code contains a call to prohibited function pattern '$pattern'. " .
                        "This library blocks registration of functions that could execute code, run system commands, access databases, " .
                        "or perform dangerous filesystem operations. Templates are for rendering data, not executing code."
                    );
                }
            }
        } catch (\ReflectionException $e) {
            return;
        }
    }

    /**
     * Register an unsafe function (bypasses security validation)
     *
     * Just kidding! There is NO bypass. Security is MANDATORY.
     *
     * This library takes security seriously. If you need dangerous functionality,
     * implement it outside the template system where you can properly control access.
     *
     * @param string $name Function name
     * @param callable $handler Function handler
     * @return void
     * @throws \BadMethodCallException Always throws - unsafe functions are not allowed
     */
    public function registerUnsafeFunction(string $name, callable $handler): void
    {
        throw new \BadMethodCallException(
            "Sorry matey, no unsafe functions allowed in DJson! Security is mandatory, not optional. " .
            "If you need to execute system commands or access files, do it OUTSIDE the template system " .
            "where you have proper access controls. Templates are for rendering data, not executing code."
        );
    }

    /**
     * Check if a value contains function syntax: @djson functionName ...
     *
     * @param mixed $value Value to check
     * @return bool True if contains function syntax
     */
    public function hasFunction(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, '@djson ');
    }

    /**
     * Validate that function names in expression exist
     *
     * @param string $expression Expression to validate
     * @return bool True if all functions exist
     */
    public function validateFunction(string $expression): bool
    {
        // Remove @djson prefix
        if (!str_starts_with($expression, '@djson ')) {
            return false;
        }

        $expression = substr($expression, 7); // strlen('@djson ')

        // Extract function chain (allow letters, numbers, underscores, pipes)
        if (!preg_match('/^([a-z0-9_|]+)(\s|$)/i', $expression, $matches)) {
            return false;
        }

        $functionChain = $matches[1];
        $functions = explode('|', $functionChain);

        // Check all functions exist
        foreach ($functions as $funcName) {
            $funcName = trim($funcName);
            if (!isset($this->functions[$funcName])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply function(s) to a value
     * Supports: @djson upper {{value}}
     * Supports chaining: @djson upper|trim {{value}}
     * Supports params: @djson number_format {{value}} 2
     *
     * @param string $expression Function expression
     * @param array $context Data context
     * @return mixed Result of function application
     */
    public function apply(string $expression, array $context): mixed
    {
        // Remove @djson prefix
        $expression = substr($expression, 7); // strlen('@djson ')

        // Extract function chain and value
        // Pattern: "functionName|function2 {{variable}} param1 param2"
        // Allow letters, numbers, underscores, and pipes in function names
        if (!preg_match('/^([a-z0-9_|]+)\s+(.+)$/i', $expression, $matches)) {
            return $expression;
        }

        $functionChain = $matches[1];
        $rest = $matches[2];

        // Parse the rest to extract value and parameters
        $value = $this->extractValue($rest, $context);
        $params = $this->extractParams($rest);

        // Apply function chain
        $functions = explode('|', $functionChain);
        $result = $value;

        foreach ($functions as $funcName) {
            $funcName = trim($funcName);
            if (isset($this->functions[$funcName])) {
                $result = call_user_func($this->functions[$funcName], $result, ...$params);
            }
        }

        return $result;
    }

    /**
     * Extract value from expression, processing {{variable}} syntax
     *
     * @param string $expression Expression to extract value from
     * @param array $context Data context
     * @return mixed Extracted value
     */
    private function extractValue(string $expression, array $context): mixed
    {
        // Check for {{variable}}
        if (preg_match('/\{\{([^}]+)\}\}/', $expression, $matches)) {
            $path = trim($matches[1]);
            return $this->getValue($path, $context);
        }

        // Check for quoted string
        if (preg_match('/^["\'](.+)["\']/', $expression, $matches)) {
            return $matches[1];
        }

        // Check for number
        if (is_numeric(trim(explode(' ', $expression)[0]))) {
            return (float)trim(explode(' ', $expression)[0]);
        }

        return $expression;
    }

    /**
     * Extract additional parameters from expression
     *
     * @param string $expression Expression to extract parameters from
     * @return array Array of extracted parameters
     */
    private function extractParams(string $expression): array
    {
        // Remove {{variable}} part
        $expression = preg_replace('/\{\{[^}]+\}\}/', '', $expression);
        $expression = trim($expression);

        if (empty($expression)) {
            return [];
        }

        // Parse remaining parameters respecting quoted strings
        $params = [];
        $length = strlen($expression);
        $i = 0;
        $current = '';
        $inQuotes = false;
        $quoteChar = null;

        while ($i < $length) {
            $char = $expression[$i];

            if (($char === '"' || $char === "'") && ($i === 0 || $expression[$i - 1] !== '\\')) {
                if (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                    // Add the quoted string without quotes
                    if (!empty($current)) {
                        $params[] = $current;
                        $current = '';
                    }
                    $i++;
                    continue;
                }
                $i++;
                continue;
            }

            if ($char === ' ' && !$inQuotes) {
                if (!empty($current)) {
                    // Check if it's a number
                    if (is_numeric($current)) {
                        $params[] = str_contains($current, '.') ? (float)$current : (int)$current;
                    } else {
                        $params[] = $current;
                    }
                    $current = '';
                }
            } else {
                $current .= $char;
            }

            $i++;
        }

        // Add last parameter
        if (!empty($current)) {
            if (is_numeric($current)) {
                $params[] = str_contains($current, '.') ? (float)$current : (int)$current;
            } else {
                $params[] = $current;
            }
        }

        return $params;
    }

    /**
     * Get value from context using dot notation
     * Supports both arrays and objects with getters/properties
     *
     * @param string $path Dot-notation path to value
     * @param array $context Data context
     * @return mixed Value at path or null if not found
     */
    private function getValue(string $path, array $context): mixed
    {
        $parts = explode('.', $path);
        $value = $context;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } elseif (is_object($value)) {
                $value = $this->getObjectProperty($value, $part);
                if ($value === null) {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Get property value from an object
     * Tries getter methods first, then public properties
     *
     * @param object $object Object to get property from
     * @param string $property Property name
     * @return mixed Property value or null if not found
     */
    private function getObjectProperty(object $object, string $property): mixed
    {
        // Try getter method: getName() for property 'name'
        $getter = 'get' . ucfirst($property);
        if (method_exists($object, $getter)) {
            return $object->$getter();
        }

        // Try is method: isActive() for property 'active'
        $isMethod = 'is' . ucfirst($property);
        if (method_exists($object, $isMethod)) {
            return $object->$isMethod();
        }

        // Try has method: hasPermission() for property 'permission'
        $hasMethod = 'has' . ucfirst($property);
        if (method_exists($object, $hasMethod)) {
            return $object->$hasMethod();
        }

        // Try direct property access
        if (property_exists($object, $property)) {
            return $object->$property;
        }

        return null;
    }

    /**
     * Register all built-in functions
     *
     * @return void
     */
    private function registerBuiltInFunctions(): void
    {
        // String functions
        $this->register('upper', fn($value) => strtoupper((string)$value));
        $this->register('lower', fn($value) => strtolower((string)$value));
        $this->register('capitalize', fn($value) => ucfirst((string)$value));
        $this->register('title', fn($value) => ucwords((string)$value));
        $this->register('trim', fn($value) => trim((string)$value));
        $this->register('escape', fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
        $this->register('json_encode', fn($value) => json_encode($value));

        $this->register('slug', function($value) {
            $value = strtolower((string)$value);
            $value = preg_replace('/[^a-z0-9]+/', '-', $value);
            return trim($value, '-');
        });

        $this->register('substr', function($value, $start = 0, $length = null) {
            return $length === null ? substr((string)$value, $start) : substr((string)$value, $start, $length);
        });

        $this->register('replace', function($value, $search, $replace = '') {
            return str_replace($search, $replace, (string)$value);
        });

        // Number functions
        $this->register('number_format', function($value, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
            return number_format((float)$value, (int)$decimals, $decPoint, $thousandsSep);
        });

        $this->register('round', fn($value, $precision = 0) => round((float)$value, (int)$precision));
        $this->register('ceil', fn($value) => ceil((float)$value));
        $this->register('floor', fn($value) => floor((float)$value));
        $this->register('abs', fn($value) => abs((float)$value));

        // Date functions
        $this->register('date', function($value, $format = 'Y-m-d H:i:s') {
            if (is_numeric($value)) {
                return date($format, (int)$value);
            }
            if (is_string($value)) {
                $timestamp = strtotime($value);
                return $timestamp ? date($format, $timestamp) : $value;
            }
            return $value;
        });

        $this->register('strtotime', fn($value) => strtotime((string)$value));

        // Array functions
        $this->register('count', fn($value) => is_array($value) ? count($value) : 0);
        $this->register('first', fn($value) => is_array($value) && !empty($value) ? reset($value) : null);
        $this->register('last', fn($value) => is_array($value) && !empty($value) ? end($value) : null);

        $this->register('join', function($value, $separator = ',') {
            return is_array($value) ? implode($separator, $value) : $value;
        });

        $this->register('sort', function($value) {
            if (is_array($value)) {
                sort($value);
                return $value;
            }
            return $value;
        });

        $this->register('unique', function($value) {
            return is_array($value) ? array_values(array_unique($value)) : $value;
        });

        // Utility functions
        $this->register('default', fn($value, $default = '') => empty($value) ? $default : $value);

        $this->register('coalesce', function($value, ...$alternatives) {
            if (!empty($value)) {
                return $value;
            }
            foreach ($alternatives as $alt) {
                if (!empty($alt)) {
                    return $alt;
                }
            }
            return null;
        });
    }
}
