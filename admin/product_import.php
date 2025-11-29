<?php
session_start();
include "../includes/db_connect.php";

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Unauthorized Access");
}

if (isset($_POST['go'])) {
    $errors = [];
    if (is_uploaded_file($_FILES['products']['tmp_name'])) {
        $csv = array_map('str_getcsv', file($_FILES['products']['tmp_name']));
        $header = array_map('trim', array_shift($csv));
        foreach ($csv as $row) {
            $data = array_combine($header, $row);
            // Only grocery or electronics
            if (!in_array(strtolower($data['category']), ['grocery', 'electronics'])) continue;
            // Check image file exists
            $image_path = "/uploads/products/" . basename($data['image']);
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path)) {
                $errors[] = "Image not found: " . $image_path;
                continue;
            }
            // Prepare and insert
            $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, stock, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $data['product_name'], $data['category'], $data['price'], $data['stock'], $image_path);
            $stmt->execute();
        }
    }
    echo "<h3>Import finished!</h3>";
    if ($errors) echo "<pre style='color:red;'>" . implode("\n", $errors) . "</pre>";
    else echo "All products uploaded successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Import Products</title>
</head>
<body>
    <h2>Import Products CSV</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="products" accept=".csv" required>
        <button type="submit" name="go">Import</button>
    </form>
    <p>Images must be placed in <b>/uploads/products/</b> and filenames in CSV should match exactly.</p>
    <p>Only "grocery" and "electronics" categories will be imported.</p>
</body>
</html>
