<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("location:../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$msg = "";
$msgType = ""; // To handle success vs error styling

if (isset($_POST['reschedule'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_date = $_POST['new_date'];

    if (strtotime($new_date) < strtotime(date("Y-m-d"))) {
        $msg = "❌ Cannot select a past date.";
        $msgType = "error";
    } else {
        mysqli_query($conn,"UPDATE appointments SET appointment_date='$new_date', status='pending' WHERE id='$appointment_id' AND patient_id='$patient_id'");
        $msg = "✅ Appointment Rescheduled Successfully!";
        $msgType = "success";
    }
}

$query="SELECT a.id, a.appointment_date, a.status, u.name AS doctor_name, d.specialization
FROM appointments a
JOIN users u ON a.doctor_id=u.id
LEFT JOIN doctors d ON u.id=d.user_id
WHERE a.patient_id='$patient_id'
ORDER BY a.appointment_date DESC";

$result = mysqli_query($conn, $query);
$num_appointments = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            min-height: 100vh;
            padding: 40px 0;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Animated Shapes */
        .shape {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.5;
        }
        .shape-1 { width: 400px; height: 400px; background: #0072ff; top: -100px; left: -100px; border-radius: 50%; }
        .shape-2 { width: 300px; height: 300px; background: #00ffd5; bottom: 10%; right: -50px; border-radius: 50%; }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            color: #1e293b;
            font-weight: 700;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Glassmorphism Cards */
        .card-ui {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 25px;
            transition: 0.4s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-ui:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }

        .doc-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.2);
            margin: 0 auto 15px auto;
            display: block;
        }

        .card-ui h5 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .specialization {
            color: #0072ff;
            font-size: 0.9rem;
            font-weight: 500;
            background: rgba(0, 114, 255, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.6);
            border-radius: 12px;
        }

        .info-row p { margin: 0; color: #475569; font-size: 0.95rem; }

        /* Status Badges */
        .badge-custom {
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-completed { background: rgba(59, 130, 246, 0.1); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Form Controls */
        .form-control {
            border-radius: 12px;
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            background: rgba(255, 255, 255, 0.8);
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #0072ff;
            box-shadow: 0 0 0 0.25rem rgba(0, 114, 255, 0.1);
            background: white;
        }

        .btn-primary {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            border: none;
            border-radius: 12px;
            padding: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
        }

        .custom-alert {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            font-weight: 500;
            text-align: center;
            max-width: 500px;
            margin: 0 auto 30px auto;
        }

        .custom-alert.success { color: #059669; border-left: 5px solid #10b981; }
        .custom-alert.error { color: #dc2626; border-left: 5px solid #ef4444; }

        .btn-back {
            background: white;
            color: #1e293b;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #f8fafc;
            color: #0072ff;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="container">
        <h2 class="page-header"><i class="fa-regular fa-calendar-check text-primary me-2"></i> My Appointments</h2>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= $msgType ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <div class="row g-4">

            <?php 
            if($num_appointments > 0) {
                while($row = mysqli_fetch_assoc($result)){ 
                    
                    // Dynamic Badge Logic
                    $status = strtolower($row['status']);
                    $badgeClass = "status-pending"; // Default
                    if($status == "approved" || $status == "confirmed") $badgeClass = "status-approved";
                    if($status == "completed") $badgeClass = "status-completed";
                    if($status == "cancelled") $badgeClass = "status-cancelled";
            ?>

            <div class="col-lg-4 col-md-6">
                <div class="card-ui text-center">
                    
                    <img src="../assets/images/doctor.jpeg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3774/3774299.png'" class="doc-img" alt="Doctor">
                    
                    <h5>Dr. <?= $row['doctor_name'] ?></h5>
                    <div class="mb-2"><span class="specialization"><?= $row['specialization'] ?? "General Physician" ?></span></div>

                    <div class="info-row">
                        <p><i class="fa-regular fa-calendar me-1"></i> <b><?= date('d M, Y', strtotime($row['appointment_date'])) ?></b></p>
                        <span class="badge-custom <?= $badgeClass ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </div>

                    <form method="post" class="mt-auto">
                        <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-calendar-day"></i></span>
                            <input type="date" name="new_date" class="form-control border-start-0 ps-0" required title="Select new date">
                        </div>
                        <button type="submit" name="reschedule" class="btn btn-primary w-100">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Reschedule Now
                        </button>
                    </form>

                </div>
            </div>

            <?php 
                } 
            } else { 
            ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <h4>No Appointments Found</h4>
                        <p class="text-muted">You haven't booked any appointments yet.</p>
                        <a href="book_appointment.php" class="btn btn-primary mt-2">Book an Appointment</a>
                    </div>
                </div>
            <?php } ?>

        </div>

        <div class="text-center mt-5">
            <a href="dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>