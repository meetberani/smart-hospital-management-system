<?php
session_start();
include("../config/db.php");

if ($_SESSION['role'] != 'doctor') {
    header("location:../login.php");
    exit;
}

$msg = "";

if (isset($_POST['generate'])) {

    $patient_id = $_POST['patient_id'];
    $medicine_id = $_POST['medicine_id'];
    $qty = $_POST['qty'];
    $doctor_id = $_SESSION['user_id'];

    // Medicine price fetch
    $med = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM medicine WHERE id='$medicine_id'")
    );

    $amount = $med['price'] * $qty;

    // Insert bill
    mysqli_query($conn,
        "INSERT INTO bills (doctor_id, patient_id, amount)
         VALUES ('$doctor_id','$patient_id','$amount')"
    );

    // Update stock
    mysqli_query($conn,
        "UPDATE medicine SET quantity = quantity - $qty WHERE id='$medicine_id'"
    );

    $msg = "✅ Bill generated successfully (₹$amount)";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generate Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4 col-md-6">
    <h3>🧾 Generate Bill</h3>
    <hr>

    <?php if($msg!=""){ ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php } ?>

    <form method="post">

        <label>Select Patient</label>
        <select name="patient_id" class="form-control mb-2" required>
            <option value="">-- Select Patient --</option>
            <?php
            $p = mysqli_query($conn, "SELECT * FROM users WHERE role='patient'");
            while ($row = mysqli_fetch_assoc($p)) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label>Select Medicine</label>
        <select name="medicine_id" class="form-control mb-2" required>
            <option value="">-- Select Medicine --</option>
            <?php
            $m = mysqli_query($conn, "SELECT * FROM medicine WHERE quantity > 0");
            while ($row = mysqli_fetch_assoc($m)) {
                echo "<option value='{$row['id']}'>{$row['name']} (₹{$row['price']})</option>";
            }
            ?>
        </select>

        <label>Quantity</label>
        <input type="number" name="qty" class="form-control mb-3" required>

        <button name="generate" class="btn btn-primary w-100">
            Generate Bill
        </button>
    </form>

    <a href="../doctor/dashboard.php">⬅ Back</a>
</div>

</body>
</html>
