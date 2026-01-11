<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id = $id");
    header("Location: inventory_dashboard.php");
    exit();
}

$supplierList = $conn->query("SELECT * FROM suppliers");

$search = $_GET['search'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$sort = $_GET['sort'] ?? 'product_id';

$where = "WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $where .= " AND (p.name LIKE '%$search%' OR p.category LIKE '%$search%')";
}

if ($supplier_id) {
    $where .= " AND p.supplier_id = $supplier_id";
}

$allowed_sorts = ['name', 'price', 'quantity', 'product_id'];
$sort = in_array($sort, $allowed_sorts) ? $sort : 'product_id';

$sql = "SELECT p.*, s.name AS supplier_name 
        FROM products p 
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        $where
        ORDER BY p.category, $sort ASC";

$result = $conn->query($sql);

$groupedProducts = [];
while ($row = $result->fetch_assoc()) {
    $category = $row['category'] ?: 'Uncategorized';
    $groupedProducts[$category][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            width: 95%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #222;
        }

        .welcome-msg {
            text-align: center;
            font-weight: bold;
            color: #444;
            margin-bottom: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 25px;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .search-form input,
        .search-form select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .search-form button {
            padding: 10px 18px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .add-btn,
        .logout-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
            color: white;
        }

        .add-btn {
            background-color: #28a745;
        }

        .add-btn:hover {
            background-color: #218838;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .category-section {
            margin-bottom: 40px;
        }

        .category-section h3 {
            background-color: #f2f2f2;
            padding: 12px;
            margin: 0;
            font-size: 20px;
            border-radius: 8px 8px 0 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: #28a745;
            color: white;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .action-btn {
            padding: 6px 12px;
            margin: 3px 4px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
        }

        .edit {
            background-color: #007BFF;
        }

        .delete {
            background-color: #dc3545;
        }

        .cart-btn {
            background-color: orange;
        }

        .view-cart-btn {
            background-color: #ffc107;
            color: black;
        }

        .cart-form {
            display: inline-block;
        }

        .cart-form input[type="number"] {
            width: 50px;
            padding: 3px;
            margin-right: 4px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>📦 EMart Inventory Dashboard</h2>
    <div class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?> 👋</div>

    <div class="top-bar">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" placeholder="Search name/category..." value="<?= htmlspecialchars($search) ?>">
            <select name="supplier_id">
                <option value="">All Suppliers</option>
                <?php while ($s = $supplierList->fetch_assoc()) { ?>
                    <option value="<?= $s['supplier_id'] ?>" <?= ($supplier_id == $s['supplier_id']) ? 'selected' : '' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php } ?>
            </select>
            <select name="sort">
                <option value="product_id" <?= $sort == 'product_id' ? 'selected' : '' ?>>Default</option>
                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name</option>
                <option value="price" <?= $sort == 'price' ? 'selected' : '' ?>>Price</option>
                <option value="quantity" <?= $sort == 'quantity' ? 'selected' : '' ?>>Quantity</option>
            </select>
            <button type="submit">🔍 Filter</button>
        </form>

        <div class="btn-group">
            <a href="add_product.php" class="add-btn">➕ Add Product</a>
            <a href="view_cart.php" class="add-btn view-cart-btn">🛒 View Cart</a>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

    <?php foreach ($groupedProducts as $category => $products) { ?>
        <div class="category-section">
            <h3>📁 <?= htmlspecialchars($category) ?></h3>
            <table>
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Price (₹)</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($products as $row) { ?>
                    <tr>
                        <td><?= $row['product_id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td><?= $row['supplier_name'] ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="action-btn edit">Edit</a>
                            <a href="inventory_dashboard.php?delete=<?= $row['product_id'] ?>" onclick="return confirm('Delete this product?');" class="action-btn delete">Delete</a>

                            <form method="POST" action="add_to_cart.php" class="cart-form">
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                                <input type="hidden" name="price" value="<?= $row['price'] ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $row['quantity'] ?>" required>
                                <button type="submit" class="action-btn cart-btn">🛒</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</div>

</body>
</html>
