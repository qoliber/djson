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

**Last Updated:** 2025-11-17
**Current Version:** 1.2.0
