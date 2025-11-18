<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 9: JSON-LD Product Schema (Schema.org)
 *
 * This example demonstrates how to generate Schema.org JSON-LD product
 * structured data for e-commerce. Helps search engines understand product
 * information, pricing, reviews, and availability.
 */

$djson = new DJson();

// Basic product
$template = '{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{product.name}}",
    "description": "{{product.description}}",
    "sku": "{{product.sku}}",
    "image": "{{product.image}}",
    "brand": {
        "@type": "Brand",
        "name": "{{product.brand}}"
    },
    "offers": {
        "@type": "Offer",
        "price": "{{product.price}}",
        "priceCurrency": "{{product.currency}}",
        "availability": "https://schema.org/InStock",
        "url": "{{product.url}}"
    }
}';

$data = [
    'product' => [
        'name' => 'Apple MacBook Pro 16"',
        'description' => 'High-performance laptop with M3 chip',
        'sku' => 'MBP-16-M3-512',
        'image' => 'https://example.com/images/macbook-pro-16.jpg',
        'brand' => 'Apple',
        'price' => 2499.00,
        'currency' => 'USD',
        'url' => 'https://example.com/products/macbook-pro-16'
    ]
];

echo "=== Basic Product Schema ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Product with reviews and rating
$template = '{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{product.name}}",
    "description": "{{product.description}}",
    "image": "{{product.image}}",
    "brand": {
        "@type": "Brand",
        "name": "{{product.brand}}"
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{{product.rating.value}}",
        "reviewCount": "{{product.rating.count}}",
        "bestRating": "5",
        "worstRating": "1"
    },
    "offers": {
        "@type": "Offer",
        "price": "{{product.price}}",
        "priceCurrency": "{{product.currency}}",
        "availability": "https://schema.org/{{product.availability}}",
        "url": "{{product.url}}"
    }
}';

$data = [
    'product' => [
        'name' => 'Sony WH-1000XM5 Headphones',
        'description' => 'Industry-leading noise canceling headphones',
        'image' => 'https://example.com/images/sony-wh1000xm5.jpg',
        'brand' => 'Sony',
        'rating' => [
            'value' => 4.8,
            'count' => 1247
        ],
        'price' => 399.99,
        'currency' => 'USD',
        'availability' => 'InStock',
        'url' => 'https://example.com/products/sony-wh1000xm5'
    ]
];

echo "=== Product with Reviews and Rating ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Product with multiple offers (variants)
$template = '{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{product.name}}",
    "description": "{{product.description}}",
    "image": "{{product.image}}",
    "brand": {
        "@type": "Brand",
        "name": "{{product.brand}}"
    },
    "offers": {
        "@djson for product.offers as offer": {
            "@type": "Offer",
            "name": "{{offer.name}}",
            "price": "{{offer.price}}",
            "priceCurrency": "{{product.currency}}",
            "availability": "https://schema.org/{{offer.availability}}",
            "sku": "{{offer.sku}}"
        }
    }
}';

$data = [
    'product' => [
        'name' => 'iPhone 15 Pro',
        'description' => 'The most powerful iPhone ever',
        'image' => 'https://example.com/images/iphone-15-pro.jpg',
        'brand' => 'Apple',
        'currency' => 'USD',
        'offers' => [
            [
                'name' => '128GB - Titanium',
                'price' => 999,
                'availability' => 'InStock',
                'sku' => 'IPH15P-128-TI'
            ],
            [
                'name' => '256GB - Titanium',
                'price' => 1099,
                'availability' => 'InStock',
                'sku' => 'IPH15P-256-TI'
            ],
            [
                'name' => '512GB - Titanium',
                'price' => 1299,
                'availability' => 'PreOrder',
                'sku' => 'IPH15P-512-TI'
            ]
        ]
    ]
];

echo "=== Product with Multiple Offers (Variants) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Product with conditional availability
$template = '{
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "{{product.name}}",
    "image": "{{product.image}}",
    "offers": {
        "@type": "Offer",
        "price": "{{product.price}}",
        "priceCurrency": "USD",
        "@djson if product.inStock": {
            "availability": "https://schema.org/InStock"
        },
        "@djson else": {
            "availability": "https://schema.org/OutOfStock"
        }
    }
}';

$inStockProduct = [
    'product' => [
        'name' => 'Laptop',
        'image' => 'https://example.com/laptop.jpg',
        'price' => 999,
        'inStock' => true
    ]
];

$outOfStockProduct = [
    'product' => [
        'name' => 'Laptop',
        'image' => 'https://example.com/laptop.jpg',
        'price' => 999,
        'inStock' => false
    ]
];

echo "=== Product - In Stock ===\n";
echo $djson->processToJson($template, $inStockProduct, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Product - Out of Stock ===\n";
echo $djson->processToJson($template, $outOfStockProduct, JSON_PRETTY_PRINT);
echo "\n";
