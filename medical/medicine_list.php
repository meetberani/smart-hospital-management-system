<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role'])) {
    header("location:../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Medicine List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3>💊 Medicine List</h3>
    <hr>

    <table class="table table-bordered">
        <tr class="table-dark">
            <th>ID</th>
            <th>Medicine Name</th>
            <th>Price (₹)</th>
            <th>Quantity</th>
        </tr>

        <?php
        $data = mysqli_query($conn, "SELECT * FROM medicine");
        while ($row = mysqli_fetch_assoc($data)) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['price']}</td>
                <td>{$row['quantity']}</td>
            </tr>";
        }
        ?>
    </table>

    <a href="../doctor/dashboard.php">⬅ Back</a>
</div>

</body>
</html>
