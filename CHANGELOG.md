# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.0] - 2025-11-18

### Summary
Version 1.5.0 enhances security with **database function blocking** and **reflection-based code inspection**. DJson now inspects the actual source code of registered callables using PHP Reflection, preventing dangerous code from being hidden inside safe-looking function names.

### Added

#### Enhanced Security - Database Function Blocking
- **Database function patterns blocked** (`src/FunctionProcessor.php`)
  - MySQL: `mysqli`, `mysql_`
  - PostgreSQL: `pg_`
  - SQLite: `sqlite`
  - PDO: `pdo`
  - ODBC: `odbc_`
  - SQL Server: `sqlsrv_`
  - Oracle: `oci_`
  - Prevents database access vulnerabilities in templates

**Database Functions are Blocked:**
```php
// BLOCKED - function name contains database pattern
$djson->registerFunction('mysqli_query', fn($q) => "SAFE: $q");
// Error: "dangerous pattern 'mysqli'"

$djson->registerFunction('pg_query', fn($q) => "SAFE: $q");
// Error: "dangerous pattern 'pg_'"

$djson->registerFunction('pdo_connect', fn() => "SAFE");
// Error: "dangerous pattern 'pdo'"
```

#### Reflection-Based Code Inspection
- **Deep callable content validation** (`src/FunctionProcessor.php`)
  - Uses PHP Reflection API to inspect callable source code
  - Detects dangerous function calls inside registered callables
  - Prevents security bypasses by wrapping dangerous code in safe function names
  - Analyzes closures, methods, and callable objects
  - Validates against all dangerous patterns (code execution, system commands, database, filesystem)

**No Security Bypass - Code is Inspected:**
```php
// BLOCKED - Even though function name is safe, eval() detected inside!
$djson->registerFunction('process_data', function ($input) {
    return eval($input);
});
// Error: "callable's source code contains a call to prohibited function pattern 'eval'"

// BLOCKED - Database access detected inside callable
$djson->registerFunction('get_user', function ($id) {
    return mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
});
// Error: "callable's source code contains a call to prohibited function pattern 'mysqli'"

// BLOCKED - System commands detected inside
$djson->registerFunction('run_task', function ($cmd) {
    return shell_exec($cmd);
});
// Error: "callable's source code contains a call to prohibited function pattern 'exec'"
```

### Testing
- **Enhanced Security Test Suite** (`tests/SecurityTest.php`)
  - +17 new tests (was 29 tests in v1.4.0, now 46 tests)
  - Tests database function name blocking (8 tests)
  - Tests reflection-based code inspection (7 tests)
  - Tests for all database patterns: MySQL, PostgreSQL, SQLite, PDO, ODBC, SQL Server, Oracle
  - Tests for dangerous code hidden inside callables
  - Removed obsolete `create_function` test (removed in PHP 8.0, library requires PHP 8.1+)

### Test Statistics
- **229 total tests** (was 212 in v1.4.0)
- **664 total assertions** (was 630 in v1.4.0)
- **+17 new security tests**
- **+34 new assertions**

### Changed
- **Removed obsolete `create_function` pattern** (`src/FunctionProcessor.php`)
  - `create_function()` was removed in PHP 8.0
  - DJson requires PHP 8.1+, so this pattern is no longer relevant
  - Cleaner, more accurate security validation

### Files Changed
- `src/FunctionProcessor.php` - Database patterns added, reflection-based validation implemented, removed obsolete create_function pattern
- `tests/SecurityTest.php` - Enhanced security test coverage, removed obsolete create_function test
- `README.md` - Updated security documentation with database blocking and reflection examples

### Backward Compatibility
- Fully backward compatible for safe functions
- BREAKING: Database function names now blocked (intentional security enhancement)
- BREAKING: Callables containing dangerous code now blocked (intentional security enhancement)

## [1.4.0] - 2025-11-18

### Summary
Version 1.4.0 adds **mandatory security protection** against dangerous function registration, preventing code execution and system command vulnerabilities. No backdoors, no compromises - security is mandatory, not optional.

### Added

#### Security Feature - Dangerous Function Name Validation
- **Mandatory security validation** when registering custom functions
  - Blocks registration of functions with dangerous patterns (`eval`, `exec`, `shell_exec`, etc.)
  - Prevents code execution vulnerabilities
  - Blocks system command injection risks
  - Prevents filesystem access vulnerabilities
  - Protects against serialization attacks

- **Comprehensive Pattern Detection** (`src/FunctionProcessor.php`)
  - Code execution: `eval`, `assert`, `call_user_func`
  - System commands: `exec`, `shell_exec`, `system`, `passthru`, `popen`, `proc_open`
  - Filesystem: `file_get_contents`, `file_put_contents`, `fopen`, `unlink`, `chmod`, `rename`
  - Includes: `include`, `require`, `include_once`, `require_once`
  - Serialization: `unserialize`
  - Reflection: `reflection`

- **No Escape Hatch** - `registerUnsafeFunction()` also throws exception
  - Method exists but ALSO throws `BadMethodCallException`
  - Security is mandatory, not optional
  - No backdoors, no compromises
  - If you need dangerous functionality, implement it outside the template system

**Security by Default (No Compromises):**
```php
// This is BLOCKED - throws InvalidArgumentException
$djson->registerFunction('exec', fn($cmd) => shell_exec($cmd));
// Error suggests trying registerUnsafeFunction()...

// BUT PLOT TWIST: That ALSO throws! BadMethodCallException
$djson->registerUnsafeFunction('exec', fn($cmd) => shell_exec($cmd));
// Error: "Sorry matey, no unsafe functions allowed! Security is mandatory!"

// These work fine - safe functions only
$djson->registerFunction('currency', fn($v) => '$' . $v);
$djson->registerFunction('md5hash', fn($v) => md5($v)); // Safe!
```

### Fixed

#### Custom Function Name Parsing Bug
- **Fixed regex pattern in FunctionProcessor** (`src/FunctionProcessor.php`)
  - Changed `/^([a-z_|]+)/i` to `/^([a-z0-9_|]+)/i` in `validateFunction()` method
  - Changed `/^([a-z_|]+)/i` to `/^([a-z0-9_|]+)/i` in `apply()` method
  - Function names can now contain digits: `base64`, `md5hash`, `sha256`, `format2decimals`, etc.
  - Previously, functions with numbers would be ignored and rendered as literal strings

**Before (broken):**
```php
$djson->registerFunction('base64', fn($v) => base64_encode($v));
// Result: "base64 {{email}}" (literal string)
```

**After (fixed):**
```php
$djson->registerFunction('base64', fn($v) => base64_encode($v));
// Result: "dGVzdEBleGFtcGxlLmNvbQ==" (actual base64)
```

### Added

#### Testing
- **Security Test Suite** (`tests/SecurityTest.php`)
  - 29 tests for dangerous function name validation
  - Tests blocking of code execution functions (eval, assert, create_function)
  - Tests blocking of system command functions (exec, shell_exec, system, passthru)
  - Tests blocking of filesystem functions (file_get_contents, unlink, chmod)
  - Tests blocking of include/require functions
  - Tests blocking of serialization functions (unserialize)
  - Tests pattern detection in function names (case-insensitive)
  - Tests that safe function names work correctly
  - Tests that registerUnsafeFunction() ALSO throws exception (troll mode!)

### Test Statistics
- **212 total tests** (was 183 in v1.3.0)
- **630 total assertions** (was 572 in v1.3.0)
- **+29 new security tests**
- **+58 new assertions**

### Files Changed
- `src/FunctionProcessor.php` - Security validation with dangerous pattern detection
- `src/DJson.php` - Added registerUnsafeFunction() method (troll mode)
- `tests/SecurityTest.php` - Security test coverage (+340 lines)

### Backward Compatibility
- Fully backward compatible for safe functions
- BREAKING: Dangerous function names now blocked (this is intentional security)
- All safe functions continue to work
- No legitimate use case should be affected

---

## [1.3.0] - 2025-11-18

### Summary
Version 1.3.0 fixes a critical bug that prevented custom functions with digits in their names (like `base64`, `md5hash`, `sha256`) from working.

### Fixed

#### Custom Function Name Parsing Bug
- **Fixed regex pattern in FunctionProcessor** (`src/FunctionProcessor.php`)
  - Changed `/^([a-z_|]+)/i` to `/^([a-z0-9_|]+)/i` in `validateFunction()` method
  - Changed `/^([a-z_|]+)/i` to `/^([a-z0-9_|]+)/i` in `apply()` method
  - Function names can now contain digits: `base64`, `md5hash`, `sha256`, `format2decimals`, etc.
  - Previously, functions with numbers would be ignored and rendered as literal strings

**Before (broken):**
```php
$djson->registerFunction('base64', fn($v) => base64_encode($v));
// Result: "base64 {{email}}" (literal string)
```

**After (fixed):**
```php
$djson->registerFunction('base64', fn($v) => base64_encode($v));
// Result: "dGVzdEBleGFtcGxlLmNvbQ==" (actual base64)
```

### Added

#### Testing
- **Custom Functions with Numbers Test Suite** (`tests/CustomFunctionsWithNumbersTest.php`)
  - 13 tests covering function names with digits
  - Tests for numbers at end: `test1`, `hash1`
  - Tests for numbers in middle: `base64encode`, `format2decimals`
  - Tests for multiple digits: `sha256`, `sha512hash`, `gravatar200`
  - Tests for numbers at beginning: `3times`
  - Tests in loops, with chaining, and with parameters
  - Real-world use case: gravatar URL generation

### Test Statistics
- **183 total tests** (was 170 in v1.2.0)
- **572 total assertions** (was 554 in v1.2.0)
- **+13 new tests** specifically for numbered function names
- **+18 new assertions**

### Files Changed
- `src/FunctionProcessor.php` - Fixed regex patterns to allow digits in function names (2 lines changed)
- `tests/CustomFunctionsWithNumbersTest.php` - Comprehensive test coverage (+334 lines)

### Backward Compatibility
- Fully backward compatible
- No breaking changes
- All existing tests pass
- Function names without numbers continue to work as before
- This fix only enables previously broken functionality

---

## [1.2.0] - 2025-11-18

### Summary
Version 1.2.0 adds **render mode support** for debug vs production JSON output, comprehensive **real-world examples** covering all features, extensive **JSON-LD/Schema.org test coverage**, and a project **roadmap** for future development. Includes 45 new tests and 11 practical examples with documentation.

### Added

#### Render Mode Feature
- **Render Mode Support**: Choose between debug and production output modes
  - `RENDER_MODE_DEBUG` - Pretty-printed JSON with indentation (uses `JSON_PRETTY_PRINT`)
  - `RENDER_MODE_COMPACT` - Single-line compact JSON for production (default)
  - `setRenderMode(string $mode): self` - Fluent interface to set render mode
  - `getRenderMode(): string` - Get current render mode
  - Constructor accepts optional `$renderMode` parameter (defaults to `RENDER_MODE_COMPACT`)

- **Enhanced processToJson() Method** (`src/DJson.php`)
  - Automatically applies `JSON_PRETTY_PRINT` when in DEBUG mode
  - Maintains backward compatibility with explicit `$flags` parameter
  - Optimized for production with compact single-line output by default

- **Enhanced processFileToJson() Method** (`src/DJson.php`)
  - Respects render mode for file-based template processing
  - Consistent behavior with `processToJson()`

#### Testing

- **JSON String Template Test Suite** (`tests/JsonStringAndScalarArrayTest.php`)
  - 18 tests validating JSON string input processing (not just PHP arrays)
  - Tests for scalar arrays (strings, numbers, booleans, mixed types)
  - Loop over scalar arrays with index support
  - JSON string to JSON string transformation tests
  - Validates that templates work as JSON strings (real-world use case for `<script>` tags)

- **Structured Data & JSON-LD Test Suite** (`tests/StructuredDataTest.php`)
  - 11 tests covering real-world Schema.org JSON-LD use cases
  - **Breadcrumb tests** including exact Sportano.pl example with Polish characters
  - **Product Schema** with single and multiple offers
  - **Organization Schema** with contact points
  - **Article Schema** with author and publication data
  - **E-commerce Product List** (ItemList)
  - Tests for arrays of objects with special keys (`@type`, `@id`, `@context`)
  - Nested loops in JSON-LD structures
  - Unicode character handling and proper JSON escaping

- **Render Mode Test Suite** (`tests/RenderModeTest.php`)
  - 16 tests for render mode functionality
  - Debug mode pretty-print validation
  - Compact mode single-line validation
  - Fluent interface chaining tests
  - Mode switching tests

#### Documentation & Examples

- **Comprehensive Examples Directory** (`examples/`)
  - **11 standalone executable examples** covering all DJson features
  - `01-basic-variables.php` - Variable interpolation, dot notation, type preservation
  - `02-loops.php` - For loops, nested loops, arrays of objects
  - `03-conditionals.php` - If/else, unless, exists, comparison operators
  - `04-functions.php` - 25+ built-in functions, chaining, string/math/date operations
  - `05-calculations-set.php` - Arithmetic, @djson set, tax/discount calculations
  - `06-match-switch.php` - Pattern matching for order status, payments, roles
  - `07-object-support.php` - PHP objects, getters, nested objects
  - `08-jsonld-breadcrumbs.php` - Schema.org breadcrumbs, SEO, Unicode support
  - `09-jsonld-product.php` - Product schema, reviews, variants, availability
  - `10-complex-real-world.php` - Complete e-commerce order with all features
  - `11-custom-functions.php` - Register and use custom functions
  - `examples/README.md` - Learning path, use cases, feature matrix

- **Roadmap Document** (`ROADMAP.md`)
  - Future feature considerations (template comments, null coalescing, template registry)
  - Priority ranking (high/medium/low)
  - Contributing guidelines
  - Decision criteria for new features

### Technical Details

**Render Mode Usage:**
```php
// Debug mode - pretty printed JSON
$djson = new DJson(DJson::RENDER_MODE_DEBUG);
$json = $djson->processToJson($template, $data);
// Output: Multi-line formatted JSON with indentation

// Compact mode - single line (default)
$djson = new DJson(DJson::RENDER_MODE_COMPACT);
$json = $djson->processToJson($template, $data);
// Output: {"@context":"https://schema.org/",...}

// Fluent interface
$json = (new DJson())
    ->setRenderMode(DJson::RENDER_MODE_DEBUG)
    ->processToJson($template, $data);
```

**Real-World JSON-LD Example:**
```php
// JSON string template (as used in <script type="application/ld+json">)
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

$data = ['breadcrumbs' => [
    ['position' => 1, 'url' => 'https://example.com/', 'name' => 'Home'],
    ['position' => 2, 'url' => 'https://example.com/products', 'name' => 'Products']
]];

$result = $djson->process($jsonTemplate, $data);
// JSON string → Array → Process → Array → JSON string
// Guarantees valid JSON structure with proper nesting
```

### Files Changed
- `src/DJson.php` - Added render mode constants and methods
- `tests/RenderModeTest.php` - Render mode test coverage (+277 lines)
- `tests/JsonStringAndScalarArrayTest.php` - JSON string and scalar array tests (+377 lines)
- `tests/StructuredDataTest.php` - Real-world JSON-LD tests (+465 lines)
- `examples/01-basic-variables.php` - Basic variables example (+84 lines)
- `examples/02-loops.php` - Loops example (+96 lines)
- `examples/03-conditionals.php` - Conditionals example (+117 lines)
- `examples/04-functions.php` - Functions example (+130 lines)
- `examples/05-calculations-set.php` - Calculations example (+143 lines)
- `examples/06-match-switch.php` - Match/switch example (+143 lines)
- `examples/07-object-support.php` - Object support example (+158 lines)
- `examples/08-jsonld-breadcrumbs.php` - JSON-LD breadcrumbs example (+122 lines)
- `examples/09-jsonld-product.php` - JSON-LD product example (+197 lines)
- `examples/10-complex-real-world.php` - Complex real-world example (+207 lines)
- `examples/11-custom-functions.php` - Custom functions example (+377 lines)
- `examples/README.md` - Examples documentation (+214 lines)
- `ROADMAP.md` - Future roadmap (+330 lines)

### Test Statistics
- **170 total tests** (was 125 in v1.1.0)
- **554 total assertions** (was 424 in v1.1.0)
- **+45 new tests** covering render modes, JSON strings, and JSON-LD
- **+130 new assertions**

### Backward Compatibility
- ✅ Fully backward compatible - default mode is COMPACT (same behavior as before)
- ✅ No breaking changes to existing API
- ✅ Optional render mode parameter in constructor
- ✅ Existing `processToJson()` calls work unchanged

---

## [1.1.0] - 2025-11-16

### Added

#### Object Support
- **Full PHP Object Support**: DJson now supports PHP objects in addition to arrays
  - Automatically works with getter methods (`getName()`, `getPrice()`)
  - Supports boolean getter methods (`isActive()`, `isEnabled()`)
  - Supports `has` methods (`hasPermission()`, `hasAccess()`)
  - Falls back to public properties when getter methods don't exist
  - Works with nested objects using dot notation (e.g., `{{user.profile.address.city}}`)

- **Enhanced getValue() Method** (`src/DJson.php`)
  - Added object property access via new `getObjectProperty()` private method
  - Intelligently detects and handles objects vs arrays
  - Maintains backward compatibility with array-based data

- **Enhanced FunctionProcessor** (`src/FunctionProcessor.php`)
  - Updated function parameter parsing to support quoted strings with spaces
  - Improved parameter handling for complex function arguments
  - Added object support to context value resolution
  - Better handling of escaped quotes in function parameters

#### Testing
- **Comprehensive Object Support Test Suite** (`tests/ObjectSupportTest.php`)
  - 531 lines of tests covering all object access patterns
  - Tests for public properties, getter methods, `is*()` methods, and `has*()` methods
  - Nested object access tests
  - Object arrays and loops with objects
  - Conditionals with object properties
  - Functions applied to object properties
  - Mixed array and object data structures

### Technical Details

**Object Property Resolution Order:**
1. Try getter method: `getName()` for property `name`
2. Try `is` method: `isActive()` for property `active`
3. Try `has` method: `hasPermission()` for property `permission`
4. Try direct property access for public properties
5. Return `null` if property not found

**Example Usage:**
```php
class Product {
    public string $name;
    private float $price;

    public function getPrice(): float {
        return $this->price;
    }
}

$product = new Product('Laptop', 999.99);

$template = '{
  "name": "{{product.name}}",
  "price": "{{product.price}}"
}';

$result = $djson->process($template, ['product' => $product]);
// Works seamlessly with object properties and getters
```

### Files Changed
- `src/DJson.php` - Added object property access support (+42 lines)
- `src/FunctionProcessor.php` - Enhanced parameter parsing and object support (+106 lines)
- `tests/ObjectSupportTest.php` - Comprehensive test coverage (+531 lines)

### Backward Compatibility
- ✅ Fully backward compatible with array-based data
- ✅ No breaking changes to existing API
- ✅ Works transparently with existing templates

---

## [1.0.0] - 2025-11-13

### Added - Initial Release

#### Core Features
- **Dynamic JSON Templating**: Template-based JSON generation with directives and variables
- **Variable Interpolation**: `{{variable}}` syntax with dot notation support
- **Directives**:
  - `@djson for ... as ...` - Loop over collections
  - `@djson if` - Conditional inclusion
  - `@djson unless` - Inverse conditional
  - `@djson else` - Else clause for conditionals
  - `@djson exists` - Check if variable exists
  - `@djson match/switch` - Pattern matching
  - `@djson set` - Computed values and expressions

#### Functions
- **25+ Built-in Functions**:
  - String: `upper`, `lower`, `trim`, `slug`, `substr`, `ucfirst`, `ucwords`, `length`
  - Array: `join`, `count`, `implode`, `first`, `last`
  - Math: `round`, `ceil`, `floor`, `abs`, `number_format`
  - Date: `date`, `now`
  - Logic: `default`, `ternary`
- **Function Chaining**: Pipe multiple functions together (e.g., `@djson upper|trim {{name}}`)
- **Custom Functions**: Register your own functions via `registerFunction()`

#### Operators
- **Comparison**: `==`, `!=`, `>`, `<`, `>=`, `<=`
- **Logical**: `&&`, `||`, `!`
- **Arithmetic**: `+`, `-`, `*`, `/`
- **Ternary**: `condition ? true : false`

#### Core Classes
- `DJson` - Main processor class
- `FunctionProcessor` - Function execution and management
- `DirectiveInterface` - Interface for custom directives
- Directives:
  - `ForDirective` - Loop implementation
  - `IfDirective` - Conditional implementation
  - `UnlessDirective` - Inverse conditional
  - `ElseDirective` - Else clause
  - `ExistsDirective` - Existence check
  - `MatchDirective` - Pattern matching
  - `SetDirective` - Computed values

#### Testing
- **Comprehensive Test Suite**:
  - `DJsonV2Test.php` - Core functionality tests
  - `LogicalOperatorsTest.php` - Logical operator tests
  - `MatchSwitchTest.php` - Pattern matching tests
  - `NestedConditionalsTest.php` - Nested conditional tests
  - `NestedLoopsTest.php` - Nested loop tests (4-level deep)
  - `SetDirectiveTest.php` - Computed value tests
  - `TernaryOperatorTest.php` - Ternary operator tests
  - `ValidationTest.php` - Template validation tests

#### Features
- **Type Preservation**: Maintains data types (numbers, booleans, null)
- **Nested Structures**: Unlimited nesting of loops, conditionals, and functions
- **JSON & Array Support**: Process from JSON strings or PHP arrays
- **Custom Directives**: Extensible directive system
- **Validation**: Template validation before processing
- **Error Handling**: Clear error messages for debugging

#### Documentation
- Comprehensive README with examples
- Template examples in `tests/templates/`
- Mutation testing documentation

### Requirements
- PHP >= 8.1
- No external dependencies (core library)

---

[1.4.0]: https://github.com/qoliber/djson/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/qoliber/djson/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/qoliber/djson/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/qoliber/djson/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/qoliber/djson/releases/tag/v1.0.0
