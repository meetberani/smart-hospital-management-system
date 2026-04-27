<?php
session_start();
include("../config/db.php");

// Ensure patient is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("location:../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'] ?? 0;

// Fetch appointments with existing doctors table
$query = "SELECT a.id, a.appointment_date, a.status, u.name AS doctor_name, d.specialization
          FROM appointments a
          JOIN users u ON a.doctor_id = u.id
          LEFT JOIN doctors d ON u.id = d.user_id
          WHERE a.patient_id='$patient_id'
          ORDER BY a.appointment_date DESC";

$result = mysqli_query($conn, $query);

if(!$result){
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>📋 My Appointments</h3>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['specialization'] ?? "General"); ?></td>
                    <td><?php echo $row['appointment_date']; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">You have no appointments yet.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Back</a>
</div>

</body>
</html>
