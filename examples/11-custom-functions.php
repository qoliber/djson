<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 11: Custom Functions
 *
 * This example demonstrates how to register and use custom functions
 * in DJson templates. Custom functions allow you to extend DJson with
 * your own business logic and transformations.
 */

$djson = new DJson();

// ============================================================================
// Example 1: Simple Custom Function - Currency Formatter
// ============================================================================

$djson->registerFunction('currency', function ($value, $currency = 'USD') {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'PLN' => 'zł'
    ];

    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . number_format((float)$value, 2);
});

$template = '{
    "product": "{{product.name}}",
    "price": "@djson currency {{product.price}} USD",
    "priceEUR": "@djson currency {{product.price}} EUR",
    "pricePLN": "@djson currency {{product.pricePLN}} PLN"
}';

$data = [
    'product' => [
        'name' => 'Laptop',
        'price' => 999.99,
        'pricePLN' => 4299.99
    ]
];

echo "=== Custom Currency Function ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 2: Text Truncation with Ellipsis
// ============================================================================

$djson->registerFunction('truncate', function ($text, $length = 50, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
});

$template = '{
    "title": "{{article.title}}",
    "fullDescription": "{{article.description}}",
    "excerpt": "@djson truncate {{article.description}} 50 ...",
    "shortExcerpt": "@djson truncate {{article.description}} 30"
}';

$data = [
    'article' => [
        'title' => 'Getting Started with DJson',
        'description' => 'DJson is a powerful PHP library for dynamic JSON templating. It provides a simple yet powerful syntax for creating dynamic JSON structures with loops, conditionals, and functions.'
    ]
];

echo "=== Custom Truncate Function ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 3: URL Slug Generator
// ============================================================================

$djson->registerFunction('makeSlug', function ($text) {
    // Convert to lowercase
    $slug = strtolower($text);
    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');
    return $slug;
});

$template = '{
    "posts": {
        "@djson for posts as post": {
            "title": "{{post.title}}",
            "slug": "@djson makeSlug {{post.title}}",
            "url": "/blog/@djson makeSlug {{post.title}}"
        }
    }
}';

$data = [
    'posts' => [
        ['title' => 'How to Use DJson Library'],
        ['title' => 'PHP 8.1 Best Practices!'],
        ['title' => 'JSON-LD for SEO: Complete Guide']
    ]
];

echo "=== Custom Slug Generator ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 4: Custom Conditional Logic - VAT Calculator
// ============================================================================

$djson->registerFunction('addVAT', function ($price, $rate = 23) {
    return $price * (1 + $rate / 100);
});

$djson->registerFunction('getVAT', function ($price, $rate = 23) {
    return $price * ($rate / 100);
});

$template = '{
    "product": "{{product.name}}",
    "netPrice": "{{product.price}}",
    "vatRate": "23%",
    "vatAmount": "@djson getVAT {{product.price}} 23",
    "grossPrice": "@djson addVAT {{product.price}} 23"
}';

$data = [
    'product' => [
        'name' => 'Laptop',
        'price' => 1000
    ]
];

echo "=== Custom VAT Calculator ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 5: Array/Collection Functions
// ============================================================================

$djson->registerFunction('pluck', function ($array, $key) {
    if (!is_array($array)) {
        return [];
    }
    return array_map(function ($item) use ($key) {
        return is_array($item) ? ($item[$key] ?? null) : null;
    }, $array);
});

$djson->registerFunction('sum', function ($array) {
    return array_sum((array)$array);
});

$template = '{
    "orderItems": "{{order.items}}",
    "productNames": "@djson pluck {{order.items}} name",
    "quantities": "@djson pluck {{order.items}} quantity"
}';

$data = [
    'order' => [
        'items' => [
            ['name' => 'Laptop', 'price' => 999, 'quantity' => 1],
            ['name' => 'Mouse', 'price' => 29, 'quantity' => 2],
            ['name' => 'Keyboard', 'price' => 79, 'quantity' => 1]
        ]
    ]
];

echo "=== Custom Array Functions (Pluck) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 6: Conditional Text Transform
// ============================================================================

$djson->registerFunction('pluralize', function ($count, $singular, $plural = null) {
    $count = (int)$count;
    if ($count === 1) {
        return "$count $singular";
    }
    $plural = $plural ?? $singular . 's';
    return "$count $plural";
});

$template = '{
    "items": {
        "@djson for cart.items as item": {
            "product": "{{item.name}}",
            "quantity": "@djson pluralize {{item.quantity}} item items"
        }
    }
}';

$data = [
    'cart' => [
        'items' => [
            ['name' => 'Laptop', 'quantity' => 1],
            ['name' => 'Mouse', 'quantity' => 3],
            ['name' => 'Keyboard', 'quantity' => 2]
        ]
    ]
];

echo "=== Custom Pluralize Function ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 7: Gravatar URL Generator
// ============================================================================

$djson->registerFunction('gravatar', function ($email, $size = 80) {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/$hash?s=$size";
});

$template = '{
    "users": {
        "@djson for users as user": {
            "name": "{{user.name}}",
            "email": "{{user.email}}",
            "avatar": "@djson gravatar {{user.email}} 200",
            "avatarSmall": "@djson gravatar {{user.email}} 50"
        }
    }
}';

$data = [
    'users' => [
        ['name' => 'John Doe', 'email' => 'john@example.com'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com']
    ]
];

echo "=== Custom Gravatar Function ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 8: Combining Custom Functions with Built-in Functions
// ============================================================================

$djson->registerFunction('highlight', function ($text, $keyword) {
    return str_replace($keyword, "**$keyword**", $text);
});

$djson->registerFunction('reverse', function ($text) {
    return strrev((string)$text);
});

$template = '{
    "text": "{{article.text}}",
    "highlighted": "@djson highlight {{article.text}} DJson",
    "upperText": "@djson upper {{article.text}}",
    "reversedText": "@djson reverse|upper {{article.text}}",
    "trimmedUpper": "@djson trim|upper {{article.spaced}}"
}';

$data = [
    'article' => [
        'text' => 'Learn how to use DJson for dynamic JSON generation',
        'spaced' => '  hello world  '
    ]
];

echo "=== Combining Custom with Built-in Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 9: Date/Time Custom Functions
// ============================================================================

$djson->registerFunction('timeAgo', function ($timestamp) {
    $diff = time() - (int)$timestamp;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';

    return date('Y-m-d', (int)$timestamp);
});

$djson->registerFunction('formatDate', function ($timestamp, $format = 'short') {
    $formats = [
        'short' => 'M j, Y',
        'long' => 'F j, Y, g:i a',
        'iso' => 'c'
    ];

    return date($formats[$format] ?? $format, (int)$timestamp);
});

$template = '{
    "posts": {
        "@djson for posts as post": {
            "title": "{{post.title}}",
            "createdAt": "{{post.created}}",
            "timeAgo": "@djson timeAgo {{post.created}}",
            "formattedShort": "@djson formatDate {{post.created}} short",
            "formattedLong": "@djson formatDate {{post.created}} long"
        }
    }
}';

$now = time();
$data = [
    'posts' => [
        ['title' => 'Recent Post', 'created' => $now - 300],      // 5 min ago
        ['title' => 'Today Post', 'created' => $now - 7200],      // 2 hours ago
        ['title' => 'Yesterday Post', 'created' => $now - 86400], // 1 day ago
    ]
];

echo "=== Custom Date/Time Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================================================
// Example 10: Business Logic - Discount Calculator
// ============================================================================

$djson->registerFunction('applyDiscount', function ($price, $discountPercent, $minPrice = 0) {
    $discounted = $price * (1 - $discountPercent / 100);
    return max($discounted, $minPrice);
});

$djson->registerFunction('membershipDiscount', function ($price, $membershipLevel) {
    $discounts = [
        'bronze' => 5,
        'silver' => 10,
        'gold' => 15,
        'platinum' => 20
    ];

    $discount = $discounts[$membershipLevel] ?? 0;
    return $price * (1 - $discount / 100);
});

$template = '{
    "products": {
        "@djson for products as product": {
            "name": "{{product.name}}",
            "originalPrice": "{{product.price}}",
            "salePrice": "@djson applyDiscount {{product.price}} 20 10",
            "memberPriceGold": "@djson membershipDiscount {{product.price}} gold",
            "memberPricePlatinum": "@djson membershipDiscount {{product.price}} platinum"
        }
    }
}';

$data = [
    'products' => [
        ['name' => 'Laptop', 'price' => 999],
        ['name' => 'Mouse', 'price' => 29],
        ['name' => 'Monitor', 'price' => 299]
    ]
];

echo "=== Custom Business Logic Functions ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
