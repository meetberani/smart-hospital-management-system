<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("location:../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$msg = "";
$msgType = "";

if(isset($_POST['submit_feedback'])){
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    $insert = mysqli_query($conn, "INSERT INTO feedback(patient_id, message, created_at) VALUES('$patient_id', '$feedback', NOW())");
    
    if($insert){
        $msg = "Thank you! Your feedback has been submitted successfully. ✅";
        $msgType = "success";
    } else {
        $msg = "⚠️ Error submitting feedback: " . mysqli_error($conn);
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Feedback | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Animated Shapes */
        .shape {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.5;
        }
        .shape-1 { width: 350px; height: 350px; background: #0072ff; top: -50px; left: -100px; border-radius: 50%; }
        .shape-2 { width: 300px; height: 300px; background: #00ffd5; bottom: -50px; right: -50px; border-radius: 50%; }

        /* Glassmorphism Card */
        .feedback-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
            text-align: center;
            animation: slideUp 0.8s ease;
            position: relative;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Floating Top Icon */
        .icon-box {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 10px 25px rgba(0, 114, 255, 0.4);
            border: 4px solid white;
        }

        .feedback-card h3 {
            margin-top: 30px;
            font-weight: 700;
            color: #1e293b;
        }

        .sub {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }

        /* Textarea Styling */
        .form-control {
            border-radius: 16px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            background: rgba(255, 255, 255, 0.9);
            color: #334155;
            font-size: 1rem;
            resize: none;
            height: 160px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .form-control:focus {
            border-color: #0072ff;
            box-shadow: 0 0 0 4px rgba(0, 114, 255, 0.1);
            outline: none;
            background: white;
        }

        /* Character Count */
        .count-box {
            text-align: right;
            font-size: 0.85rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 8px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .count-box.limit-reached { color: #ef4444; }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: 0.4s ease;
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 114, 255, 0.4);
            color: white;
        }

        /* Back Link */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #0072ff;
            transform: translateX(-3px);
        }

        /* Custom Alert */
        .custom-alert {
            border-radius: 12px;
            padding: 12px;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .custom-alert.success { background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.3); }
        .custom-alert.error { background: rgba(239, 68, 68, 0.1); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.3); }

    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="feedback-card">
        
        <div class="icon-box">
            <i class="fa-regular fa-comments"></i>
        </div>

        <h3>Patient Feedback</h3>
        <p class="sub">We value your opinion. Help us improve our services.</p>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= $msgType ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <form method="post" id="feedbackForm">
            
            <textarea 
                name="feedback" 
                id="feedback" 
                class="form-control" 
                placeholder="Share your experience with us..." 
                maxlength="300" 
                required
            ></textarea>
            
            <div class="count-box">
                <span id="chars">0</span> / 300
            </div>

            <button type="submit" name="submit_feedback" class="btn-submit" id="submitBtn">
                <i class="fa-solid fa-paper-plane me-2"></i> Submit Feedback
            </button>

        </form>

        <a href="dashboard.php" class="back-link">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const textarea = document.getElementById('feedback');
            const charCount = document.getElementById('chars');
            const countBox = document.querySelector('.count-box');

            textarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCount.textContent = currentLength;

                // Turn red if close to limit
                if (currentLength >= 300) {
                    countBox.classList.add('limit-reached');
                } else {
                    countBox.classList.remove('limit-reached');
                }
            });
        });
    </script>

</body>
</html>