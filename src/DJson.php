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

use Qoliber\DJson\Directives\IfDirective;
use Qoliber\DJson\Directives\UnlessDirective;
use Qoliber\DJson\Directives\ExistsDirective;
use Qoliber\DJson\Directives\ForDirective;
use Qoliber\DJson\Directives\ElseDirective;
use Qoliber\DJson\Directives\SetDirective;
use Qoliber\DJson\Directives\MatchDirective;

/**
 * Main DJson processor class
 *
 * Processes dynamic JSON templates with support for variables, functions, loops, and conditionals.
 */
class DJson
{
    /** Render mode for debug output with pretty printing */
    public const RENDER_MODE_DEBUG = 'debug';

    /** Render mode for compact production output */
    public const RENDER_MODE_COMPACT = 'compact';

    private const DIRECTIVE_ELSE = '@djson else';

    /** @var \Qoliber\DJson\DirectiveInterface[] */
    private array $directives = [];

    /** @var \Qoliber\DJson\FunctionProcessor */
    private \Qoliber\DJson\FunctionProcessor $functions;

    private mixed $lastDirectiveResult = null;

    private string $renderMode = self::RENDER_MODE_COMPACT;

    /**
     * Constructor
     *
     * @param string $renderMode Render mode: RENDER_MODE_DEBUG or RENDER_MODE_COMPACT
     */
    public function __construct(string $renderMode = self::RENDER_MODE_COMPACT)
    {
        $this->setRenderMode($renderMode);
        $this->functions = new FunctionProcessor();
        $this->registerBuiltInDirectives();
    }

    /**
     * Set the render mode for JSON output
     *
     * @param string $mode Render mode: RENDER_MODE_DEBUG or RENDER_MODE_COMPACT
     * @return $this
     * @throws \InvalidArgumentException If mode is invalid
     */
    public function setRenderMode(string $mode): self
    {
        if ($mode !== self::RENDER_MODE_DEBUG && $mode !== self::RENDER_MODE_COMPACT) {
            throw new \InvalidArgumentException(
                "Invalid render mode '{$mode}'. Use DJson::RENDER_MODE_DEBUG or DJson::RENDER_MODE_COMPACT"
            );
        }

        $this->renderMode = $mode;
        return $this;
    }

    /**
     * Get the current render mode
     *
     * @return string Current render mode
     */
    public function getRenderMode(): string
    {
        return $this->renderMode;
    }

    /**
     * Register a custom directive
     *
     * @param \Qoliber\DJson\DirectiveInterface $directive
     * @return void
     */
    public function registerDirective(\Qoliber\DJson\DirectiveInterface $directive): void
    {
        $this->directives[] = $directive;
    }

    /**
     * Register a custom function with security validation
     *
     * Security: This method validates function names against dangerous patterns
     * (eval, exec, shell_exec, file operations, etc.) to prevent code execution risks.
     *
     * @param string $name Function name
     * @param callable $handler Function handler
     * @return void
     * @throws \InvalidArgumentException If function name contains dangerous patterns
     */
    public function registerFunction(string $name, callable $handler): void
    {
        $this->functions->register($name, $handler);
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
        $this->functions->registerUnsafeFunction($name, $handler);
    }

    /**
     * Process a template with provided data and return the result as an array
     *
     * @param array|string $template Template array or JSON string
     * @param array $data Data context
     * @return array Processed result
     * @throws \JsonException If JSON is invalid
     */
    public function process(array|string $template, array $data): array
    {
        if (is_string($template)) {
            $template = json_decode($template, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->processNode($template, $data);
    }

    /**
     * Process a template with provided data and return JSON string
     *
     * @param array|string $template Template array or JSON string
     * @param array $data Data context
     * @param int|null $flags JSON encode flags (null = use render mode default)
     * @return string JSON string result
     * @throws \JsonException If JSON encoding fails
     */
    public function processToJson(array|string $template, array $data, ?int $flags = null): string
    {
        $result = $this->process($template, $data);

        // If no flags provided, use render mode to determine formatting
        if ($flags === null) {
            $flags = JSON_THROW_ON_ERROR;
            if ($this->renderMode === self::RENDER_MODE_DEBUG) {
                $flags |= JSON_PRETTY_PRINT;
            }
        }

        return json_encode($result, $flags);
    }

    /**
     * Process a JSON string template from a file
     *
     * @param string $templatePath Path to template file
     * @param array $data Data context
     * @return array Processed result
     * @throws \InvalidArgumentException If file not found
     * @throws \JsonException If JSON is invalid
     */
    public function processFile(string $templatePath, array $data): array
    {
        if (!file_exists($templatePath)) {
            throw new \InvalidArgumentException("Template file not found: {$templatePath}");
        }

        $templateJson = file_get_contents($templatePath);
        return $this->process($templateJson, $data);
    }

    /**
     * Process a JSON string template from a file and return JSON string
     *
     * @param string $templatePath Path to template file
     * @param array $data Data context
     * @param int|null $flags JSON encode flags (null = use render mode default)
     * @return string JSON string result
     * @throws \InvalidArgumentException If file not found
     * @throws \JsonException If JSON is invalid or encoding fails
     */
    public function processFileToJson(string $templatePath, array $data, ?int $flags = null): string
    {
        $result = $this->processFile($templatePath, $data);

        // If no flags provided, use render mode to determine formatting
        if ($flags === null) {
            $flags = JSON_THROW_ON_ERROR;
            if ($this->renderMode === self::RENDER_MODE_DEBUG) {
                $flags |= JSON_PRETTY_PRINT;
            }
        }

        return json_encode($result, $flags);
    }

    /**
     * Validate a template without processing it
     * Checks for:
     * - Valid JSON syntax
     * - Valid directive syntax
     * - Valid function names
     *
     * @param array|string $template Template array or JSON string
     * @return array Array of error messages (empty if valid)
     */
    public function validate(array|string $template): array
    {
        $errors = [];

        // Parse JSON if string
        if (is_string($template)) {
            try {
                $template = json_decode($template, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $errors[] = "Invalid JSON syntax: {$e->getMessage()}";
                return $errors;
            }
        }

        // Validate the template structure
        $this->validateNode($template, $errors, '');

        return $errors;
    }

    /**
     * Recursively validate a template node
     *
     * @param mixed $node Template node to validate
     * @param array &$errors Array of error messages
     * @param string $path Current path in template
     * @return void
     */
    private function validateNode(mixed $node, array &$errors, string $path): void
    {
        if (!is_array($node)) {
            // Validate string values for functions and variables
            if (is_string($node)) {
                $this->validateValue($node, $errors, $path);
            }
            return;
        }

        foreach ($node as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;

            if (is_string($key)) {
                // Check if it's a directive
                if (str_starts_with($key, '@djson')) {
                    $this->validateDirective($key, $errors, $currentPath);
                }
            }

            // Recursively validate the value
            $this->validateNode($value, $errors, $currentPath);
        }
    }

    /**
     * Validate a directive key
     *
     * @param string $key Directive key
     * @param array &$errors Array of error messages
     * @param string $path Current path in template
     * @return void
     */
    private function validateDirective(string $key, array &$errors, string $path): void
    {
        $matched = false;

        foreach ($this->directives as $directive) {
            if ($directive->matches($key)) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            $errors[] = "Invalid directive at '$path': $key";
        }
    }

    /**
     * Validate a value string for functions
     *
     * @param string $value Value to validate
     * @param array &$errors Array of error messages
     * @param string $path Current path in template
     * @return void
     */
    private function validateValue(string $value, array &$errors, string $path): void
    {
        // Check for function syntax
        if (str_starts_with($value, '@djson ')) {
            $isValid = $this->functions->validateFunction($value);
            if (!$isValid) {
                // Extract function name for error message
                preg_match('/^@djson\s+(\w+)/', $value, $matches);
                $functionName = $matches[1] ?? 'unknown';
                $errors[] = "Unknown function '$functionName' at '$path': $value";
            }
        }
    }

    /**
     * Process a single node (could be array, object, or scalar)
     *
     * @param mixed $node Node to process
     * @param array $context Data context
     * @return mixed Processed result
     */
    public function processNode(mixed $node, array $context): mixed
    {
        if (!is_array($node)) {
            return $this->processValue($node, $context);
        }

        // Reset directive state for this node
        $previousDirectiveResult = $this->lastDirectiveResult;
        $this->lastDirectiveResult = false;

        $result = [];
        $isSequential = array_is_list($node);

        foreach ($node as $key => $value) {
            // Check if key is a directive (only string keys can be directives)
            $directiveResult = false;
            if (is_string($key)) {
                // Special handling for else directive
                if ($key === self::DIRECTIVE_ELSE) {
                    // Only process else if previous directive was null (condition failed)
                    if ($this->lastDirectiveResult === null) {
                        $elseResult = $this->processNode($value, $context);
                        if (is_array($elseResult) && $this->isAssocArray($elseResult)) {
                            $result = array_merge($result, $elseResult);
                        } else {
                            $result[] = $elseResult;
                        }
                    }
                    // Reset after else
                    $this->lastDirectiveResult = false;
                    continue;
                }

                $directiveResult = $this->processDirectiveKey($key, $value, $context);

                // Track result for potential else directive
                if ($directiveResult !== false) {
                    $this->lastDirectiveResult = $directiveResult;
                }
            }

            if ($directiveResult !== false) {
                // Directive was processed
                if ($directiveResult === null) {
                    // Directive returned null (condition failed), skip
                    continue;
                }

                // Merge directive result into parent
                if (is_array($directiveResult)) {
                    if ($this->isAssocArray($directiveResult)) {
                        // Merge associative array
                        $result = array_merge($result, $directiveResult);
                    } else {
                        // It's a sequential array (from loop), return it directly
                        return $directiveResult;
                    }
                } else {
                    $result[] = $directiveResult;
                }
            } else {
                // Regular key-value pair
                $processedKey = $this->processValue($key, $context);
                $processedValue = $this->processNode($value, $context);

                if ($isSequential) {
                    $result[] = $processedValue;
                } else {
                    $result[$processedKey] = $processedValue;
                }
            }
        }

        // Restore previous directive state
        $this->lastDirectiveResult = $previousDirectiveResult;

        return $result;
    }

    /**
     * Check if key is a directive and process it
     * Returns false if not a directive, null if condition failed, or processed result
     *
     * @param string $key Directive key
     * @param mixed $value Associated value
     * @param array $context Data context
     * @return mixed False if not a directive, null if condition failed, or processed result
     */
    private function processDirectiveKey(string $key, mixed $value, array $context): mixed
    {
        foreach ($this->directives as $directive) {
            if ($directive->matches($key)) {
                $params = $directive->parse($key);
                return $directive->process($params, $value, $context, $this);
            }
        }

        return false;
    }

    /**
     * Process a value - replace variables and apply functions
     *
     * @param mixed $value Value to process
     * @param array $context Data context
     * @return mixed Processed value
     */
    private function processValue(mixed $value, array $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        // Check for function syntax: @djson functionName ...
        if ($this->functions->hasFunction($value)) {
            return $this->functions->apply($value, $context);
        }

        // Check if the entire string is a single variable or expression {{variable}} or {{condition ? true : false}}
        if (preg_match('/^\{\{([^}]+)\}\}$/', $value, $matches)) {
            $expression = trim($matches[1]);

            // Check for ternary operator
            if ($this->isTernaryExpression($expression)) {
                return $this->evaluateTernary($expression, $context);
            }

            return $this->getValue($expression, $context);
        }

        // Check for variable syntax {{variable}} in string with other content
        return preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function ($matches) use ($context) {
                $expression = trim($matches[1]);

                // Check for ternary operator
                if ($this->isTernaryExpression($expression)) {
                    $resolvedValue = $this->evaluateTernary($expression, $context);
                } else {
                    $resolvedValue = $this->getValue($expression, $context);
                }

                // Convert to string for interpolation within text
                return $resolvedValue === null ? '' : (string)$resolvedValue;
            },
            $value
        );
    }

    /**
     * Get a value from context using dot notation
     * Supports both arrays and objects with getters/properties
     *
     * @param string $path Dot-notation path to value
     * @param array $context Data context
     * @return mixed Value at path or null if not found
     */
    public function getValue(string $path, array $context): mixed
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
     * Evaluate a conditional expression
     *
     * @param string $condition Condition expression
     * @param array $context Data context
     * @return bool Evaluation result
     */
    public function evaluateExpression(string $condition, array $context): bool
    {
        $condition = trim($condition);

        // Handle logical OR (||) - lowest precedence
        if (str_contains($condition, '||')) {
            $parts = explode('||', $condition, 2);
            if (count($parts) === 2) {
                $left = $this->evaluateExpression(trim($parts[0]), $context);
                $right = $this->evaluateExpression(trim($parts[1]), $context);
                return $left || $right;
            }
        }

        // Handle logical AND (&&) - higher precedence than OR
        if (str_contains($condition, '&&')) {
            $parts = explode('&&', $condition, 2);
            if (count($parts) === 2) {
                $left = $this->evaluateExpression(trim($parts[0]), $context);
                $right = $this->evaluateExpression(trim($parts[1]), $context);
                return $left && $right;
            }
        }

        // Handle logical NOT (!) - highest precedence
        if (str_starts_with($condition, '!')) {
            $innerCondition = trim(substr($condition, 1));
            return !$this->evaluateExpression($innerCondition, $context);
        }

        // Check for comparison operators
        $operators = [
            '==' => fn($a, $b) => $a == $b,
            '!=' => fn($a, $b) => $a != $b,
            '>=' => fn($a, $b) => $a >= $b,
            '<=' => fn($a, $b) => $a <= $b,
            '>' => fn($a, $b) => $a > $b,
            '<' => fn($a, $b) => $a < $b,
        ];

        foreach ($operators as $operator => $comparator) {
            if (str_contains($condition, $operator)) {
                $parts = explode($operator, $condition, 2);
                if (count($parts) === 2) {
                    $left = $this->resolveConditionValue(trim($parts[0]), $context);
                    $right = $this->resolveConditionValue(trim($parts[1]), $context);
                    return $comparator($left, $right);
                }
            }
        }

        // Simple truthiness check
        $value = $this->getValue($condition, $context);
        return !empty($value);
    }

    /**
     * Resolve a value in a condition expression
     *
     * @param string $value Value to resolve
     * @param array $context Data context
     * @return mixed Resolved value
     */
    private function resolveConditionValue(string $value, array $context): mixed
    {
        $value = trim($value);

        // Check for quoted strings
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }

        // Check for booleans
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }

        // Check for null
        if ($value === 'null') {
            return null;
        }

        // Check for numbers
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float)$value : (int)$value;
        }

        // Otherwise, treat as variable path
        return $this->getValue($value, $context);
    }

    /**
     * Check if array is associative
     *
     * @param array $array Array to check
     * @return bool True if associative, false if sequential
     */
    private function isAssocArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        return !array_is_list($array);
    }

    /**
     * Check if expression contains a ternary operator
     *
     * @param string $expression Expression to check
     * @return bool True if contains ternary operator
     */
    private function isTernaryExpression(string $expression): bool
    {
        return str_contains($expression, '?') && str_contains($expression, ':');
    }

    /**
     * Evaluate a ternary expression: condition ? trueValue : falseValue
     *
     * @param string $expression Ternary expression
     * @param array $context Data context
     * @return mixed Result of ternary evaluation
     */
    private function evaluateTernary(string $expression, array $context): mixed
    {
        // Split by ? to get condition and values
        $parts = explode('?', $expression, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $condition = trim($parts[0]);
        $values = trim($parts[1]);

        // Split values by : to get true and false values
        $valueParts = explode(':', $values, 2);
        if (count($valueParts) !== 2) {
            return null;
        }

        $trueValue = trim($valueParts[0]);
        $falseValue = trim($valueParts[1]);

        // Evaluate condition
        $conditionResult = $this->evaluateExpression($condition, $context);

        // Return appropriate value
        $selectedValue = $conditionResult ? $trueValue : $falseValue;

        // Resolve the selected value
        return $this->resolveConditionValue($selectedValue, $context);
    }

    /**
     * Register built-in directives
     *
     * @return void
     */
    private function registerBuiltInDirectives(): void
    {
        $this->registerDirective(new ForDirective());
        $this->registerDirective(new IfDirective());
        $this->registerDirective(new UnlessDirective());
        $this->registerDirective(new ExistsDirective());
        $this->registerDirective(new ElseDirective());
        $this->registerDirective(new SetDirective());
        $this->registerDirective(new MatchDirective());
    }
}
