<?php
include("config/db.php");

$msg = "";
$msgType = "";

if (isset($_POST['register'])) {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Note: Ideally use password_hash here for security

    // Check email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "❌ Email already registered";
        $msgType = "error";
    } else {

        // Insert patient (approved = 0, admin approval needed)
        // Fixed: Matching your dashboard logic where status might be used instead of approved column
        $query = "INSERT INTO users (name, email, password, role, status)
                  VALUES ('$name', '$email', '$password', 'patient', 'pending')";

        if (mysqli_query($conn, $query)) {
            $msg = "✅ Registration successful! Please wait for admin approval.";
            $msgType = "success";
        } else {
            $msg = "❌ Registration failed. Try again.";
            $msgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration | Smart HMS</title>

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
            opacity: 0.5;
            animation: float 10s infinite ease-in-out alternate;
            z-index: 1;
        }

        .orb-1 { width: 300px; height: 300px; background: #00ffd5; top: -50px; left: -50px; }
        .orb-2 { width: 400px; height: 400px; background: #0072ff; bottom: -100px; right: -50px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 50px) scale(1.1); }
        }

        /* Glassmorphism Card */
        .register-box {
            width: 100%;
            max-width: 450px;
            padding: 40px;
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

        .register-box h2 {
            text-align: center;
            margin-bottom: 10px;
            font-weight: 700;
            color: #ffffff;
        }

        .register-box p.subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        /* Alert Styling */
        .custom-alert {
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-align: center;
            border: 1px solid transparent;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; }

        /* Input Group Styling */
        .input-group {
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
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

        .form-control {
            background: transparent !important;
            border: none;
            color: white !important;
            padding: 14px 15px;
            box-shadow: none !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Register Button */
        .btn-register {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.4s ease;
            margin-top: 10px;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.5);
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .login-link {
            color: #00ffd5;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .login-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="register-box">
        <h2><i class="fa-solid fa-user-plus text-success"></i> Join Us</h2>
        <p class="subtitle">Create your Patient account for Smart HMS</p>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= ($msgType == 'success') ? 'alert-success' : 'alert-error' ?>">
                <?php echo $msg; ?>
            </div>
        <?php } ?>

        <form method="post">
            
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Create Password" required>
            </div>

            <button name="register" class="btn-register">
                Register Now <i class="fa fa-paper-plane ms-2"></i>
            </button>

        </form>

        <div class="footer-text">
            Already Registered? <a class="login-link" href="login.php">Login Here</a>
        </div>
    </div>

</body>
</html>