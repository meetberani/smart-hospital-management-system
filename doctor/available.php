<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!='doctor'){
    header("location:../login.php");
    exit;
}

$doc_id = $_SESSION['user_id'];

// ✅ FIXED (use user_id)
mysqli_query($conn,"
UPDATE doctors 
SET status='available', leave_date=NULL 
WHERE user_id='$doc_id'
");

header("location:dashboard.php");
exit;
?>