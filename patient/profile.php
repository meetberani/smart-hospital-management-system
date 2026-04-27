<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("location:../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$msg = "";

/* Update profile */
if(isset($_POST['update_profile'])){

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone'] ?? '');

    mysqli_query($conn,"
    UPDATE users 
    SET name='$name', email='$email', phone='$phone'
    WHERE id='$patient_id'
    ");

    $msg="✅ Profile Updated Successfully!";
}

/* Fetch user */
$res=mysqli_query($conn,"SELECT * FROM users WHERE id='$patient_id'");
$user=mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:linear-gradient(120deg,#e3f2fd,#f9fbff);
font-family:Segoe UI;
}

.profile-card{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 10px 25px rgba(0,0,0,.1);
}

.profile-img{
width:100px;
height:100px;
border-radius:50%;
border:4px solid #2196f3;
}
</style>

</head>

<body>

<div class="container mt-5 col-md-5">

<div class="profile-card text-center">

<img src="../assets/images/patient.jpeg" class="profile-img mb-3">

<h4><?=htmlspecialchars($user['name'])?></h4>
<p class="text-muted">Patient Account</p>

<?php if($msg!=""){ ?>
<div class="alert alert-success"><?=$msg?></div>
<?php } ?>

<form method="post">

<div class="mb-3 text-start">
<label>Name</label>
<input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['name'])?>" required>
</div>

<div class="mb-3 text-start">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" required>
</div>

<div class="mb-3 text-start">
<label>Phone</label>
<input type="text" name="phone" class="form-control" value="<?= $user['phone'] ?? '' ?>">
</div>

<button name="update_profile" class="btn btn-primary w-100">Update Profile</button>

</form>

<a href="dashboard.php" class="btn btn-secondary mt-3 w-100">⬅ Back Dashboard</a>

</div>

</div>

</body>
</html>
