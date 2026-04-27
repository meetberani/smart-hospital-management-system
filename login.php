<?php
session_start();
include("config/db.php");

$msg="";

if(isset($_POST['login'])){

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];
$role = mysqli_real_escape_string($conn, $_POST['role']);

$query = "SELECT * FROM users WHERE email='$email' AND role='$role'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0){

$row = mysqli_fetch_assoc($result);

// 🚫 CHECK LOGIN ATTEMPTS
if($row['login_attempts'] >= 3){
    $msg = "🚫 Account Locked! Try later.";
}
else{

    if(password_verify($password, $row['password'])){

        // ✅ RESET ATTEMPTS
        mysqli_query($conn,"UPDATE users SET login_attempts=0 WHERE id=".$row['id']);

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user'] = $row['email'];
        $_SESSION['role'] = $row['role'];

        if($role=="admin"){
            header("location:admin/dashboard.php");
        }
        elseif($role=="doctor"){
            header("location:doctor/dashboard.php");
        }
        else{
            header("location:patient/dashboard.php");
        }
        exit;

    } else {

        // ❌ WRONG PASSWORD → INCREASE ATTEMPT
        mysqli_query($conn,"UPDATE users SET login_attempts = login_attempts + 1 WHERE id=".$row['id']);
        $msg = "❌ Invalid Password";

    }
}

}else{
$msg = "❌ User Not Found";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart HMS | Secure Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0284c7, #0d9488);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        /* Smooth Gradient Animation */
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Modern Glowing Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            background: #00ffd5;
            top: -50px;
            left: -50px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: #0072ff;
            bottom: -100px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 50px) scale(1.1); }
        }

        /* Login Card */
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 45px 40px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            color: white;
            z-index: 10;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .login-box h2 i {
            color: #00ffd5;
        }

        /* Alerts */
        .custom-alert {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Custom Inputs */
        .input-group {
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #00ffd5;
            box-shadow: 0 0 15px rgba(0, 255, 213, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }

        .input-group-text {
            background: transparent;
            color: #00ffd5;
            border: none;
            padding-left: 18px;
        }

        .form-control, .form-select {
            background: transparent !important;
            border: none;
            color: white !important;
            padding: 14px 15px;
            box-shadow: none !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Select Options Fix for Dark Mode */
        .form-select option {
            background: #1e293b;
            color: white;
        }

        /* Button */
        .login-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.4s ease;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 114, 255, 0.5);
            background: linear-gradient(45deg, #00c6ff, #0072ff);
        }

        /* Links */
        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .reg {
            color: #00ffd5;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .reg:hover {
            color: #ffffff;
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-box">
        <h2><i class="fa-solid fa-laptop-medical"></i> Smart HMS</h2>

        <?php if($msg != ""){ ?>
            <div class="custom-alert">
                <?php echo $msg; ?>
            </div>
        <?php } ?>

        <form method="post">

            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>

            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user-shield"></i></span>
                <select class="form-select" name="role" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>
            </div>

            <button class="login-btn" name="login">
                Login <i class="fa fa-arrow-right ms-2"></i>
            </button>

        </form>

        <div class="footer-text">
            New User? <a class="reg" href="register.php">Register Here</a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>