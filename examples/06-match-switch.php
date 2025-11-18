<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qoliber\DJson\DJson;

/**
 * Example 6: Match/Switch Directive
 *
 * This example demonstrates pattern matching using @djson match/switch.
 * Similar to switch statements in PHP, useful for handling multiple cases.
 */

$djson = new DJson();

// Basic match
$template = '{
    "order": "{{order.id}}",
    "status": "{{order.status}}",
    "@djson match order.status": {
        "@djson switch pending": {
            "message": "Your order is being processed",
            "icon": "⏳"
        },
        "@djson switch confirmed": {
            "message": "Your order has been confirmed",
            "icon": "✓"
        },
        "@djson switch shipped": {
            "message": "Your order is on the way",
            "icon": "🚚"
        },
        "@djson switch delivered": {
            "message": "Your order has been delivered",
            "icon": "📦"
        }
    }
}';

$orderPending = ['order' => ['id' => 1001, 'status' => 'pending']];
$orderShipped = ['order' => ['id' => 1002, 'status' => 'shipped']];
$orderDelivered = ['order' => ['id' => 1003, 'status' => 'delivered']];

echo "=== Match/Switch - Pending Order ===\n";
echo $djson->processToJson($template, $orderPending, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Match/Switch - Shipped Order ===\n";
echo $djson->processToJson($template, $orderShipped, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Match/Switch - Delivered Order ===\n";
echo $djson->processToJson($template, $orderDelivered, JSON_PRETTY_PRINT);
echo "\n\n";

// Payment method
$template = '{
    "user": "{{user.name}}",
    "paymentMethod": "{{payment.method}}",
    "@djson match payment.method": {
        "@djson switch credit_card": {
            "processor": "Stripe",
            "fee": 2.9,
            "currency": "USD"
        },
        "@djson switch paypal": {
            "processor": "PayPal",
            "fee": 3.5,
            "currency": "USD"
        },
        "@djson switch bank_transfer": {
            "processor": "Direct",
            "fee": 0,
            "currency": "USD"
        },
        "@djson switch crypto": {
            "processor": "Coinbase",
            "fee": 1.5,
            "currency": "BTC"
        }
    }
}';

$creditCardPayment = [
    'user' => ['name' => 'John Doe'],
    'payment' => ['method' => 'credit_card']
];

$cryptoPayment = [
    'user' => ['name' => 'Jane Smith'],
    'payment' => ['method' => 'crypto']
];

echo "=== Payment Method - Credit Card ===\n";
echo $djson->processToJson($template, $creditCardPayment, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== Payment Method - Crypto ===\n";
echo $djson->processToJson($template, $cryptoPayment, JSON_PRETTY_PRINT);
echo "\n\n";

// User role permissions
$template = '{
    "user": "{{user.name}}",
    "role": "{{user.role}}",
    "@djson match user.role": {
        "@djson switch admin": {
            "canEdit": true,
            "canDelete": true,
            "canApprove": true,
            "canViewReports": true
        },
        "@djson switch moderator": {
            "canEdit": true,
            "canDelete": false,
            "canApprove": true,
            "canViewReports": false
        },
        "@djson switch user": {
            "canEdit": false,
            "canDelete": false,
            "canApprove": false,
            "canViewReports": false
        },
        "@djson switch guest": {
            "canEdit": false,
            "canDelete": false,
            "canApprove": false,
            "canViewReports": false,
            "limitedAccess": true
        }
    }
}';

$adminUser = ['user' => ['name' => 'Admin User', 'role' => 'admin']];
$moderatorUser = ['user' => ['name' => 'Moderator User', 'role' => 'moderator']];

echo "=== User Permissions - Admin ===\n";
echo $djson->processToJson($template, $adminUser, JSON_PRETTY_PRINT);
echo "\n\n";

echo "=== User Permissions - Moderator ===\n";
echo $djson->processToJson($template, $moderatorUser, JSON_PRETTY_PRINT);
echo "\n";
