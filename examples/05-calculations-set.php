<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 5: Calculations and @djson set Directive
 *
 * This example demonstrates how to perform calculations and create
 * computed values using the @djson set directive. Supports arithmetic
 * operations, string concatenation, and complex expressions.
 */

$djson = new DJson();

// Basic arithmetic
$template = '{
    "product": "{{product.name}}",
    "price": "{{product.price}}",
    "quantity": "{{product.quantity}}",
    "@djson set total = product.price * product.quantity": {
        "total": "{{total}}"
    }
}';

$data = [
    'product' => [
        'name' => 'Laptop',
        'price' => 999.99,
        'quantity' => 3
    ]
];

echo "=== Basic Arithmetic (Multiplication) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Multiple calculations
$template = '{
    "subtotal": "{{order.subtotal}}",
    "taxRate": "{{order.taxRate}}",
    "@djson set tax = order.subtotal * order.taxRate": {
        "tax": "{{tax}}"
    },
    "@djson set total = order.subtotal + tax": {
        "total": "{{total}}"
    }
}';

$data = [
    'order' => [
        'subtotal' => 100,
        'taxRate' => 0.23
    ]
];

echo "=== Multiple Calculations (Tax) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Discount calculation
$template = '{
    "product": "{{product.name}}",
    "originalPrice": "{{product.price}}",
    "discountPercent": "{{product.discount}}",
    "@djson set discountAmount = product.price * (product.discount / 100)": {
        "discountAmount": "{{discountAmount}}"
    },
    "@djson set finalPrice = product.price - discountAmount": {
        "finalPrice": "{{finalPrice}}",
        "savings": "{{discountAmount}}"
    }
}';

$data = [
    'product' => [
        'name' => 'Smartphone',
        'price' => 699,
        'discount' => 15
    ]
];

echo "=== Discount Calculation ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// String concatenation
$template = '{
    "firstName": "{{user.firstName}}",
    "lastName": "{{user.lastName}}",
    "@djson set fullName = user.firstName + \' \' + user.lastName": {
        "fullName": "{{fullName}}"
    }
}';

$data = [
    'user' => [
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]
];

echo "=== String Concatenation ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Using set in loops
$template = '{
    "items": {
        "@djson for cart.items as item": {
            "product": "{{item.name}}",
            "price": "{{item.price}}",
            "quantity": "{{item.quantity}}",
            "@djson set itemTotal = item.price * item.quantity": {
                "itemTotal": "{{itemTotal}}"
            }
        }
    }
}';

$data = [
    'cart' => [
        'items' => [
            ['name' => 'Laptop', 'price' => 999, 'quantity' => 1],
            ['name' => 'Mouse', 'price' => 29, 'quantity' => 2],
            ['name' => 'Keyboard', 'price' => 79, 'quantity' => 1]
        ]
    ]
];

echo "=== Set in Loops (Cart Items) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Complex expressions
$template = '{
    "product": "{{product.name}}",
    "price": "{{product.price}}",
    "quantity": "{{product.quantity}}",
    "taxRate": "{{taxRate}}",
    "shippingFee": "{{shippingFee}}",
    "@djson set subtotal = product.price * product.quantity": {
        "subtotal": "{{subtotal}}"
    },
    "@djson set tax = subtotal * taxRate": {
        "tax": "{{tax}}"
    },
    "@djson set grandTotal = subtotal + tax + shippingFee": {
        "grandTotal": "{{grandTotal}}"
    }
}';

$data = [
    'product' => [
        'name' => 'Laptop',
        'price' => 999,
        'quantity' => 2
    ],
    'taxRate' => 0.23,
    'shippingFee' => 15
];

echo "=== Complex Expressions (Order Total) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
