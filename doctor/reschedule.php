<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("location:../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$msg = "";
$msgType = "success";

if(isset($_GET['approve'])){
    $app_id = intval($_GET['approve']);
    mysqli_query($conn,"UPDATE appointments SET status='approved' WHERE id='$app_id' AND doctor_id='$doctor_id'");
    $msg = "✅ Appointment approved successfully!";
    $msgType = "success";
}

if(isset($_GET['reject'])){
    $app_id = intval($_GET['reject']);
    mysqli_query($conn,"UPDATE appointments SET status='rejected' WHERE id='$app_id' AND doctor_id='$doctor_id'");
    $msg = "❌ Appointment request rejected!";
    $msgType = "error";
}

$requests = mysqli_query($conn,"
SELECT a.id, a.appointment_date, a.appointment_time, u.name AS patient_name
FROM appointments a
JOIN users u ON a.patient_id=u.id
WHERE a.doctor_id='$doctor_id' AND a.status='reschedule'
ORDER BY a.id DESC
");
$num_requests = mysqli_num_rows($requests);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Requests | Doctor Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: #0f172a;
            padding: 25px 15px;
            color: white;
            transition: 0.4s;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .sidebar h3 { font-weight: 700; text-align: center; margin-bottom: 35px; color: #00ffd5; letter-spacing: 1px; font-size: 1.4rem; }

        .sidebar a {
            display: flex; align-items: center; color: #cbd5e1; text-decoration: none; padding: 12px 18px; margin-bottom: 8px; border-radius: 12px; font-weight: 500; transition: all 0.3s ease;
        }
        .sidebar a i { margin-right: 15px; font-size: 18px; width: 25px; }
        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(90deg, #0072ff, #00c6ff); color: white; transform: translateX(5px); box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }

        .sidebar-bottom { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
        .btn-available { background: rgba(16, 185, 129, 0.15) !important; color: #10b981 !important; }
        .btn-available:hover { background: #10b981 !important; color: white !important; transform: none !important; box-shadow: none !important; }
        .btn-leave { background: rgba(239, 68, 68, 0.15) !important; color: #ef4444 !important; }
        .btn-leave:hover { background: #ef4444 !important; color: white !important; transform: none !important; box-shadow: none !important; }
        .btn-logout { color: #cbd5e1; }
        .btn-logout:hover { background: rgba(255,255,255,0.1) !important; transform: none !important; box-shadow: none !important; }

        /* --- MAIN CONTENT --- */
        .main { margin-left: 260px; padding: 30px; min-height: 100vh; }

        .header-box {
            background: white; padding: 20px 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); margin-bottom: 35px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #f59e0b; /* Orange for Reschedule */
        }
        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; font-size: 1.6rem; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-count {
            background: #fef3c7; color: #d97706; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px;
        }

        /* --- TABLE UI --- */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table { margin: 0; vertical-align: middle; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; padding: 15px; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;
        }
        
        .table tbody td { padding: 15px; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr { transition: 0.3s; }
        .table tbody tr:hover { background: #f8fafc; }

        .patient-info { display: flex; align-items: center; gap: 12px; }
        .patient-avatar {
            width: 40px; height: 40px; background: rgba(0, 114, 255, 0.1); color: #0072ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }

        /* Custom Alerts */
        .custom-alert {
            padding: 12px 18px; border-radius: 12px; margin-bottom: 25px; font-weight: 500; font-size: 0.95rem; text-align: center;
        }
        .custom-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .custom-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* --- ACTION BUTTONS --- */
        .btn-approve {
            background: linear-gradient(45deg, #10b981, #059669); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .btn-approve:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4); }

        .btn-reject {
            background: linear-gradient(45deg, #ef4444, #dc2626); color: white; border: none; padding: 8px 15px; border-radius: 10px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }
        .btn-reject:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4); }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #f59e0b; opacity: 0.3; margin-bottom: 20px; }
        .empty-state h4 { color: #1e293b; font-weight: 700; }

        .btn-back {
            background: transparent; color: #64748b; border: 2px solid #cbd5e1; padding: 10px 25px; border-radius: 30px; font-weight: 600; transition: 0.3s; text-decoration: none; display: inline-block; margin-top: 25px;
        }
        .btn-back:hover { background: #f1f5f9; color: #1e293b; }

        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main { margin-left: 0; } }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-user-doctor me-2"></i> DocPanel</h3>

        <a href="dashboard.php"><i class="fa-solid fa-house-medical"></i> Dashboard</a>
        <a href="view_appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a href="add_bill.php"><i class="fa-solid fa-file-invoice-dollar"></i> Add Bill</a>
        <a href="working_time.php"><i class="fa-solid fa-clock"></i> Working Hours</a>
        <a class="active" href="#"><i class="fa-solid fa-calendar-day"></i> Reschedule</a>
        
        <div class="sidebar-bottom">
            <a href="available.php" class="btn-available"><i class="fa-solid fa-check-circle"></i> Mark Available</a>
            <a href="leave.php" class="btn-leave"><i class="fa-solid fa-bed"></i> Emergency Leave</a>
            <a href="../logout.php" class="btn-logout mt-2"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Reschedule Requests</h2>
                <p>Manage patient requests for changing appointment timings.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-solid fa-hourglass-half me-1"></i> <?= $num_requests ?> New Requests
                </span>
            </div>
        </div>

        <?php if($msg != ""){ ?>
            <div class="custom-alert <?= ($msgType == 'success') ? 'success' : 'error' ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <div class="table-card">
            <?php if($num_requests > 0){ ?>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Requested Date</th>
                                <th>Requested Time</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($requests)){ ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                                    <td>
                                        <div class="patient-info">
                                            <div class="patient-avatar"><i class="fa-solid fa-user"></i></div>
                                            <span class="fw-bold"><?= htmlspecialchars($row['patient_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><i class="fa-regular fa-calendar-days text-primary me-2"></i><?= date('d M, Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><span class="badge bg-light text-dark border px-3 py-2"><i class="fa-regular fa-clock me-2"></i><?= $row['appointment_time'] ?? 'N/A' ?></span></td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn-approve" href="?approve=<?= $row['id'] ?>">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </a>
                                            <a class="btn-reject" href="?reject=<?= $row['id'] ?>">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h4>All Caught Up!</h4>
                    <p class="text-muted">No pending reschedule requests at the moment.</p>
                </div>
            <?php } ?>
        </div>

        <a href="dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
        </a>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>