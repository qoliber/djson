# DJson - Dynamic JSON Templating Library

A powerful yet lightweight PHP library for creating dynamic JSON with loops, conditionals, functions, variables, and pattern matching. Think of it as a feature-rich templating engine specifically designed for JSON generation.

[![Tests](https://img.shields.io/badge/tests-229%20passed-brightgreen)](https://github.com/qoliber/djson)
[![Mutation Score](https://img.shields.io/badge/mutation%20score-100%25-brightgreen)](https://github.com/qoliber/djson)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![Security](https://img.shields.io/badge/security-mandatory-brightgreen)](https://github.com/qoliber/djson)

## 🚀 Features

- **Variable Interpolation**: Use `{{variable}}` syntax with dot notation for nested access
- **Template Functions**: 30+ built-in functions (upper, lower, date, round, join, etc.)
- **Arithmetic Operations**: Perform calculations with `@set` directive
- **Loops**: Iterate over arrays with `@djson for` directive
- **Conditionals**: Include/exclude content with `@djson if`, `@djson unless`, `@djson exists`
- **Pattern Matching**: Switch/case logic with `@djson match` and `@djson switch`
- **Variable Assignment**: Set and calculate values with `@djson set`
- **Else Statements**: Full conditional logic with `@djson else`
- **Loop Helpers**: Access `_index`, `_key`, `_first`, `_last` in loops
- **Type Preservation**: Maintains data types (numbers, booleans, null)
- **Error Handling**: Comprehensive validation with detailed error messages
- **Security First**: Mandatory protection against dangerous functions (eval, exec, shell_exec, etc.)
- **PHP 8.1+**: Modern PHP with constructor property promotion

📚 **Full Documentation**: [https://djson.dev](https://djson.dev)

## Installation

```bash
composer require qoliber/djson
```

## Quick Start

```php
use Qoliber\DJson\DJson;

$djson = new DJson();

$template = [
    'greeting' => 'Hello {{name | upper}}!',
    'users' => [
        '@djson for users as user',
        'name' => '{{user.name}}',
        'email' => '{{user.email}}'
    ]
];

$data = [
    'name' => 'world',
    'users' => [
        ['name' => 'John', 'email' => 'john@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]
];

$result = $djson->process($template, $data);
// ['greeting' => 'Hello WORLD!', 'users' => [...]]
```

## Security

DJson takes security seriously with **mandatory protection** against dangerous function registration.

### What's Protected

```php
// These are BLOCKED - throws InvalidArgumentException
$djson->registerFunction('exec', fn($cmd) => shell_exec($cmd));
$djson->registerFunction('eval', fn($code) => eval($code));
$djson->registerFunction('file_get_contents', fn($path) => file_get_contents($path));
// Error: "dangerous pattern 'exec'... use registerUnsafeFunction()"

// Trying the "unsafe" method? ALSO blocked! (troll mode activated)
$djson->registerUnsafeFunction('exec', fn($cmd) => shell_exec($cmd));
// Error: "Sorry matey, no unsafe functions allowed! Security is mandatory!"
```

### Protected Patterns

- **Code Execution**: `eval`, `assert`, `call_user_func`
- **System Commands**: `exec`, `shell_exec`, `system`, `passthru`, `popen`, `proc_open`
- **Filesystem**: `file_get_contents`, `file_put_contents`, `unlink`, `chmod`, `rename`
- **Includes**: `include`, `require`, `include_once`, `require_once`
- **Serialization**: `unserialize`
- **Reflection**: `reflection`
- **Database Functions**: `mysqli`, `mysql_`, `pg_`, `sqlite`, `pdo`, `odbc_`, `sqlsrv_`, `oci_`

### Safe Functions Work Fine

```php
// These are SAFE and work perfectly
$djson->registerFunction('currency', fn($v, $s = '$') => $s . number_format($v, 2));
$djson->registerFunction('md5hash', fn($v) => md5($v));
$djson->registerFunction('base64', fn($v) => base64_encode($v));
$djson->registerFunction('truncate', fn($v, $len = 50) => substr($v, 0, $len) . '...');
```

### Why No Bypass?

Security is **mandatory, not optional**. If you need to execute system commands or access files, do it **outside the template system** where you have proper access controls. Templates are for rendering data, not executing code.

### Deep Code Inspection

DJson uses **PHP Reflection** to inspect the actual source code of registered callables, not just function names:

```php
// BLOCKED - Even though function name is safe, the code inside is dangerous!
$djson->registerFunction('process_data', function ($input) {
    return eval($input);  // Detected via reflection!
});
// Error: "callable's source code contains a call to prohibited function pattern 'eval'"

// BLOCKED - Database access detected in callable
$djson->registerFunction('get_user', function ($id) {
    return mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
});
// Error: "callable's source code contains a call to prohibited function pattern 'mysqli'"
```

This means you **cannot bypass security** by wrapping dangerous functions inside safe-looking closures. DJson analyzes the actual implementation and blocks any dangerous code patterns.

## Core Features

### Variable Interpolation with Functions

```php
$template = [
    'title' => '{{product.name | upper}}',
    'price' => '{{product.price | number_format(2)}}',
    'date' => '{{timestamp | date("Y-m-d")}}'
];
```

### Loops with Special Variables

```php
$template = [
    'items' => [
        '@djson for products as product',
        'position' => '{{_index}}',        // Current index
        'isFirst' => '{{_first}}',         // true if first item
        'isLast' => '{{_last}}',           // true if last item
        'name' => '{{product.name}}'
    ]
];
```

### Conditionals with Else

```php
$template = [
    '@djson if user.isPremium',
    'status' => 'Premium Member',
    '@djson else',
    'status' => 'Free User'
];
```

### Pattern Matching (Switch/Case)

```php
$template = [
    '@djson match status',
    '@djson case active',
    'message' => 'User is active',
    '@djson case pending',
    'message' => 'Awaiting approval',
    '@djson default',
    'message' => 'Status unknown'
];
```

### Arithmetic Operations

```php
$template = [
    '@djson set total = price * quantity',
    '@djson set discount = total * 0.1',
    '@djson set final = total - discount',
    'finalPrice' => '{{final | number_format(2)}}'
];
```

### Conditional Expressions

```php
$template = [
    '@djson if stock > 0',
    'available' => true,
    '@djson unless stock < 10',
    'lowStock' => false
];
```

## Built-in Template Functions

### String Functions
- `upper`, `lower`, `capitalize`, `title`
- `trim`, `escape`, `slug`
- `substr`, `replace`

### Number Functions
- `number_format`, `round`, `ceil`, `floor`, `abs`

### Array Functions
- `count`, `first`, `last`, `join`, `sort`, `unique`

### Date Functions
- `date`, `strtotime`

### Utility Functions
- `default`, `coalesce`
- `json_encode`

See full function documentation at [djson.dev/functions](https://djson.dev)

## Advanced Examples

### E-commerce Product with Calculations

```php
$template = [
    'product' => '{{name}}',
    '@djson set subtotal = price * quantity',
    '@djson set tax = subtotal * 0.2',
    '@djson set total = subtotal + tax',
    'pricing' => [
        'subtotal' => '{{subtotal | number_format(2)}}',
        'tax' => '{{tax | number_format(2)}}',
        'total' => '{{total | number_format(2)}}'
    ]
];
```

### Complex Conditional Logic

```php
$template = [
    '@djson match userType',
    '@djson case admin',
    'permissions' => ['read', 'write', 'delete'],
    '@djson case editor',
    'permissions' => ['read', 'write'],
    '@djson case viewer',
    'permissions' => ['read'],
    '@djson default',
    'permissions' => []
];
```

For more examples, visit [djson.dev/examples](https://djson.dev)

## Testing

DJson has comprehensive test coverage with 100% mutation score:

```bash
composer install
./vendor/bin/phpunit
```

**Test Results:**
- ✅ 103 tests, 385 assertions
- 🏆 100% mutation score (70/70 meaningful mutations killed)
- ⚡ Rock-solid quality assurance

## API Reference

### Core Methods

```php
// Process array/JSON template, return array
$result = $djson->process($template, $data);

// Process and return JSON string
$json = $djson->processToJson($template, $data, JSON_PRETTY_PRINT);

// Load from file, return array
$result = $djson->processFile('template.json', $data);

// Load from file, return JSON string
$json = $djson->processFileToJson('template.json', $data, JSON_PRETTY_PRINT);

// Validate template (returns array of errors, empty if valid)
$errors = $djson->validate($template);
```

### Directives

| Directive | Description | Example |
|-----------|-------------|---------|
| `@djson for <array> as <var>` | Loop over array | `@djson for users as user` |
| `@djson if <condition>` | Include if truthy | `@djson if isActive` |
| `@djson unless <condition>` | Include if falsy | `@djson unless isDeleted` |
| `@djson exists <path>` | Include if path exists | `@djson exists user.email` |
| `@djson else` | Else clause | `@djson else` |
| `@djson match <value>` | Pattern matching | `@djson match status` |
| `@djson case <pattern>` | Match case | `@djson case active` |
| `@djson default` | Default case | `@djson default` |
| `@djson set <var> = <expr>` | Set variable | `@djson set total = price * qty` |
| `{{variable}}` | Variable interpolation | `{{user.name}}` |
| `{{var \| function}}` | Apply function | `{{name \| upper}}` |

### Loop Variables

- `{{_index}}` - Current loop index (0-based)
- `{{_key}}` - Current loop key
- `{{_first}}` - `true` if first iteration
- `{{_last}}` - `true` if last iteration

## Requirements

- PHP 8.1 or higher

## Documentation

Complete documentation available at **[djson.dev](https://djson.dev)**:
- [Getting Started](https://djson.dev/getting-started)
- [Template Functions](https://djson.dev/functions)
- [Advanced Examples](https://djson.dev/examples)
- [API Reference](https://djson.dev/api)

## License

MIT

## Author

**Qoliber** - [info@qoliber.com](mailto:info@qoliber.com)

---

Made with ❤️ by [Qoliber](https://qoliber.com) | [Documentation](https://djson.dev) | [GitHub](https://github.com/qoliber/djson)
