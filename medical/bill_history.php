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
    <title>Bill History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3>📜 Bill History</h3>
    <hr>

    <table class="table table-bordered">
        <tr class="table-secondary">
            <th>Bill ID</th>
            <th>Doctor ID</th>
            <th>Patient ID</th>
            <th>Amount (₹)</th>
            <th>Date</th>
        </tr>

        <?php
        $q = "SELECT * FROM bills ORDER BY id DESC";

        // Doctor sirf apne bills dekhe
        if ($_SESSION['role'] == 'doctor') {
            $did = $_SESSION['user_id'];
            $q = "SELECT * FROM bills WHERE doctor_id='$did'";
        }

        $data = mysqli_query($conn, $q);
        while ($row = mysqli_fetch_assoc($data)) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['doctor_id']}</td>
                <td>{$row['patient_id']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['created_at']}</td>
            </tr>";
        }
        ?>
    </table>

    <a href="../admin/dashboard.php">⬅ Back</a>
</div>

</body>
</html>
