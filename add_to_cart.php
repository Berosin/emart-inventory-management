<?php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name       = $_POST['name'];
    $price      = (float) $_POST['price'];
    $quantity   = (int) $_POST['quantity'];

    $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $available_quantity = $product['quantity'];

        if ($available_quantity < $quantity) {
            $_SESSION['message'] = "Not enough stock available for this product!";
            header('Location: inventory_dashboard.php');
            exit();
        }

        $new_quantity = $available_quantity - $quantity;
        $update = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
        $update->bind_param("ii", $new_quantity, $product_id);
        $update->execute();
        $update->close();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'name'       => $name,
                'price'      => $price,
                'quantity'   => $quantity
            ];
        }

        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;

            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_quantity, $row['id']);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (product_id, name, price, quantity) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isdi", $product_id, $name, $price, $quantity);
            $insert->execute();
            $insert->close();
        }

        $stmt->close();
        $conn->close();

        header('Location: inventory_dashboard.php');
        exit();
    }
}
?>
