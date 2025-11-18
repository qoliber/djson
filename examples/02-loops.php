<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 2: Loops with @djson for
 *
 * This example demonstrates how to loop over arrays and create
 * dynamic JSON structures. Supports arrays of scalars, arrays of arrays,
 * and arrays of objects.
 */

$djson = new DJson();

// Simple array loop
$template = '{
    "colors": {
        "@djson for colors as color": "{{color}}"
    }
}';

$data = [
    'colors' => ['red', 'green', 'blue']
];

echo "=== Simple Array Loop ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Array of objects
$template = '{
    "users": {
        "@djson for users as user": {
            "name": "{{user.name}}",
            "email": "{{user.email}}",
            "age": "{{user.age}}"
        }
    }
}';

$data = [
    'users' => [
        ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25],
        ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 35]
    ]
];

echo "=== Array of Objects ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Nested loops
$template = '{
    "categories": {
        "@djson for categories as category": {
            "name": "{{category.name}}",
            "products": {
                "@djson for category.products as product": {
                    "name": "{{product.name}}",
                    "price": "{{product.price}}"
                }
            }
        }
    }
}';

$data = [
    'categories' => [
        [
            'name' => 'Electronics',
            'products' => [
                ['name' => 'Laptop', 'price' => 999],
                ['name' => 'Phone', 'price' => 699]
            ]
        ],
        [
            'name' => 'Books',
            'products' => [
                ['name' => 'PHP Guide', 'price' => 39],
                ['name' => 'JavaScript Handbook', 'price' => 45]
            ]
        ]
    ]
];

echo "=== Nested Loops ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
