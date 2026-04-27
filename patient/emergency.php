<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("location:../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$msg = "";

// ================= SEND ALERT =================
if(isset($_POST['send_alert'])){

    $message = trim($_POST['message']);

    // 🔒 Empty / short message check
    if(strlen($message) < 5){
        $msg = "⚠️ Please enter a valid emergency message!";
    } 
    elseif(strlen($message) > 300){
        $msg = "⚠️ Message too long! Max 300 characters allowed.";
    }
    else{

        $message = mysqli_real_escape_string($conn, $message);

        // 🚫 Daily limit (max 3 alerts per day)
        $check = mysqli_query($conn,"
        SELECT COUNT(*) as total 
        FROM emergency_alerts 
        WHERE patient_id='$patient_id' 
        AND DATE(created_at)=CURDATE()
        ");

        if(!$check){
            $msg = "⚠️ Error checking limit!";
        } else {

            $data = mysqli_fetch_assoc($check);

            if($data['total'] >= 3){
                $msg = "🚫 Limit reached! Only 3 alerts allowed per day.";
            } 
            else{

                // ✅ Insert alert
                $insert = mysqli_query($conn, "
                INSERT INTO emergency_alerts(patient_id,message,status,created_at)
                VALUES('$patient_id','$message','pending',NOW())
                ");

                if($insert){
                    $msg = "🚨 Emergency alert sent successfully!";
                } else {
                    $msg = "⚠️ Database Error!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Alert | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at center, #1e1b4b, #0f172a);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Background Warning Shapes */
        .shape {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.3;
        }
        .shape-1 { width: 400px; height: 400px; background: #ef4444; top: -100px; left: -100px; border-radius: 50%; animation: float 6s infinite alternate; }
        .shape-2 { width: 350px; height: 350px; background: #dc2626; bottom: -50px; right: -50px; border-radius: 50%; animation: float 8s infinite alternate-reverse; }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(30px) scale(1.1); }
        }

        /* Glassmorphism Card */
        .alert-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(239, 68, 68, 0.05);
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: slideUp 0.8s ease;
            position: relative;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Pulsing Siren Icon */
        .icon-box {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px auto;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid #0f172a;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .alert-card h3 {
            margin-top: 35px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 1px;
        }

        .alert-card p {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }

        /* Textarea Styling */
        .form-control {
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(0, 0, 0, 0.3);
            color: #ffffff;
            font-size: 1rem;
            resize: none;
            height: 130px;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .form-control:focus {
            border-color: #ef4444;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            outline: none;
            background: rgba(0, 0, 0, 0.5);
            color: white;
        }

        /* Character Count */
        .count-box {
            text-align: right;
            font-size: 0.85rem;
            font-weight: 500;
            color: #64748b;
            margin-top: 8px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .count-box.limit-reached { color: #ef4444; font-weight: 700; }

        /* Submit Button */
        .btn-alert {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            transition: 0.4s ease;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }

        .btn-alert:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(239, 68, 68, 0.5);
            background: linear-gradient(45deg, #dc2626, #b91c1c);
            color: white;
        }

        /* Back Link */
        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #ffffff;
            transform: translateX(-5px);
        }

        /* Custom Alert Message */
        .custom-alert {
            border-radius: 12px;
            padding: 12px;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .custom-alert.success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .custom-alert.error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="alert-card">
        
        <div class="icon-box">
            <i class="fa-solid fa-truck-medical"></i>
        </div>

        <h3>Emergency Alert</h3>
        <p>This will notify the hospital staff immediately. Please use only in real emergencies.</p>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= strpos($msg, 'successfully') !== false ? 'success' : 'error' ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <form method="post" onsubmit="return validateEmergency()">

            <textarea 
                name="message" 
                id="message" 
                class="form-control" 
                maxlength="300"
                placeholder="Describe your emergency here (e.g., Heart pain, severe bleeding)..." 
                required
            ></textarea>

            <div class="count-box" id="countBox">
                <span id="chars">0</span> / 300
            </div>

            <button type="submit" name="send_alert" class="btn-alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> Send Emergency Alert
            </button>

        </form>

        <a href="dashboard.php" class="back-link">
            <i class="fa-solid fa-arrow-left me-1"></i> Cancel & Go Back
        </a>

    </div>

    <script>
        // Character Counter
        let msgBox = document.getElementById("message");
        let count = document.getElementById("chars");
        let countBox = document.getElementById("countBox");

        msgBox.addEventListener("input", function(){
            let currentLength = msgBox.value.length;
            count.innerText = currentLength;

            if(currentLength >= 280){
                countBox.classList.add('limit-reached');
            } else {
                countBox.classList.remove('limit-reached');
            }
        });

        // Validation
        function validateEmergency(){
            let msg = document.getElementById("message").value.trim();

            if(msg.length < 5){
                alert("⚠️ Please describe the emergency properly (minimum 5 characters).");
                return false;
            }

            if(msg.length > 300){
                alert("⚠️ Message too long! Maximum 300 characters allowed.");
                return false;
            }

            return true;
        }
    </script>

</body>
</html>