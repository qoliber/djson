<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 4: Functions
 *
 * This example demonstrates the 25+ built-in functions for string manipulation,
 * array operations, math, dates, and more. Functions can be chained using pipes.
 */

$djson = new DJson();

// String functions
$template = '{
    "original": "{{name}}",
    "uppercase": "@djson upper {{name}}",
    "lowercase": "@djson lower {{name}}",
    "capitalized": "@djson ucfirst {{name}}",
    "titleCase": "@djson ucwords {{description}}",
    "trimmed": "@djson trim {{text}}",
    "slug": "@djson slug {{title}}"
}';

$data = [
    'name' => 'john doe',
    'description' => 'hello world from djson',
    'text' => '  extra spaces  ',
    'title' => 'My Awesome Blog Post'
];

echo "=== String Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Math functions
$template = '{
    "price": "{{price}}",
    "rounded": "@djson round {{price}}",
    "ceil": "@djson ceil {{price}}",
    "floor": "@djson floor {{price}}",
    "absolute": "@djson abs {{negative}}",
    "formatted": "@djson number_format {{price}} 2"
}';

$data = [
    'price' => 99.567,
    'negative' => -42.5
];

echo "=== Math Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Array functions
$template = '{
    "colors": "{{colors}}",
    "count": "@djson count {{colors}}",
    "joined": "@djson join {{colors}} , ",
    "first": "@djson first {{colors}}",
    "last": "@djson last {{colors}}"
}';

$data = [
    'colors' => ['red', 'green', 'blue', 'yellow']
];

echo "=== Array Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Date functions
$template = '{
    "timestamp": "{{timestamp}}",
    "formatted": "@djson date {{timestamp}} Y-m-d",
    "fullDate": "@djson date {{timestamp}} F j, Y",
    "time": "@djson date {{timestamp}} H:i:s",
    "now": "@djson now Y-m-d H:i:s"
}';

$data = [
    'timestamp' => time()
];

echo "=== Date Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Default value function
$template = '{
    "username": "@djson default {{user.username}} Guest",
    "bio": "@djson default {{user.bio}} No bio available",
    "avatar": "@djson default {{user.avatar}} /default-avatar.png"
}';

$data = [
    'user' => [
        'username' => 'johndoe'
        // bio and avatar are missing
    ]
];

echo "=== Default Function ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Ternary operator
$template = '{
    "user": "{{user.name}}",
    "status": "@djson ternary {{user.active}} Active Inactive",
    "badge": "@djson ternary {{user.isPremium}} Premium Standard"
}';

$data = [
    'user' => [
        'name' => 'John Doe',
        'active' => true,
        'isPremium' => false
    ]
];

echo "=== Ternary Operator ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Function chaining with pipes
$template = '{
    "title": "{{title}}",
    "processed": "@djson trim|lower|ucfirst {{title}}"
}';

$data = [
    'title' => '  HELLO WORLD  '
];

echo "=== Function Chaining ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// String length and substring
$template = '{
    "text": "{{text}}",
    "length": "@djson length {{text}}",
    "excerpt": "@djson substr {{text}} 0 20"
}';

$data = [
    'text' => 'This is a long text that needs to be truncated for display purposes.'
];

echo "=== String Length and Substring ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
