<?php
session_start();
include("../config/db.php");

// Admin security
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit;
}

// Fetch feedback
$query = "SELECT f.id, f.patient_id, f.message, f.created_at, u.name AS patient_name
          FROM feedback f
          LEFT JOIN users u ON f.patient_id = u.id
          ORDER BY f.created_at DESC";

$result = mysqli_query($conn, $query);
if(!$result){
    die("Query Failed: ".mysqli_error($conn));
}

$total_feedback = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Feedback | Admin Panel</title>

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
            border-left: 5px solid #8b5cf6; /* Violet Border for Feedback */
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-count {
            background: #ede9fe;
            color: #6d28d9;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* --- TABLE & LIST UI --- */
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
            padding: 20px 15px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody tr { transition: 0.3s; }
        .table tbody tr:hover { background: #f8fafc; transform: scale(1.002); }

        .patient-avatar {
            width: 40px; height: 40px;
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            margin-right: 12px;
        }

        /* Message Bubble Styling */
        .msg-bubble {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 0 15px 15px 15px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-style: italic;
            position: relative;
            max-width: 600px;
        }

        .msg-bubble::before {
            content: '\f10d'; /* FontAwesome Quote Left */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: -10px;
            left: -10px;
            color: #8b5cf6;
            background: white;
            padding: 0 5px;
            font-size: 1.2rem;
        }

        /* --- EMPTY STATE --- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #8b5cf6; opacity: 0.3; margin-bottom: 20px; }
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
        <a href="approve_doctor.php"><i class="fa-solid fa-user-doctor"></i> Approve Doctors</a>
        <a href="approve_patient.php"><i class="fa-solid fa-user-check"></i> Approve Patients</a>
        <a href="manage_medicine.php"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a href="emergency.php"><i class="fa-solid fa-truck-fast"></i> Emergency</a>
        <a class="active" href="#"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Patient Feedback Hub</h2>
                <p>Read what patients are saying about their hospital experience.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-regular fa-comment-dots me-1"></i> <?= $total_feedback ?> Reviews
                </span>
            </div>
        </div>

        <div class="table-card">
            
            <?php if($total_feedback > 0): ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th width="25%">Patient Name</th>
                                <th width="45%">Feedback Message</th>
                                <th width="20%">Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($row = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="patient-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['patient_name'] ?? 'Anonymous Patient') ?></span>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="msg-bubble">
                                        "<?= htmlspecialchars($row['message']) ?>"
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="fa-regular fa-calendar-days text-muted me-1"></i> 
                                        <?= date('d M, Y', strtotime($row['created_at'])) ?>
                                    </div>
                                    <small class="text-muted ms-4">
                                        <?= date('h:i A', strtotime($row['created_at'])) ?>
                                    </small>
                                </td>
                            </tr>

                            <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>

            <?php else: ?>

                <div class="empty-state">
                    <i class="fa-regular fa-face-smile"></i>
                    <h4>No Feedback Yet</h4>
                    <p>There is no patient feedback available in the system right now.</p>
                </div>

            <?php endif; ?>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>