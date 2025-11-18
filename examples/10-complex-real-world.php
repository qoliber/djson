<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 10: Complex Real-World Scenario
 *
 * This example demonstrates a comprehensive e-commerce order receipt
 * combining multiple DJson features: loops, conditionals, calculations,
 * functions, and formatting.
 */

$djson = new DJson();

$template = '{
    "order": {
        "orderId": "{{order.id}}",
        "orderDate": "@djson date {{order.timestamp}} Y-m-d H:i:s",
        "customer": {
            "name": "@djson ucwords {{customer.firstName}} {{customer.lastName}}",
            "email": "@djson lower {{customer.email}}",
            "phone": "{{customer.phone}}",
            "isPremium": "{{customer.isPremium}}"
        },
        "shippingAddress": {
            "street": "{{shipping.street}}",
            "city": "{{shipping.city}}",
            "postalCode": "{{shipping.postalCode}}",
            "country": "{{shipping.country}}"
        },
        "items": {
            "@djson for order.items as item": {
                "product": "{{item.name}}",
                "sku": "{{item.sku}}",
                "quantity": "{{item.quantity}}",
                "unitPrice": "@djson number_format {{item.price}} 2",
                "@djson set itemTotal = item.price * item.quantity": {
                    "itemTotal": "@djson number_format {{itemTotal}} 2"
                },
                "@djson if item.discount > 0": {
                    "@djson set discountAmount = itemTotal * (item.discount / 100)": {
                        "discount": "{{item.discount}}%",
                        "discountAmount": "@djson number_format {{discountAmount}} 2",
                        "@djson set finalItemPrice = itemTotal - discountAmount": {
                            "finalPrice": "@djson number_format {{finalItemPrice}} 2"
                        }
                    }
                }
            }
        },
        "summary": {
            "@djson set subtotal = 0": {},
            "@djson for order.items as item": {
                "@djson set itemTotal = item.price * item.quantity": {
                    "@djson set subtotal = subtotal + itemTotal": {}
                }
            },
            "subtotal": "@djson number_format {{subtotal}} 2",
            "@djson set taxAmount = subtotal * order.taxRate": {
                "tax": "@djson number_format {{taxAmount}} 2",
                "taxRate": "{{order.taxRate}}"
            },
            "@djson if customer.isPremium": {
                "shippingFee": "0.00",
                "premiumDiscount": true
            },
            "@djson else": {
                "shippingFee": "@djson number_format {{order.shippingFee}} 2"
            },
            "@djson set grandTotal = subtotal + taxAmount": {
                "@djson if customer.isPremium": {
                    "total": "@djson number_format {{grandTotal}} 2"
                },
                "@djson else": {
                    "@djson set grandTotal = grandTotal + order.shippingFee": {
                        "total": "@djson number_format {{grandTotal}} 2"
                    }
                }
            }
        },
        "status": {
            "@djson match order.status": {
                "@djson switch pending": {
                    "status": "Pending Payment",
                    "message": "Please complete payment to process your order",
                    "canCancel": true
                },
                "@djson switch processing": {
                    "status": "Processing",
                    "message": "Your order is being prepared",
                    "canCancel": true
                },
                "@djson switch shipped": {
                    "status": "Shipped",
                    "message": "Your order is on the way",
                    "trackingUrl": "{{order.trackingUrl}}",
                    "canCancel": false
                },
                "@djson switch delivered": {
                    "status": "Delivered",
                    "message": "Your order has been delivered",
                    "canCancel": false,
                    "canReturn": true
                }
            }
        }
    }
}';

$data = [
    'order' => [
        'id' => 'ORD-2025-001234',
        'timestamp' => time(),
        'status' => 'shipped',
        'trackingUrl' => 'https://tracking.example.com/ORD-2025-001234',
        'taxRate' => 0.23,
        'shippingFee' => 15.00,
        'items' => [
            [
                'name' => 'MacBook Pro 16"',
                'sku' => 'MBP-16-M3-512',
                'price' => 2499.00,
                'quantity' => 1,
                'discount' => 10
            ],
            [
                'name' => 'USB-C Cable',
                'sku' => 'CABLE-USBC-2M',
                'price' => 19.99,
                'quantity' => 2,
                'discount' => 0
            ],
            [
                'name' => 'Laptop Sleeve',
                'sku' => 'SLEEVE-16',
                'price' => 39.99,
                'quantity' => 1,
                'discount' => 15
            ]
        ]
    ],
    'customer' => [
        'firstName' => 'john',
        'lastName' => 'doe',
        'email' => 'JOHN.DOE@EXAMPLE.COM',
        'phone' => '+1-555-0100',
        'isPremium' => true
    ],
    'shipping' => [
        'street' => 'Marszałkowska 1',
        'city' => 'Warsaw',
        'postalCode' => '00-624',
        'country' => 'Poland'
    ]
];

echo "=== Complex E-commerce Order Receipt ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n\n";

// Same order for non-premium customer
$data['customer']['isPremium'] = false;
$data['order']['status'] = 'processing';

echo "=== Same Order - Non-Premium Customer ===\n";
echo $djson->processToJson($template, $data, JSON_PRETTY_PRINT);
echo "\n";
