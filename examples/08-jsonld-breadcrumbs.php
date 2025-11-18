<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 8: JSON-LD Breadcrumbs (Schema.org)
 *
 * This example demonstrates how to generate Schema.org JSON-LD breadcrumb
 * structured data for SEO purposes. This is commonly used in e-commerce
 * sites and content websites to show navigation hierarchy in search results.
 */

$djson = new DJson();

// Basic breadcrumb navigation
$template = '{
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
}';

$data = [
    'breadcrumbs' => [
        ['position' => 1, 'url' => 'https://example.com/', 'name' => 'Home'],
        ['position' => 2, 'url' => 'https://example.com/electronics', 'name' => 'Electronics'],
        ['position' => 3, 'url' => 'https://example.com/electronics/laptops', 'name' => 'Laptops']
    ]
];

echo "=== Basic Breadcrumb Navigation ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// E-commerce category breadcrumbs
$data = [
    'breadcrumbs' => [
        ['position' => 1, 'url' => 'https://shop.example.com/', 'name' => 'Shop'],
        ['position' => 2, 'url' => 'https://shop.example.com/men', 'name' => 'Men'],
        ['position' => 3, 'url' => 'https://shop.example.com/men/clothing', 'name' => 'Clothing'],
        ['position' => 4, 'url' => 'https://shop.example.com/men/clothing/jackets', 'name' => 'Jackets']
    ]
];

echo "=== E-commerce Category Breadcrumbs ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Blog post breadcrumbs
$data = [
    'breadcrumbs' => [
        ['position' => 1, 'url' => 'https://blog.example.com/', 'name' => 'Blog'],
        ['position' => 2, 'url' => 'https://blog.example.com/technology', 'name' => 'Technology'],
        ['position' => 3, 'url' => 'https://blog.example.com/technology/php', 'name' => 'PHP'],
        ['position' => 4, 'url' => 'https://blog.example.com/technology/php/djson-tutorial', 'name' => 'DJson Tutorial']
    ]
];

echo "=== Blog Post Breadcrumbs ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Real-world example with Polish characters (like Sportano.pl)
$data = [
    'breadcrumbs' => [
        ['position' => 1, 'url' => 'https://sportano.pl/', 'name' => 'Sportano'],
        ['position' => 2, 'url' => 'https://sportano.pl/mezczyzna', 'name' => 'Mężczyzna'],
        ['position' => 3, 'url' => 'https://sportano.pl/mezczyzna/odziez-meska', 'name' => 'Odzież męska'],
        ['position' => 4, 'url' => 'https://sportano.pl/mezczyzna/odziez-meska/kurtki', 'name' => 'Kurtki']
    ]
];

echo "=== Breadcrumbs with Unicode Characters (Polish) ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// How to use in HTML
echo "=== HTML Usage Example ===\n";
echo "<!-- Add this to your <head> or before </body> -->\n";
echo "<script type=\"application/ld+json\">\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n</script>\n";
echo "\n";

echo "=== Compact Mode for Production ===\n";
$compactDjson = new DJson(DJson::RENDER_MODE_COMPACT);
echo "<script type=\"application/ld+json\">";
echo $compactDjson->processToJson($template, $data);
echo "</script>\n";
