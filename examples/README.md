# DJson Examples

This directory contains comprehensive examples demonstrating all features of the DJson library. Each example is a standalone PHP script that can be executed directly.

## Running Examples

```bash
# From the lib directory
php examples/01-basic-variables.php
php examples/02-loops.php
# ... etc
```

## Available Examples

### 01. Basic Variables (`01-basic-variables.php`)
**Topics Covered:**
- Simple variable interpolation with `{{variable}}`
- Nested data access with dot notation (`user.profile.address.city`)
- Type preservation (strings, integers, floats, booleans, null)

**Best For:** Understanding the basics of variable usage in DJson

---

### 02. Loops (`02-loops.php`)
**Topics Covered:**
- `@djson for` directive
- Looping over scalar arrays
- Looping over arrays of objects
- Loop index with `{{@index}}`
- Nested loops

**Best For:** Learning how to create dynamic arrays in JSON output

---

### 03. Conditionals (`03-conditionals.php`)
**Topics Covered:**
- `@djson if` directive
- `@djson else` directive
- `@djson unless` (inverse if)
- `@djson exists` (check if variable exists)
- Comparison operators (`>`, `<`, `>=`, `<=`, `==`, `!=`)
- Logical operators (`&&`, `||`, `!`)

**Best For:** Understanding conditional logic and control flow

---

### 04. Functions (`04-functions.php`)
**Topics Covered:**
- String functions (`upper`, `lower`, `trim`, `ucfirst`, `ucwords`, `slug`, `substr`, `length`)
- Math functions (`round`, `ceil`, `floor`, `abs`, `number_format`)
- Array functions (`count`, `join`, `first`, `last`)
- Date functions (`date`, `now`)
- Default values (`default`)
- Ternary operator
- Function chaining with pipes (`trim|lower|ucfirst`)

**Best For:** Learning data transformation and formatting

---

### 05. Calculations and Set (`05-calculations-set.php`)
**Topics Covered:**
- `@djson set` directive
- Arithmetic operations (`+`, `-`, `*`, `/`)
- Creating computed values
- Using calculations in loops
- Complex multi-step calculations (tax, discount, totals)

**Best For:** Understanding how to perform calculations within templates

---

### 06. Match/Switch (`06-match-switch.php`)
**Topics Covered:**
- `@djson match` directive
- `@djson switch` cases
- Pattern matching for multiple conditions
- Order status handling
- Payment method selection
- User role permissions

**Best For:** Learning pattern matching as an alternative to multiple if/else

---

### 07. Object Support (`07-object-support.php`)
**Topics Covered:**
- Using PHP objects in templates
- Getter methods (`getName()`, `getPrice()`)
- Boolean getters (`isActive()`, `isInStock()`)
- Public properties
- Nested objects
- Arrays of objects
- Objects with conditionals and functions

**Best For:** Understanding how DJson works with PHP objects

---

### 08. JSON-LD Breadcrumbs (`08-jsonld-breadcrumbs.php`)
**Topics Covered:**
- Schema.org structured data
- JSON-LD breadcrumb navigation
- SEO markup for search engines
- Unicode character handling (Polish: Mężczyzna, Odzież)
- Debug vs. Compact render modes
- HTML integration with `<script type="application/ld+json">`

**Best For:** Real-world SEO implementation for websites

---

### 09. JSON-LD Product (`09-jsonld-product.php`)
**Topics Covered:**
- Schema.org Product schema
- Product with offers
- Aggregate ratings and reviews
- Multiple product variants
- Conditional availability
- E-commerce structured data

**Best For:** E-commerce product page SEO

---

### 10. Complex Real-World Scenario (`10-complex-real-world.php`)
**Topics Covered:**
- Comprehensive e-commerce order receipt
- Combining loops, conditionals, calculations, and functions
- Premium vs. standard customer handling
- Tax and shipping calculations
- Discount calculations per item
- Order status with match/switch
- String formatting and date formatting

**Best For:** Seeing how all DJson features work together in a realistic scenario

---

## Learning Path

**Beginner:**
1. Start with `01-basic-variables.php` to understand variable syntax
2. Move to `02-loops.php` to learn dynamic arrays
3. Try `03-conditionals.php` for control flow

**Intermediate:**
4. Learn `04-functions.php` for data transformation
5. Master `05-calculations-set.php` for computed values
6. Understand `06-match-switch.php` for pattern matching

**Advanced:**
7. Explore `07-object-support.php` for OOP integration
8. Study `08-jsonld-breadcrumbs.php` for real-world SEO
9. Review `09-jsonld-product.php` for e-commerce
10. Analyze `10-complex-real-world.php` to see it all together

---

## Common Use Cases

### API Response Generation
See: `01-basic-variables.php`, `02-loops.php`, `03-conditionals.php`

### E-commerce Product Feeds
See: `09-jsonld-product.php`, `07-object-support.php`

### SEO Structured Data
See: `08-jsonld-breadcrumbs.php`, `09-jsonld-product.php`

### Order Processing
See: `10-complex-real-world.php`, `05-calculations-set.php`

### Dynamic Configurations
See: `06-match-switch.php`, `03-conditionals.php`

---

## Features by Example

| Feature | Examples |
|---------|----------|
| Variables `{{var}}` | All examples |
| Loops `@djson for` | 02, 07, 08, 09, 10 |
| Conditionals `@djson if/else` | 03, 07, 09, 10 |
| Functions | 04, 07, 10 |
| Calculations `@djson set` | 05, 10 |
| Match/Switch | 06, 10 |
| Objects | 07 |
| JSON-LD | 08, 09 |
| Render Modes | 08 |

---

## Tips

1. **Start Simple:** Begin with basic examples and gradually move to complex ones
2. **Experiment:** Modify the data and templates to see how changes affect output
3. **Combine Features:** The real power comes from combining multiple features
4. **Check Output:** Run examples to see JSON output and understand the structure
5. **Read Comments:** Each example has detailed comments explaining the concepts

---

## Next Steps

After reviewing these examples:

1. Check the main `README.md` for complete documentation
2. Review `ROADMAP.md` for upcoming features
3. Read `CHANGELOG.md` to see what's new
4. Explore the `tests/` directory for more edge cases
5. Build your own templates!

---

**Need Help?**
- GitHub Issues: https://github.com/qoliber/djson/issues
- Documentation: See main README.md
- Tests: Review `tests/` directory for more examples
