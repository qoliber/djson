# DJson Roadmap

This document outlines potential future features and improvements for DJson. These are ideas based on common use cases and user feedback. Implementation priority will be determined by community needs and real-world usage patterns.

---

## Future Considerations

### 1. Template Comments
**Status:** Under Consideration
**Priority:** Low
**Use Case:** Documenting complex JSON-LD templates

Allow developers to add comments to templates that won't appear in the output:

```json
{
  "@comment": "Schema.org breadcrumb navigation for SEO",
  "@context": "https://schema.org/",
  "@type": "BreadcrumbList",
  "itemListElement": {
    "@djson for breadcrumbs as crumb": {
      "@comment": "Each breadcrumb item",
      "@type": "ListItem",
      "position": "{{crumb.position}}",
      "item": {
        "@id": "{{crumb.url}}",
        "name": "{{crumb.name}}"
      }
    }
  }
}
```

**Benefits:**
- Self-documenting templates
- Easier maintenance for complex structures
- Better collaboration between team members

**Implementation Ideas:**
- Strip `@comment` keys during processing
- Support both object key and string value formats
- Optional: Support multi-line comments

---

### 2. Null Coalescing Operator
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Cleaner default value handling

Add PHP-style null coalescing operator for simpler fallback values:

```json
{
  "price": "{{product.price ?? 0}}",
  "discount": "{{product.discount ?? 'No discount'}}",
  "name": "{{user.profile.name ?? user.username ?? 'Guest'}}"
}
```

**Benefits:**
- More concise than `@djson default` function
- Familiar syntax for PHP developers
- Chain multiple fallbacks
- Better readability for simple cases

**Current Workaround:**
```json
"price": "@djson default {{product.price}} 0"
```

---

### 3. Template Registry / Named Templates
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Reusable templates across application

Register and reuse common template patterns:

```php
// Register templates
$djson->registerTemplate('breadcrumb', '{
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
}');

$djson->registerTemplate('product', $productSchemaTemplate);
$djson->registerTemplate('organization', $organizationTemplate);

// Use templates by name
$breadcrumbJson = $djson->processTemplate('breadcrumb', $breadcrumbData);
$productJson = $djson->processTemplate('product', $productData);

// List registered templates
$templates = $djson->getRegisteredTemplates();
```

**Benefits:**
- DRY principle - define once, use everywhere
- Centralized template management
- Easy to update common patterns (like Schema.org structures)
- Version control for templates
- Perfect for repeated JSON-LD patterns

**Use Cases:**
- E-commerce sites with standard product/review schemas
- Multi-page breadcrumb navigation
- Standardized API response formats
- Microservices with shared data structures

---

### 4. Array Filter/Map Operations
**Status:** Under Consideration
**Priority:** Low
**Use Case:** Advanced data transformation

Add functional programming operations for arrays:

```json
{
  "activeUsers": "@djson filter users where user.active == true",
  "usernames": "@djson map users get username",
  "prices": "@djson map products get price",
  "topProducts": "@djson filter products where product.rating > 4 | first 10",
  "totalPrice": "@djson sum products get price"
}
```

**Potential Operations:**
- `filter` - Filter array by condition
- `map` - Extract/transform array values
- `reduce` / `sum` - Aggregate values
- `sort` - Sort by property
- `unique` - Remove duplicates
- `chunk` - Split into groups

**Benefits:**
- More powerful data manipulation
- Less PHP preprocessing needed
- Cleaner templates for complex transformations

**Current Workaround:**
Pre-process data in PHP before passing to DJson

---

### 5. Enhanced Error Messages
**Status:** Under Consideration
**Priority:** High
**Use Case:** Debugging template issues

Improve error reporting with detailed context:

**Current:**
```
Error: Invalid directive syntax
```

**Proposed:**
```
Template Error at line 5, column 12:
    "@djson for products as product": {
            ^
Error: Invalid directive syntax - expected 'for ARRAY as VARIABLE'
Did you mean: "@djson for products as product"?
```

**Features:**
- Line and column numbers for JSON string templates
- Helpful suggestions for common mistakes
- Context showing surrounding template code
- Detailed error types (syntax, missing data, type mismatch)
- Stack trace for nested errors

**Benefits:**
- Faster debugging
- Better developer experience
- Easier onboarding for new users

---

### 6. Template Validation Mode
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Catching errors before runtime

Validate templates without processing:

```php
// Validate template syntax
$issues = $djson->validate($template);

if (!empty($issues)) {
    foreach ($issues as $issue) {
        echo "{$issue['type']}: {$issue['message']} at line {$issue['line']}\n";
    }
}

// Or throw on invalid
$djson->validateOrThrow($template);

// Validate with sample data structure
$djson->validate($template, ['users' => [], 'products' => []]);
// Returns warnings for missing data references
```

**Validation Checks:**
- Syntax errors in directives
- Invalid function names
- Malformed variable references
- Missing closing braces
- Circular references
- Optional: Data structure compatibility

**Benefits:**
- Catch errors in CI/CD pipeline
- Test templates without running full processing
- Better IDE integration potential
- Safer deployments

---

### 7. Template Partials/Includes
**Status:** Under Consideration
**Priority:** Low
**Use Case:** Complex nested templates

Include one template within another:

```json
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{product.name}}",
  "offers": "@djson include 'offer-template' with product.offer",
  "review": "@djson include 'review-template' with product.reviews"
}
```

**Benefits:**
- Break large templates into manageable pieces
- Reuse common sub-structures
- Better organization for complex schemas

**Note:** Can be achieved with Template Registry (feature #3)

---

### 8. Custom Directive Registration
**Status:** Under Consideration
**Priority:** Low
**Use Case:** Domain-specific extensions

Allow users to register custom directives:

```php
$djson->registerDirective('cache', new CacheDirective());
$djson->registerDirective('translate', new TranslateDirective());
```

**Benefits:**
- Extensibility for specific use cases
- Domain-specific languages
- Integration with frameworks/libraries

**Note:** Already possible via `DirectiveInterface`, but could simplify registration

---

## Implementation Priority

Based on potential impact and user needs:

1. **High Priority**
   - Enhanced Error Messages (better DX)
   - Template Validation Mode (safety)

2. **Medium Priority**
   - Null Coalescing Operator (convenience)
   - Template Registry (reusability)

3. **Low Priority**
   - Template Comments (nice-to-have)
   - Array Filter/Map (niche use case)
   - Template Partials (can use registry)
   - Custom Directive Registration (already possible)

---

### 9. Enhanced Security - Network Function Blocking
**Status:** Under Consideration
**Priority:** High
**Use Case:** Prevent templates from making external network requests

Add network-related functions to dangerous patterns:

```php
// These would be BLOCKED
$djson->registerFunction('fetch_data', function($url) {
    return file_get_contents($url);  // Already blocked
});

$djson->registerFunction('api_call', function($url) {
    $ch = curl_init($url);  // Would be blocked
    return curl_exec($ch);
});

$djson->registerFunction('connect', function($host) {
    return fsockopen($host, 80);  // Would be blocked
});
```

**Patterns to Block:**
- `curl_exec`, `curl_init`, `curl_setopt`, `curl_multi_exec`
- `fsockopen`, `pfsockopen`
- `socket_create`, `socket_connect`, `socket_send`
- `stream_socket_client`, `stream_context_create`
- `ftp_connect`, `ftp_login`, `ftp_get`
- `ssh2_connect`, `ssh2_exec`

**Benefits:**
- Prevents Server-Side Request Forgery (SSRF) attacks
- Blocks unauthorized external API calls
- Prevents data exfiltration via network
- Complete template isolation from network layer

**Implementation:**
- Add patterns to `DANGEROUS_PATTERNS` constant
- Reflection validator already catches these inside callables
- No performance impact (pattern matching only)

---

### 10. Performance - Cache Reflection Results
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Improve performance when registering multiple functions

Cache reflection-based validation results:

```php
// Current: Reflection runs every time
$djson->registerFunction('currency', $currencyFormatter);  // Validates source
$djson->registerFunction('currency', $currencyFormatter);  // Validates AGAIN (same closure)

// Proposed: Cache by object hash
private array $validatedCallables = [];

private function validateCallableContent(string $name, callable $handler): void
{
    $hash = spl_object_hash($handler);
    if (isset($this->validatedCallables[$hash])) {
        return; // Already validated - skip reflection
    }

    // ... do reflection validation ...

    $this->validatedCallables[$hash] = true;
}
```

**Benefits:**
- Faster function registration for the same callable
- Reduced file I/O when re-registering functions
- Better performance in tests with repeated registrations
- No impact on security (same validation, just cached)

**Trade-offs:**
- Slightly more memory usage (hash map)
- Cache invalidation if callable source changes (rare in production)

---

### 11. More Built-in Functions
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Reduce need for custom function registration

Add commonly useful functions to cover more use cases out of the box:

**String Functions:**
```json
{
  "hasKeyword": "{{description | contains 'PHP'}}",
  "isEmail": "{{email | starts_with 'admin@'}}",
  "isDomain": "{{url | ends_with '.com'}}",
  "words": "{{text | split ' '}}",
  "repeated": "{{char | repeat 5}}",
  "backwards": "{{text | reverse}}"
}
```

**Math Functions:**
```json
{
  "lowest": "{{prices | min}}",
  "highest": "{{prices | max}}",
  "total": "{{prices | sum}}",
  "average": "{{prices | avg}}",
  "discount": "{{price | percentage 20}}"
}
```

**Array Functions:**
```json
{
  "properties": "{{object | keys}}",
  "data": "{{object | values}}",
  "batches": "{{items | chunk 10}}",
  "reversed": "{{items | reverse}}",
  "filtered": "{{items | filter}}"
}
```

**Type Checking Functions:**
```json
{
  "isEmpty": "{{value | is_empty}}",
  "isNull": "{{value | is_null}}",
  "isNumeric": "{{value | is_numeric}}",
  "isString": "{{value | is_string}}",
  "isArray": "{{value | is_array}}"
}
```

**Benefits:**
- Better out-of-box experience
- Less boilerplate in user code
- Covers 90% of common use cases
- Consistent API across projects

**Current Workaround:**
Register these functions manually in every project

---

### 12. Template Debugging Mode
**Status:** Under Consideration
**Priority:** Medium
**Use Case:** Understanding template processing and performance

Add a debug mode that shows processing details:

```php
$djson = new DJson(['debug' => true]);

$result = $djson->process($template, $data);

// Get debug info
$debugInfo = $djson->getDebugInfo();
/*
[
    'directives_processed' => [
        ['type' => 'for', 'path' => 'users', 'iterations' => 100, 'time_ms' => 2.5],
        ['type' => 'if', 'path' => 'users[0].isPremium', 'result' => true, 'time_ms' => 0.1],
        ['type' => 'match', 'path' => 'status', 'matched_case' => 'active', 'time_ms' => 0.2]
    ],
    'variables_resolved' => [
        'user.name' => 'John Doe',
        'user.email' => 'john@example.com',
        'product.price' => 99.99
    ],
    'functions_called' => [
        ['name' => 'upper', 'input' => 'hello', 'output' => 'HELLO', 'time_ms' => 0.05],
        ['name' => 'number_format', 'input' => 99.99, 'output' => '99.99', 'time_ms' => 0.03]
    ],
    'total_time_ms' => 12.5,
    'memory_peak_mb' => 2.1
]
*/
```

**Features:**
- Directive execution trace with timing
- Variable resolution steps
- Function call logs with inputs/outputs
- Performance metrics (time, memory)
- Nested context tracking
- Optional: Export debug log to file

**Benefits:**
- Easier debugging of complex templates
- Performance profiling and optimization
- Understanding data flow
- Better developer experience
- Useful for learning DJson

**Use Cases:**
- "Why is this template slow?"
- "Which variable is undefined?"
- "What data is being passed to this function?"
- "How many times is this loop executing?"

---

### 13. Better Error Messages with Context
**Status:** Under Consideration
**Priority:** High
**Use Case:** Faster debugging with contextual information

*Enhancement to existing item #5 - Enhanced Error Messages*

Add path-based error context for nested structures:

**Current:**
```
Error: Invalid directive syntax
```

**Proposed:**
```
Template Error at path "users[0].profile.address.city":
    "city": "{{user.address.city | upper}}"
                                    ^
Error: Unknown function 'upper_case'
Did you mean: 'upper'?

Available string functions: upper, lower, capitalize, title, trim, escape

Context:
  users[0].profile.name = "John Doe"
  users[0].profile.address.street = "123 Main St"
  users[0].profile.address.city = undefined ❌
```

**Features:**
- JSON path to error location (e.g., `users[0].profile.name`)
- Visual pointer to exact position
- Suggestions for typos/mistakes
- List of available functions/directives
- Show surrounding context data
- Distinguish between: syntax errors, missing data, type mismatches, security violations

**Benefits:**
- 10x faster debugging
- Reduced support questions
- Better onboarding for new users
- Professional error handling

---

## Implementation Priority

Based on potential impact and user needs:

1. **High Priority**
   - Enhanced Security - Network Function Blocking (security critical)
   - Better Error Messages with Context (better DX)
   - Template Validation Mode (safety)

2. **Medium Priority**
   - More Built-in Functions (reduce boilerplate)
   - Template Debugging Mode (development experience)
   - Performance - Cache Reflection Results (optimization)
   - Null Coalescing Operator (convenience)
   - Template Registry (reusability)

3. **Low Priority**
   - Template Comments (nice-to-have)
   - Array Filter/Map (niche use case)
   - Template Partials (can use registry)
   - Custom Directive Registration (already possible)

---

## Contributing Ideas

Have a feature idea? We'd love to hear from you!

1. **Open an issue** on GitHub describing your use case
2. **Share real-world examples** of how you'd use the feature
3. **Discuss implementation** approaches with the community
4. **Submit a PR** if you want to implement it yourself

---

## Decision Process

Features will be evaluated based on:

- **Real-world usage**: Is there actual demand?
- **Backward compatibility**: Will it break existing code?
- **Complexity**: Does the benefit justify the maintenance cost?
- **Scope**: Does it fit DJson's core mission (dynamic JSON templating)?
- **Alternatives**: Can it be achieved with existing features?

---

**Last Updated:** 2025-11-18
**Current Version:** 1.5.0
