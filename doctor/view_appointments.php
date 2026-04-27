<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';

    $app = mysqli_fetch_assoc(mysqli_query($conn,"SELECT patient_id FROM appointments WHERE id='$id'"));

    mysqli_query($conn,"UPDATE appointments SET status='$status' WHERE id='$id'");

    $msg = ($status == "approved") ? "✅ Your appointment is approved" : "❌ Your appointment is rejected";

    mysqli_query($conn,"INSERT INTO notifications(user_id,message)
    VALUES('".$app['patient_id']."','$msg')");

    header("Location: view_appointments.php");
    exit;
}

// Fallback checking for time_slot vs appointment_time based on DB schema variations
$query = "
SELECT a.id, a.appointment_date, a.time_slot, a.appointment_time, a.status,
u.name AS patient_name, u.email AS patient_email
FROM appointments a
JOIN users u ON a.patient_id=u.id
WHERE a.doctor_id='$doctor_id'
ORDER BY a.id DESC
";

$result = mysqli_query($conn, $query);
$total_appointments = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | Doctor Panel</title>

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

        .sidebar h3 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 35px;
            color: #00ffd5;
            letter-spacing: 1px;
            font-size: 1.4rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 18px;
            margin-bottom: 8px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar a i { margin-right: 15px; font-size: 18px; width: 25px; }

        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(90deg, #0072ff, #00c6ff);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }

        .sidebar-bottom {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px;
        }

        .btn-available { background: rgba(16, 185, 129, 0.15) !important; color: #10b981 !important; }
        .btn-available:hover { background: #10b981 !important; color: white !important; transform: none !important; box-shadow: none !important; }
        
        .btn-leave { background: rgba(239, 68, 68, 0.15) !important; color: #ef4444 !important; }
        .btn-leave:hover { background: #ef4444 !important; color: white !important; transform: none !important; box-shadow: none !important; }

        .btn-logout { color: #cbd5e1; }
        .btn-logout:hover { background: rgba(255,255,255,0.1) !important; transform: none !important; box-shadow: none !important; }

        /* --- MAIN CONTENT --- */
        .main {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        .header-box {
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #0072ff;
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; font-size: 1.6rem; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .stat-badge {
            background: #e0f2fe;
            color: #0284c7;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- TABLE UI --- */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table { margin: 0; vertical-align: middle; }
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 15px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody tr { transition: 0.3s; }
        .table tbody tr:hover { background: #f8fafc; transform: scale(1.002); }

        .patient-avatar {
            width: 40px; height: 40px;
            background: rgba(0, 114, 255, 0.1);
            color: #0072ff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .time-badge {
            font-size: 0.85rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        /* Dynamic Status Badges */
        .status-badge { padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: inline-block;}
        
        .status-pending { 
            background: #fef3c7; color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);
            animation: pulse-warning 2s infinite; 
        }
        @keyframes pulse-warning {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        .status-approved { background: #d1fae5; color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-rejected { background: #fee2e2; color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }
        .status-completed { background: #e0f2fe; color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2); }

        /* --- ACTION BUTTONS --- */
        .btn-approve {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-approve:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4); }

        .btn-reject {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-reject:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4); }

        /* --- EMPTY STATE --- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #0072ff; opacity: 0.3; margin-bottom: 20px; }
        .empty-state h4 { color: #1e293b; font-weight: 700; margin-bottom: 10px; }
        .empty-state p { color: #64748b; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-user-doctor me-2"></i> DocPanel</h3>

        <a href="dashboard.php"><i class="fa-solid fa-house-medical"></i> Dashboard</a>
        <a class="active" href="#"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a href="add_bill.php"><i class="fa-solid fa-file-invoice-dollar"></i> Add Bill</a>
        <a href="working_time.php"><i class="fa-solid fa-clock"></i> Working Hours</a>
        <a href="reschedule.php"><i class="fa-solid fa-calendar-days"></i> Reschedule</a>
        
        <div class="sidebar-bottom">
            <a href="available.php" class="btn-available"><i class="fa-solid fa-check-circle"></i> Mark Available</a>
            <a href="leave.php" class="btn-leave"><i class="fa-solid fa-bed"></i> Emergency Leave</a>
            <a href="../logout.php" class="btn-logout mt-2"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>My Appointments</h2>
                <p>Manage patient bookings and schedule consultations.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="stat-badge">
                    <i class="fa-regular fa-calendar-days"></i> <b><?= $total_appointments ?></b> Requests
                </div>
            </div>
        </div>

        <div class="table-card">
            
            <?php if($total_appointments > 0){ ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Patient Details</th>
                                <th>Schedule Date & Time</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($row = mysqli_fetch_assoc($result)){ 
                                
                                // Fetch time safely depending on DB column name
                                $time = $row['time_slot'] ?? $row['appointment_time'] ?? 'N/A';

                                // Status Badge Logic
                                $status = strtolower($row['status']);
                                $badgeClass = "status-pending"; // Default
                                if($status == "approved" || $status == "confirmed") $badgeClass = "status-approved";
                                if($status == "rejected" || $status == "cancelled") $badgeClass = "status-rejected";
                                if($status == "completed") $badgeClass = "status-completed";
                            ?>

                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="patient-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['patient_name']) ?></div>
                                            <small class="text-muted"><i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($row['patient_email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold text-dark mb-1">
                                        <i class="fa-regular fa-calendar text-primary me-1"></i> 
                                        <?= date('d M, Y', strtotime($row['appointment_date'])) ?>
                                    </div>
                                    <div class="time-badge">
                                        <i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($time) ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                
                                <td class="text-center align-middle">
                                    <?php if($status == "pending"){ ?>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn-approve" href="?action=approve&id=<?=$row['id']?>" title="Approve Appointment">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </a>
                                            <a class="btn-reject" href="?action=reject&id=<?=$row['id']?>" title="Reject Appointment">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </a>
                                        </div>
                                    <?php } else { ?>
                                        <span class="text-muted"><i class="fa-solid fa-minus"></i></span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="empty-state">
                    <i class="fa-regular fa-calendar-plus"></i>
                    <h4>Schedule is Clear</h4>
                    <p>You have no appointments booked at the moment.</p>
                </div>

            <?php } ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>