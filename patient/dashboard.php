<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();
include("../config/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!='patient'){
    header("location:../login.php");
    exit;
}

$id=$_SESSION['user_id'];

/* Latest bill */
$billQ=mysqli_query($conn,"SELECT * FROM bills WHERE patient_id='$id' ORDER BY id DESC LIMIT 1");
$bill=mysqli_fetch_assoc($billQ);

/* Latest appointment */
$appQ=mysqli_query($conn,"SELECT * FROM appointments WHERE patient_id='$id' ORDER BY id DESC LIMIT 1");
$app=mysqli_fetch_assoc($appQ);

// Status Color Logic (For better UI presentation)
$status = strtolower($app['status'] ?? 'N/A');
$statusColor = "bg-secondary"; // default
if($status == 'confirmed' || $status == 'completed') $statusColor = "bg-success";
if($status == 'pending') $statusColor = "bg-warning text-dark";
if($status == 'cancelled') $statusColor = "bg-danger";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa; /* Clean light grey background */
            overflow-x: hidden;
            margin: 0;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: #0f172a;
            padding: 20px 15px;
            color: white;
            transition: 0.4s;
            z-index: 1000;
        }

        .sidebar h3 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            color: #00ffd5;
            letter-spacing: 1px;
        }

        .sidebar h3 i {
            color: white;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 20px;
            margin-bottom: 8px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
        }

        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(90deg, rgba(0, 114, 255, 0.8), rgba(0, 198, 255, 0.8));
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }

        .sidebar .logout {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 30px);
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .sidebar .logout:hover {
            background: #ef4444;
            color: white;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        /* --- MAIN CONTENT --- */
        .main {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
            min-height: 100vh;
        }

        /* Header */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .header-box h2 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
        }

        .header-box p {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .avatar img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0072ff;
            padding: 2px;
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.2);
        }

        /* --- CARDS --- */
        .card-custom {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            border: none;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .card-custom:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .card-icon {
            font-size: 35px;
            position: absolute;
            right: 20px;
            top: 25px;
            opacity: 0.15;
            transition: 0.3s;
        }

        .card-custom:hover .card-icon {
            opacity: 0.5;
            transform: scale(1.1);
        }

        .card-custom h4 {
            color: #64748b;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-custom p {
            color: #0f172a;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        /* Themed Cards */
        .card-appt .card-icon { color: #0072ff; }
        .card-bill .card-icon { color: #10b981; }
        
        /* Emergency Card Special Look */
        .card-emergency {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
        }
        .card-emergency h4, .card-emergency p { color: white; }
        .card-emergency .card-icon { color: white; opacity: 0.3; }

        /* --- BIG ACTIVITY CARD --- */
        .activity-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
        }

        .activity-card h3 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #0072ff;
        }

        .activity-item:last-child {
            border-left-color: #10b981;
        }

        .activity-item i {
            font-size: 20px;
            margin-right: 15px;
            color: #64748b;
        }

        .activity-text strong {
            display: block;
            color: #334155;
        }

        .activity-text span {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Responsive Logic */
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; padding: 15px; }
            .sidebar .logout { position: relative; width: 100%; bottom: 0; margin-top: 10px; }
            .main { margin-left: 0; width: 100%; padding: 15px; }
            .header-box { flex-direction: column-reverse; text-align: center; gap: 15px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-hospital me-2"></i> MediCare</h3>

        <a href="#" class="active"><i class="fa-solid fa-house-chimney"></i> Dashboard</a>
        <a href="book_appointment.php"><i class="fa-solid fa-calendar-plus"></i> Book Appt.</a>
        <a href="reschedule.php"><i class="fa-solid fa-clock-rotate-left"></i> Reschedule</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> My Bills</a>
        <a href="profile.php"><i class="fa-solid fa-user-pen"></i> Profile</a>
        <a href="feedback.php"><i class="fa-solid fa-comment-dots"></i> Feedback</a>
        
        <a href="emergency.php" style="color: #ff4b2b;"><i class="fa-solid fa-truck-medical"></i> Emergency</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Welcome Back</h2>
                <p>Smart Healthcare System - Your health is our priority.</p>
            </div>
            <div class="avatar">
                <img src="../assets/images/patient.jpeg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" alt="User Avatar">
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-3 col-md-6">
                <div class="card-custom card-appt">
                    <i class="fa-solid fa-calendar-check card-icon"></i>
                    <h4>Last Appt.</h4>
                    <p><?= $app['appointment_date'] ?? 'No Record' ?></p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-custom">
                    <i class="fa-solid fa-clipboard-list card-icon" style="color: #f59e0b;"></i>
                    <h4>Status</h4>
                    <p>
                        <span class="badge <?= $statusColor ?> fs-6">
                            <?= ucfirst($app['status'] ?? 'N/A') ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-custom card-bill">
                    <i class="fa-solid fa-indian-rupee-sign card-icon"></i>
                    <h4>Total Bill</h4>
                    <p>₹<?= $bill['total_amount'] ?? '0.00' ?></p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-custom card-emergency">
                    <i class="fa-solid fa-truck-fast card-icon"></i>
                    <h4>Emergency</h4>
                    <p>24x7 Support</p>
                </div>
            </div>

        </div>

        <div class="activity-card">
            <h3><i class="fa-solid fa-bolt me-2 text-warning"></i> Latest Activity</h3>

            <div class="activity-item">
                <i class="fa-solid fa-user-doctor"></i>
                <div class="activity-text">
                    <strong>Doctor Visit Scheduled</strong>
                    <span>Date: <?= $app['appointment_date'] ?? 'No recent appointments' ?></span>
                </div>
            </div>

            <div class="activity-item">
                <i class="fa-solid fa-receipt"></i>
                <div class="activity-text">
                    <strong>Invoice Generated</strong>
                    <span>Bill Date: <?= $bill['bill_date'] ?? 'No recent bills' ?></span>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>