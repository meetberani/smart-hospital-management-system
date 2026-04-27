<?php
session_start();
include("../config/db.php");

$msg = "";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $q = mysqli_query($conn,
        "SELECT * FROM users 
         WHERE email='$email' AND password='$password' 
         AND role='patient'");

    if (mysqli_num_rows($q) == 1) {
        $user = mysqli_fetch_assoc($q);

        if ($user['approved'] == 0) {
            $msg = "⏳ Waiting for admin approval";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = "patient";
            header("location:dashboard.php");
            exit;
        }
    } else {
        $msg = "❌ Invalid login details";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 col-md-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>Patient Login</h4>
        </div>
        <div class="card-body">

            <?php if($msg!=""){ ?>
                <div class="alert alert-warning"><?php echo $msg; ?></div>
            <?php } ?>

            <form method="post">
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
