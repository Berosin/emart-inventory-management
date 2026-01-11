<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "No product selected.";
    exit();
}

$id = $_GET['id'];

$product_sql = "SELECT * FROM products WHERE product_id = $id";
$product_result = $conn->query($product_sql);
$product = $product_result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $category = $_POST["category"];
    $quantity = $_POST["quantity"];
    $price = $_POST["price"];
    $supplier_id = $_POST["supplier_id"];
    $expiry_date = $_POST["expiry_date"];

    $update_sql = "UPDATE products SET 
                    name='$name', 
                    category='$category', 
                    quantity=$quantity, 
                    price=$price, 
                    supplier_id=$supplier_id, 
                    expiry_date='$expiry_date' 
                  WHERE product_id=$id";

    if ($conn->query($update_sql) === TRUE) {
        header("Location: inventory_dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$suppliers = $conn->query("SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>✏️ Edit Product</h2>
    <form method="POST" action="">
        <label>Product Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>">

        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" min="0" required>

        <label>Price (₹):</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>

        <label>Supplier:</label>
        <select name="supplier_id" required>
            <option value="">-- Select Supplier --</option>
            <?php while($s = $suppliers->fetch_assoc()) { ?>
                <option value="<?= $s['supplier_id'] ?>" <?= $s['supplier_id'] == $product['supplier_id'] ? 'selected' : '' ?>>
                    <?= $s['name'] ?>
                </option>
            <?php } ?>
        </select>

        <label>Expiry Date:</label>
        <input type="date" name="expiry_date" value="<?= $product['expiry_date'] ?>">

        <button type="submit">Update Product</button>
    </form>
    <a href="inventory_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>
