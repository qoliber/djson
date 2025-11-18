<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 1: Basic Variable Interpolation
 *
 * This example demonstrates how to use basic variables in templates.
 * Variables are referenced using {{variable}} syntax and support dot notation
 * for nested data access.
 */

$djson = new DJson();

// Simple variables
$template = '{
    "name": "{{name}}",
    "email": "{{email}}",
    "age": "{{age}}",
    "active": "{{active}}"
}';

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
    'active' => true
];

echo "=== Basic Variables ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Nested data with dot notation
$template = '{
    "username": "{{user.username}}",
    "email": "{{user.email}}",
    "city": "{{user.profile.address.city}}",
    "country": "{{user.profile.address.country}}",
    "company": "{{user.profile.company}}"
}';

$data = [
    'user' => [
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'profile' => [
            'address' => [
                'city' => 'Warsaw',
                'country' => 'Poland'
            ],
            'company' => 'Qoliber'
        ]
    ]
];

echo "=== Nested Data with Dot Notation ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Type preservation
$template = '{
    "string": "{{stringValue}}",
    "integer": "{{intValue}}",
    "float": "{{floatValue}}",
    "boolean": "{{boolValue}}",
    "null": "{{nullValue}}"
}';

$data = [
    'stringValue' => 'Hello World',
    'intValue' => 42,
    'floatValue' => 3.14,
    'boolValue' => true,
    'nullValue' => null
];

echo "=== Type Preservation ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
