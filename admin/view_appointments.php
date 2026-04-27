<?php
session_start();
include("../config/db.php");

// Admin check (Added for security, consistent with your other admin pages)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch all appointments
$query = "SELECT a.id, p.name as patient_name, u.name as doctor_name, d.specialization, a.appointment_date, a.status
          FROM appointments a
          JOIN users p ON a.patient_id = p.id
          JOIN users u ON a.doctor_id = u.id
          LEFT JOIN doctor_info d ON u.id = d.user_id
          ORDER BY a.appointment_date DESC";

$result = mysqli_query($conn, $query);
$total_appointments = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments | Admin Panel</title>

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

        .sidebar a i { margin-right: 15px; font-size: 18px; width: 25px; }

        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(90deg, #0072ff, #00c6ff);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }

        .sidebar .logout {
            margin-top: auto;
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
            min-height: 100vh;
        }

        .header-box {
            background: white;
            padding: 25px 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #0ea5e9; /* Sky Blue accent */
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-count {
            background: #e0f2fe;
            color: #0284c7;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* --- TABLE UI --- */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
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

        .patient-icon { color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 8px; border-radius: 8px; margin-right: 10px; }
        .doctor-icon { color: #0072ff; background: rgba(0, 114, 255, 0.1); padding: 8px; border-radius: 8px; margin-right: 10px; }
        
        .spec-badge {
            font-size: 0.8rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 12px;
            margin-top: 5px;
            display: inline-block;
        }

        /* Dynamic Status Badges */
        .status-badge { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
        .status-pending { background: #fef3c7; color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-approved { background: #d1fae5; color: #059669; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-completed { background: #dbeafe; color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.2); }
        .status-cancelled { background: #fee2e2; color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.2); }

        /* --- EMPTY STATE --- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #0ea5e9; opacity: 0.3; margin-bottom: 20px; }
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
        <h3><i class="fa-solid fa-shield-halved me-2"></i> Admin</h3>

        <a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a class="active" href="#"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a href="approve_doctor.php"><i class="fa-solid fa-user-doctor"></i> Approve Doctors</a>
        <a href="approve_patient.php"><i class="fa-solid fa-user-check"></i> Approve Patients</a>
        <a href="manage_medicine.php"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a href="emergency.php"><i class="fa-solid fa-truck-fast"></i> Emergency</a>
        <a href="feedback.php"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>All Appointments</h2>
                <p>View and monitor all patient bookings and schedules.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-solid fa-list-ul me-1"></i> <?= $total_appointments ?> Records
                </span>
            </div>
        </div>

        <div class="table-card">
            
            <?php if($total_appointments > 0){ ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Doctor Assigned</th>
                                <th>Date Scheduled</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($row = mysqli_fetch_assoc($result)){ 
                                
                                // Dynamic Badge Logic
                                $status = strtolower($row['status']);
                                $badgeClass = "status-pending"; // Default
                                if($status == "approved" || $status == "confirmed") $badgeClass = "status-approved";
                                if($status == "completed") $badgeClass = "status-completed";
                                if($status == "cancelled") $badgeClass = "status-cancelled";
                            ?>

                            <tr>
                                <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-bed-pulse patient-icon"></i>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['patient_name']) ?></span>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-user-doctor doctor-icon"></i>
                                        <div>
                                            <div class="fw-bold text-dark">Dr. <?= htmlspecialchars($row['doctor_name']) ?></div>
                                            <div class="spec-badge"><?= htmlspecialchars($row['specialization'] ?? "General Physician") ?></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="fa-regular fa-calendar text-muted me-1"></i> 
                                        <?= date('d M, Y', strtotime($row['appointment_date'])) ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                            </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <h4>No Appointments Found</h4>
                    <p>There are currently no appointments scheduled in the system.</p>
                </div>

            <?php } ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>