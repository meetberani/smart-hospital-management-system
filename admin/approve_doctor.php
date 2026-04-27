<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if(isset($_GET['approve'])){
    $id = intval($_GET['approve']);

    mysqli_query($conn,"UPDATE users SET status='approved' WHERE id='$id'");

    mysqli_query($conn,"
    INSERT INTO notifications(user_id,message,seen)
    VALUES('$id','✅ Your account was approved by Admin',0)
    ");

    header("location:approve_doctor.php");
    exit;
}

$doctors = mysqli_query($conn,"SELECT * FROM users WHERE role='doctor' AND status='pending'");
$num_pending = mysqli_num_rows($doctors);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Doctors | Admin Panel</title>

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
            border-left: 5px solid #0072ff; /* Blue border for Doctors */
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-pending {
            background: #dbeafe;
            color: #1d4ed8;
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
        .table tbody tr:hover { background: #f8fafc; transform: scale(1.005); }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(0, 114, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0072ff;
            font-size: 1.2rem;
        }

        /* --- APPROVE BUTTON (Blue for Doctors) --- */
        .btn-approve {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 114, 255, 0.3);
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.4);
            color: white;
            background: linear-gradient(45deg, #00c6ff, #0072ff);
        }

        /* --- EMPTY STATE --- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 60px;
            color: #0072ff;
            margin-bottom: 20px;
            background: rgba(0, 114, 255, 0.1);
            padding: 25px;
            border-radius: 50%;
        }
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
        <a class="active" href="#"><i class="fa-solid fa-user-doctor"></i> Approve Doctors</a>
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
                <h2>Pending Doctors</h2>
                <p>Review and verify new doctor registrations.</p>
            </div>
            <div>
                <span class="badge-pending">
                    <i class="fa-solid fa-stethoscope me-1"></i> <?= $num_pending ?> Pending
                </span>
            </div>
        </div>

        <div class="table-card">
            
            <?php if($num_pending > 0){ ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Doctor Details</th>
                                <th>Email Address</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($d = mysqli_fetch_assoc($doctors)){ ?>

                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <i class="fa-solid fa-user-doctor"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Dr. <?= htmlspecialchars($d['name']) ?></div>
                                            <small class="text-muted">Awaiting Verification</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mt-2 text-muted">
                                        <i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($d['email']) ?>
                                    </div>
                                </td>
                                <td class="text-end align-middle">
                                    <a class="btn-approve" href="?approve=<?= $d['id'] ?>">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </a>
                                </td>
                            </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="empty-state">
                    <i class="fa-solid fa-user-md"></i>
                    <h4>All Caught Up!</h4>
                    <p>There are no pending doctor approvals at the moment.</p>
                </div>

            <?php } ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>