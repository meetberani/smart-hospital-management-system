<?php
session_start();
include("../config/db.php");

// Admin security
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit;
}

// Fetch monthly summary
$query = "
SELECT 
    DATE_FORMAT(bill_date, '%Y-%m') AS month,
    COUNT(DISTINCT patient_id) AS total_patients,
    COUNT(*) AS total_bills,
    SUM(total_amount) AS total_amount
FROM bills
GROUP BY month
ORDER BY month DESC
";

$result = mysqli_query($conn, $query);
if (!$result) die("Query Failed: " . mysqli_error($conn));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>📊 Monthly Reports</h3>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Patients</th>
                <th>Total Bills</th>
                <th>Total Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['month'] ?></td>
                <td><?= $row['total_patients'] ?></td>
                <td><?= $row['total_bills'] ?></td>
                <td><?= $row['total_amount'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No monthly data available.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
