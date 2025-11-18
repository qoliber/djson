<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 3: Conditionals (@djson if, unless, else, exists)
 *
 * This example demonstrates conditional logic in templates.
 * Supports if/else, unless (inverse if), and exists (check if variable is defined).
 */

$djson = new DJson();

// Simple if condition
$template = '{
    "user": "{{user.name}}",
    "@djson if user.isPremium": {
        "badge": "Premium Member",
        "discount": 20
    }
}';

$data = [
    'user' => [
        'name' => 'John Doe',
        'isPremium' => true
    ]
];

echo "=== Simple If Condition (Premium User) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// If with else
$template = '{
    "product": "{{product.name}}",
    "@djson if product.inStock": {
        "status": "Available",
        "message": "Order now!"
    },
    "@djson else": {
        "status": "Out of Stock",
        "message": "Coming soon"
    }
}';

$dataInStock = [
    'product' => ['name' => 'Laptop', 'inStock' => true]
];

$dataOutOfStock = [
    'product' => ['name' => 'Laptop', 'inStock' => false]
];

echo "=== If/Else - In Stock ===\n";
echo $djson->processToJson($template, $dataInStock, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== If/Else - Out of Stock ===\n";
echo $djson->processToJson($template, $dataOutOfStock, JSON_PRETTY_PRINT);
echo "\n\n";

// Unless (inverse if)
$template = '{
    "user": "{{user.name}}",
    "@djson unless user.isBlocked": {
        "accessGranted": true,
        "message": "Welcome!"
    }
}';

$data = [
    'user' => [
        'name' => 'John Doe',
        'isBlocked' => false
    ]
];

echo "=== Unless Condition (User Not Blocked) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Exists check
$template = '{
    "username": "{{user.username}}",
    "@djson exists user.profile": {
        "hasProfile": true,
        "bio": "{{user.profile.bio}}"
    }
}';

$dataWithProfile = [
    'user' => [
        'username' => 'johndoe',
        'profile' => ['bio' => 'Software developer']
    ]
];

$dataWithoutProfile = [
    'user' => ['username' => 'janedoe']
];

echo "=== Exists Check - With Profile ===\n";
echo $djson->processToJson($template, $dataWithProfile, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Exists Check - Without Profile ===\n";
echo $djson->processToJson($template, $dataWithoutProfile, JSON_PRETTY_PRINT);
echo "\n\n";

// Comparison operators
$template = '{
    "product": "{{product.name}}",
    "@djson if product.price > 100": {
        "category": "Premium",
        "freeShipping": true
    },
    "@djson else": {
        "category": "Standard",
        "freeShipping": false
    }
}';

$data = [
    'product' => ['name' => 'Laptop', 'price' => 999]
];

echo "=== Comparison Operators (price > 100) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Logical operators
$template = '{
    "user": "{{user.name}}",
    "@djson if user.age >= 18 && user.hasLicense": {
        "canDrive": true
    }
}';

$data = [
    'user' => [
        'name' => 'John Doe',
        'age' => 25,
        'hasLicense' => true
    ]
];

echo "=== Logical Operators (AND) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
