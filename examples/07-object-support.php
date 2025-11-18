<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 7: Object Support
 *
 * This example demonstrates how DJson works with PHP objects.
 * Supports getter methods, is/has methods, and public properties.
 * Objects can be nested and used in loops just like arrays.
 */

// Example classes
class Product
{
    public string $name;
    private float $price;
    private bool $inStock;

    public function __construct(string $name, float $price, bool $inStock = true)
    {
        $this->name = $name;
        $this->price = $price;
        $this->inStock = $inStock;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }
}

class Address
{
    public function __construct(
        private string $street,
        private string $city,
        private string $country
    ) {
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}

class User
{
    public string $username;
    private string $email;
    private Address $address;
    private bool $active;

    public function __construct(string $username, string $email, Address $address, bool $active = true)
    {
        $this->username = $username;
        $this->email = $email;
        $this->address = $address;
        $this->active = $active;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}

$djson = new DJson();

// Simple object with getter
$template = '{
    "name": "{{product.name}}",
    "price": "{{product.price}}",
    "inStock": "{{product.inStock}}"
}';

$product = new Product('Laptop', 999.99, true);

echo "=== Object with Getters and Public Properties ===\n";
echo $djson->processToJson($template, ['product' => $product], JSON_PRETTY_PRINT);
echo "\n\n";

// Nested objects
$template = '{
    "username": "{{user.username}}",
    "email": "{{user.email}}",
    "active": "{{user.active}}",
    "address": {
        "street": "{{user.address.street}}",
        "city": "{{user.address.city}}",
        "country": "{{user.address.country}}"
    }
}';

$address = new Address('Marszałkowska 1', 'Warsaw', 'Poland');
$user = new User('johndoe', 'john@example.com', $address, true);

echo "=== Nested Objects ===\n";
echo $djson->processToJson($template, ['user' => $user], JSON_PRETTY_PRINT);
echo "\n\n";

// Array of objects
$template = '{
    "products": {
        "@djson for products as product": {
            "name": "{{product.name}}",
            "price": "{{product.price}}",
            "available": "{{product.inStock}}"
        }
    }
}';

$products = [
    new Product('Laptop', 999.99, true),
    new Product('Mouse', 29.99, true),
    new Product('Keyboard', 79.99, false)
];

echo "=== Array of Objects ===\n";
echo $djson->processToJson($template, ['products' => $products], JSON_PRETTY_PRINT);
echo "\n\n";

// Objects with conditionals
$template = '{
    "product": "{{product.name}}",
    "price": "{{product.price}}",
    "@djson if product.inStock": {
        "status": "Available",
        "action": "Add to Cart"
    },
    "@djson else": {
        "status": "Out of Stock",
        "action": "Notify Me"
    }
}';

$productInStock = new Product('Laptop', 999.99, true);
$productOutOfStock = new Product('Tablet', 499.99, false);

echo "=== Object with Conditionals - In Stock ===\n";
echo $djson->processToJson($template, ['product' => $productInStock], JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Object with Conditionals - Out of Stock ===\n";
echo $djson->processToJson($template, ['product' => $productOutOfStock], JSON_PRETTY_PRINT);
echo "\n\n";

// Objects with functions
$template = '{
    "name": "{{product.name}}",
    "nameUpper": "@djson upper {{product.name}}",
    "price": "{{product.price}}",
    "priceFormatted": "@djson number_format {{product.price}} 2"
}';

$product = new Product('laptop computer', 1299.99, true);

echo "=== Objects with Functions ===\n";
echo $djson->processToJson($template, ['product' => $product], JSON_PRETTY_PRINT);
echo "\n";
