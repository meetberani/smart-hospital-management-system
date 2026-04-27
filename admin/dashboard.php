<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:../login.php");
    exit;
}

$search = mysqli_real_escape_string($conn, $_GET['search'] ?? "");

/* Statistics */
$doctorCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM users WHERE role='doctor'"))['total'];
$patientCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM users WHERE role='patient'"))['total'];
$medicineCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM medicines"))['total'];
$billCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) total FROM bills"))['total'];
$revenue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) total FROM bills"))['total'];

if($search != ""){
    $users = mysqli_query($conn,"SELECT * FROM users WHERE name LIKE '%$search%' OR email LIKE '%$search%'");
} else {
    $users = mysqli_query($conn,"SELECT * FROM users");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Smart HMS</title>

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

        .sidebar a i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
        }

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
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.02);
            margin-bottom: 35px;
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        /* --- STAT CARDS --- */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            transition: 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.02);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .stat-info h5 { color: #64748b; font-size: 0.95rem; font-weight: 600; margin-bottom: 5px; }
        .stat-info h2 { color: #0f172a; font-size: 1.8rem; font-weight: 700; margin: 0; }

        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }

        /* Card Theme Colors */
        .c-doc .stat-icon { background: rgba(0, 114, 255, 0.1); color: #0072ff; }
        .c-pat .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .c-med .stat-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        .c-bil .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .c-rev { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; }
        .c-rev .stat-info h5, .c-rev .stat-info h2 { color: white; }
        .c-rev .stat-icon { background: rgba(255,255,255,0.2); color: white; }

        /* --- TABLE & SEARCH --- */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            margin-top: 35px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            max-width: 400px;
            margin-bottom: 25px;
        }

        .search-form input {
            border-radius: 12px;
            padding: 12px 20px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            box-shadow: none;
            transition: 0.3s;
        }
        
        .search-form input:focus { border-color: #0072ff; background: white; outline: none; }

        .search-form button {
            background: #0072ff;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 25px;
            font-weight: 600;
            transition: 0.3s;
        }

        .search-form button:hover { background: #005bb5; }

        /* Custom Table */
        .table { margin: 0; vertical-align: middle; }
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px;
        }
        .table tbody td {
            padding: 15px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:hover { background: #f8fafc; }

        /* Badges */
        .badge { padding: 8px 12px; border-radius: 8px; font-weight: 500; letter-spacing: 0.5px; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-shield-halved me-2"></i> Admin</h3>

        <a class="active" href="#"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="approve_doctor.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
        <a href="approve_patient.php"><i class="fa-solid fa-bed-pulse"></i> Patients</a>
        <a href="manage_medicine.php"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a href="emergency.php"><i class="fa-solid fa-truck-fast"></i> Emergency</a>
        <a href="feedback.php"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Admin Command Center</h2>
                <p>System overview and management dashboard.</p>
            </div>
            <div>
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin" style="width: 50px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card c-doc">
                    <div class="stat-info">
                        <h5>Total Doctors</h5>
                        <h2><?= $doctorCount ?></h2>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-stethoscope"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card c-pat">
                    <div class="stat-info">
                        <h5>Total Patients</h5>
                        <h2><?= $patientCount ?></h2>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card c-med">
                    <div class="stat-info">
                        <h5>Medicines</h5>
                        <h2><?= $medicineCount ?></h2>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-capsules"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card c-rev">
                    <div class="stat-info">
                        <h5>Total Revenue</h5>
                        <h2>₹<?= number_format($revenue, 2) ?></h2>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                </div>
            </div>

        </div>

        <div class="table-card">
            
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h4 style="font-weight: 700; color: #1e293b; margin: 0;">System Users</h4>
                
                <form class="search-form m-0" method="GET">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email..." class="form-control">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php 
                        if(mysqli_num_rows($users) > 0) {
                            while($u = mysqli_fetch_assoc($users)){ 
                                
                                // Dynamic Role Badge Logic
                                $roleClass = "bg-secondary";
                                if($u['role'] == 'admin') $roleClass = "bg-danger text-white";
                                if($u['role'] == 'doctor') $roleClass = "bg-primary text-white";
                                if($u['role'] == 'patient') $roleClass = "bg-info text-dark";

                                // Dynamic Status Badge Logic
                                $status = strtolower($u['status']);
                                $statusClass = "bg-secondary";
                                if($status == 'approved' || $status == 'active') $statusClass = "bg-success text-white";
                                if($status == 'pending') $statusClass = "bg-warning text-dark";
                                if($status == 'rejected' || $status == 'locked' || $status == 'inactive') $statusClass = "bg-danger text-white";
                        ?>
                        
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge <?= $roleClass ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td><span class="badge <?= $statusClass ?>"><?= ucfirst($u['status'] ?? 'N/A') ?></span></td>
                        </tr>

                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-4'>No users found matching your search.</td></tr>";
                        }
                        ?>

                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>