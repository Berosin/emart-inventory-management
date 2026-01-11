<?php
session_start();
$cart = $_SESSION['cart'] ?? [];
$total = 0;

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $delete_id) {
            unset($cart[$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($cart);
    header('Location: view_cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #28a745; color: white; }
        .delete-btn { color: red; cursor: pointer; text-decoration: underline; }
        .btn-order {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-order:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>🛒 Your Shopping Cart</h2>

<?php if (count($cart) > 0): ?>
    <form method="POST" action="place_order.php">
        <table>
            <tr>
                <th>#ID</th>
                <th>Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cart as $item): 
                $lineTotal = $item['quantity'] * $item['price'];
                $total += $lineTotal;
            ?>
                <tr>
                    <td><?= $item['product_id'] ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₹<?= $item['price'] ?></td>
                    <td>₹<?= $lineTotal ?></td>
                    <td><a href="view_cart.php?delete_id=<?= $item['product_id'] ?>" class="delete-btn">Delete</a></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5" align="right"><strong>Total:</strong></td>
                <td><strong>₹<?= $total ?></strong></td>
            </tr>
        </table>
        <button type="submit" class="btn-order">✅ Place Order</button>
    </form>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>

<br>
<a href="inventory_dashboard.php">← Back to Dashboard</a>
</body>
</html>