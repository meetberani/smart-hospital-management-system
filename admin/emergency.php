<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("location:../login.php");
    exit;
}

// ================= ACTION (APPROVE / REJECT) =================
if(isset($_GET['action']) && isset($_GET['id'])){
    
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if($action == "approve"){
        mysqli_query($conn,"UPDATE emergency_alerts SET status='approved', verified=1 WHERE id='$id'");
    }

    if($action == "reject"){
        mysqli_query($conn,"UPDATE emergency_alerts SET status='rejected', verified=0 WHERE id='$id'");
    }

    header("location:emergency.php");
    exit;
}

// ================= FETCH DATA =================
$data = mysqli_query($conn,"
SELECT e.*,u.name AS patient_name
FROM emergency_alerts e
LEFT JOIN users u ON e.patient_id=u.id
ORDER BY e.created_at DESC
");
$total_alerts = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Alerts | Admin Panel</title>

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
            color: #ef4444; /* Red accent for Admin Emergency */
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
            background: linear-gradient(90deg, #ef4444, #dc2626); /* Red Active State */
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
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
            border-left: 5px solid #ef4444; /* Red Border */
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-count {
            background: #fee2e2;
            color: #dc2626;
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

        .icon-patient { color: #dc2626; background: rgba(239, 68, 68, 0.1); padding: 8px; border-radius: 8px; margin-right: 10px; }

        .msg-box {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            border-left: 3px solid #ef4444;
            font-size: 0.9rem;
            color: #1e293b;
            max-width: 300px;
        }

        /* Dynamic Status Badges */
        .status-badge { padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: inline-block; }
        
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

        /* --- ACTION BUTTONS --- */
        .btn-approve {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .btn-approve:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4); }

        .btn-reject {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }
        .btn-reject:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4); }

        /* --- EMPTY STATE --- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #10b981; opacity: 0.3; margin-bottom: 20px; }
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
        <h3><i class="fa-solid fa-truck-medical me-2"></i> HMS Admin</h3>

        <a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="approve_doctor.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
        <a href="approve_patient.php"><i class="fa-solid fa-user-check"></i> Patients</a>
        <a href="manage_medicine.php"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a class="active" href="#"><i class="fa-solid fa-bell"></i> Emergency</a>
        <a href="feedback.php"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Emergency Alerts</h2>
                <p>Respond to critical patient alerts and SOS requests immediately.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $total_alerts ?> Logs
                </span>
            </div>
        </div>

        <div class="table-card">
            
            <?php if($total_alerts > 0){ ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Critical Message</th>
                                <th>Time Reported</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($e = mysqli_fetch_assoc($data)){ 
                                
                                // Status Badge Logic
                                $status = strtolower($e['status']);
                                $badgeClass = "status-pending"; // Default
                                if($status == "approved") $badgeClass = "status-approved";
                                if($status == "rejected") $badgeClass = "status-rejected";
                            ?>

                            <tr>
                                <td class="fw-bold text-muted">#<?= $e['id'] ?></td>
                                
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-user-injured icon-patient"></i> 
                                    <?= htmlspecialchars($e['patient_name'] ?? "Unknown") ?>
                                </td>
                                
                                <td>
                                    <div class="msg-box">
                                        <?= htmlspecialchars($e['message']) ?>
                                    </div>
                                </td>
                                
                                <td class="text-muted">
                                    <div class="fw-semibold text-dark"><i class="fa-regular fa-clock me-1 text-danger"></i> <?= date('h:i A', strtotime($e['created_at'])) ?></div>
                                    <small><?= date('d M, Y', strtotime($e['created_at'])) ?></small>
                                </td>
                                
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <?php if($status == 'pending'){ ?>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn-approve" href="?action=approve&id=<?= $e['id'] ?>" title="Approve/Acknowledge">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                            <a class="btn-reject" href="?action=reject&id=<?= $e['id'] ?>" title="Reject/Dismiss">
                                                <i class="fa-solid fa-xmark"></i>
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
                    <i class="fa-solid fa-shield-heart"></i>
                    <h4>All Clear!</h4>
                    <p>There are no emergency alerts at the moment. Everything is running smoothly.</p>
                </div>

            <?php } ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>