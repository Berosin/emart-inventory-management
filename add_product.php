<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $quantity = (int)$_POST["quantity"];
    $price = (float)$_POST["price"];
    $supplier_id = (int)$_POST["supplier_id"];

    $check_sql = "SELECT product_id, quantity FROM products 
                  WHERE name = ? AND category = ? AND price = ? 
                  AND supplier_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssdi", $name, $category, $price, $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;

        $update_sql = "UPDATE products SET quantity = ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_quantity, $row['product_id']);

        if ($update_stmt->execute()) {
            $message = "<p class='success'>✅ Quantity updated for existing product!</p>";
        } else {
            $message = "<p class='error'>❌ Error updating quantity: " . $conn->error . "</p>";
        }
    } else {
        $insert_sql = "INSERT INTO products (name, category, quantity, price, supplier_id)
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssidi", $name, $category, $quantity, $price, $supplier_id);

        if ($insert_stmt->execute()) {
            $message = "<p class='success'>✅ Product added successfully!</p>";
        } else {
            $message = "<p class='error'>❌ Error: " . $conn->error . "</p>";
        }
    }
}

$suppliers = $conn->query("SELECT supplier_id, name FROM suppliers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - EMart Inventory</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
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

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .success {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .error {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .back-btn {
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

        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>➕ Add New Product</h2>

    <?php if (isset($message)) echo $message; ?>

    <form method="POST" action="">
        <label>Product Name:</label>
        <input type="text" name="name" required>

        <label>Category:</label>
        <input type="text" name="category">

        <label>Quantity:</label>
        <input type="number" name="quantity" min="0" required>

        <label>Price (₹):</label>
        <input type="number" step="0.01" name="price" required>

        <label>Supplier:</label>
        <select name="supplier_id" required>
            <option value="">-- Select Supplier --</option>
            <?php while($row = $suppliers->fetch_assoc()) { ?>
                <option value="<?= $row['supplier_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
            <?php } ?>
        </select>

        <button type="submit">Add Product</button>
    </form>

    <form method="GET" action="inventory_dashboard.php">
        <button type="submit" class="back-btn">Back to Inventory Dashboard</button>
    </form>
</div>

</body>
</html>
