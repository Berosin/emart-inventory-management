<?php
session_start();
require_once 'db_connect.php';

$cart = $_SESSION['cart'] ?? [];

if (count($cart) === 0) {
    header("Location: view_cart.php");
    exit();
}

foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $product_name = $item['name'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $total = $quantity * $price;

    $stmt = $conn->prepare("INSERT INTO orders (product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isidd", $product_id, $product_name, $quantity, $price, $total);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['cart'] = [];

$conn->query("DELETE FROM cart");

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Success</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding: 100px;
            background-color: #f7f9fc;
        }

        h1 {
            color: #28a745;
            font-size: 36px;
        }

        p {
            margin-top: 20px;
            font-size: 18px;
        }

        a {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h1>🎉 Order Placed Successfully!</h1>
    <p>Your order has been saved to the database. Thank you for shopping with us!</p>
    <a href="inventory_dashboard.php">← Back to Dashboard</a>

</body>
</html>
