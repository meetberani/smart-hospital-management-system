<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!='doctor'){
    header("location:../login.php");
    exit;
}

$doc_id = $_SESSION['user_id'];
$today = date("Y-m-d");

// ✅ FIXED (use user_id instead of id)
mysqli_query($conn,"
UPDATE doctors 
SET status='on_leave', leave_date='$today' 
WHERE user_id='$doc_id'
");

echo "<script>
alert('Marked as On Leave for today');
window.location='dashboard.php';
</script>";
?>