<?php
session_start();
include("../config/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

if(!isset($_SESSION['user_id'])){
    die("Patient not logged in!");
}

$patient_id = $_SESSION['user_id'];
$msg = "";

if(isset($_POST['book'])){

    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    if($doctor_id == 0){
        $msg = "⚠️ Please select a doctor";
    }
    else if(empty($appointment_time)){
        $msg = "⚠️ Please select a time slot";
    }
    else {

        $query = "INSERT INTO appointments 
        (patient_id, doctor_id, appointment_date, appointment_time, status)
        VALUES 
        ('$patient_id','$doctor_id','$appointment_date','$appointment_time','pending')";

        if(mysqli_query($conn, $query)){

            // ================= MAIL START =================
            try {
                $mail = new PHPMailer(true);

                // ❌ Debug OFF rakho (production)
                $mail->SMTPDebug = 0;

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                // ✅ Gmail credentials
                $mail->Username = 'smarthospitalm@gmail.com';
                $mail->Password = 'rrxyqdddmomehqqx';

                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // 👉 Patient data
                $user_q = mysqli_query($conn,"SELECT email,name FROM users WHERE id='$patient_id'");
                $user_data = mysqli_fetch_assoc($user_q);

                $patient_email = $user_data['email'];
                $patient_name = $user_data['name'];

                // 👉 Doctor data
                $doc_q = mysqli_query($conn,"SELECT name FROM users WHERE id='$doctor_id'");
                $doc_data = mysqli_fetch_assoc($doc_q);

                $doctor_name = $doc_data['name'];

                // 👉 Email setup
                $mail->setFrom('smarthospitalm@gmail.com', 'MediQueue');
                $mail->addAddress($patient_email, $patient_name);

                $mail->isHTML(true);
                $mail->Subject = "Appointment Confirmation";

                $mail->Body = "
                <h2>Appointment Confirmed ✅</h2>
                <p>Dear $patient_name,</p>
                <p>Your appointment has been successfully booked.</p>
                <p><b>Doctor:</b> Dr. $doctor_name</p>
                <p><b>Date:</b> $appointment_date</p>
                <p><b>Time:</b> $appointment_time</p>
                <br>
                <p>Thank you for using MediQueue ❤️</p>
                ";

                // ✅ send mail
                if($mail->send()){
                    $msg = "✅ Appointment booked & Email sent!";
                } else {
                    $msg = "⚠️ Appointment booked but Email issue.";
                }

            } catch (Exception $e) {
                // ❌ error user ko directly mat dikhao
                $msg = "⚠️ Appointment booked but Email issue.";
            }
            // ================= MAIL END =================

        } else {
            die("Insert Error: ".mysqli_error($conn));
        }
    }
}
?>

<?php
/* Fetch approved doctors */
$doctors_result = mysqli_query($conn,"
SELECT u.id, u.name 
FROM users u
JOIN doctors d ON u.id = d.user_id
WHERE u.role='doctor' 
AND u.status='approved'
AND d.status='available'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            /* Modern medical gradient background */
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Background Animated Shapes */
        .shape {
            position: absolute;
            filter: blur(50px);
            z-index: -1;
            opacity: 0.6;
        }
        .shape-1 { width: 300px; height: 300px; background: #0072ff; top: -50px; left: -50px; border-radius: 50%; }
        .shape-2 { width: 400px; height: 400px; background: #00ffd5; bottom: -100px; right: -50px; border-radius: 50%; }

        /* Glassmorphism Card */
        .booking-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Profile Avatar */
        .avatar-container {
            text-align: center;
            margin-top: -80px;
            margin-bottom: 20px;
        }

        .avatar-container img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 25px rgba(0, 114, 255, 0.3);
            background: white;
        }

        .booking-card h3 {
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 25px;
        }

        /* Input Styling */
        .form-label {
            font-weight: 500;
            color: #475569;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: white;
            border-right: none;
            color: #0072ff;
            border-radius: 12px 0 0 12px;
            border-color: #cbd5e1;
        }

        .form-control, .form-select {
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px 15px;
            border-color: #cbd5e1;
            box-shadow: none !important;
            transition: 0.3s;
            color: #334155;
            background: white;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control,
        .input-group:focus-within .form-select {
            border-color: #0072ff;
            box-shadow: 0 0 0 0.25rem rgba(0, 114, 255, 0.1) !important;
        }

        /* Custom Alert */
        .custom-alert {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #047857;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .custom-alert.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #b91c1c;
        }

        /* Buttons */
        .btn-book {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: 0.4s;
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
            margin-top: 10px;
        }

        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 114, 255, 0.5);
            color: white;
        }

        .btn-back {
            border: 2px solid #cbd5e1;
            color: #64748b;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
            background: transparent;
        }

        .btn-back:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="booking-card">
        
        <div class="avatar-container">
            <img src="../assets/images/booking.jpeg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/822/822105.png'" alt="Booking Icon">
        </div>

        <h3>Book Appointment</h3>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= strpos($msg, '⚠️') !== false ? 'error' : '' ?>">
                <?php echo $msg; ?>
            </div>
        <?php } ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Select Doctor</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user-doctor"></i></span>
                    <select name="doctor_id" class="form-select" required>
                        <option value="" disabled selected>-- Choose a Specialist --</option>
                        <?php while($row = mysqli_fetch_assoc($doctors_result)){ ?>
                            <option value="<?php echo $row['id']; ?>">
                                Dr. <?php echo $row['name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Appointment Date</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Time Slot</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
                    <select name="appointment_time" class="form-select" required>
                        <option value="" disabled selected>-- Select Time --</option>
                        <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                        <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                        <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                        <option value="12:00 PM - 01:00 PM">12:00 PM - 01:00 PM</option>
                        <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                        <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="book" class="btn btn-book w-100">
                <i class="fa-solid fa-check-circle me-2"></i> Confirm Booking
            </button>

        </form>

        <a href="dashboard.php" class="btn btn-back w-100 mt-3 text-center text-decoration-none">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
        </a>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>