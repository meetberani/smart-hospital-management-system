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

if (isset($_POST['save'])) {
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];

    $check = mysqli_query($conn, "SELECT * FROM doctors WHERE user_id='$doctor_id'");

    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE doctors SET start_time='$start_time', end_time='$end_time' WHERE user_id='$doctor_id'");
    } else {
        // Specialization handling might be needed depending on DB, but kept query exact as requested.
        mysqli_query($conn, "INSERT INTO doctors (user_id,start_time,end_time) VALUES('$doctor_id','$start_time','$end_time')");
    }

    $msg = "✅ Working hours updated successfully!";
}

$working = mysqli_fetch_assoc(mysqli_query($conn, "SELECT start_time, end_time FROM doctors WHERE user_id='$doctor_id'"));
$current_start = $working['start_time'] ?? null;
$current_end = $working['end_time'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Working Hours | Doctor Panel</title>

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
            background: white; padding: 20px 30px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); margin-bottom: 35px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #0072ff;
        }
        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; font-size: 1.6rem; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .status-badge {
            background: #e0f2fe; color: #0284c7; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px;
        }

        /* --- FORM CARD --- */
        .work-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            max-width: 600px;
            margin: 0 auto;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Current Schedule Display */
        .current-schedule {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .current-schedule h5 { color: #475569; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; }
        .current-schedule h3 { color: #0072ff; font-weight: 700; margin: 0; }

        /* Custom Alerts */
        .custom-alert {
            padding: 12px 18px; border-radius: 12px; margin-bottom: 25px; font-weight: 500; font-size: 0.95rem; text-align: center;
        }
        .custom-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }

        /* Inputs */
        .form-label { font-weight: 600; color: #1e293b; font-size: 0.95rem; margin-bottom: 8px; }
        .input-group-text { background: #f8fafc; border-right: none; color: #0072ff; border-radius: 12px 0 0 12px; }
        .form-control { border-left: none; border-radius: 0 12px 12px 0; padding: 12px 15px; border-color: #cbd5e1; background: white; color: #334155; transition: 0.3s; box-shadow: none !important; font-weight: 500;}
        
        .input-group:focus-within .input-group-text, .input-group:focus-within .form-control {
            border-color: #0072ff; box-shadow: 0 0 0 4px rgba(0, 114, 255, 0.1) !important;
        }

        /* Button */
        .btn-submit {
            background: linear-gradient(45deg, #0072ff, #00c6ff); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 600; font-size: 1.05rem; transition: 0.4s ease; box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3); width: 100%; margin-top: 15px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(0, 114, 255, 0.4); color: white; }

        .btn-back {
            background: transparent; color: #64748b; border: 2px solid #cbd5e1; padding: 12px 20px; border-radius: 12px; font-weight: 600; transition: 0.3s; width: 100%; display: block; text-align: center; text-decoration: none; margin-top: 15px;
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
        <a class="active" href="#"><i class="fa-solid fa-clock"></i> Working Hours</a>
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
                <h2>Set Working Hours</h2>
                <p>Define your daily availability for patient appointments.</p>
            </div>
            <div>
                <i class="fa-regular fa-clock fa-2x text-primary opacity-50"></i>
            </div>
        </div>

        <div class="work-card">

            <div class="current-schedule">
                <h5><i class="fa-solid fa-business-time me-2"></i>Current Schedule</h5>
                <?php if($current_start && $current_end){ ?>
                    <h3>
                        <?= date("h:i A", strtotime($current_start)) ?> 
                        <span class="text-muted fs-5 mx-2">to</span> 
                        <?= date("h:i A", strtotime($current_end)) ?>
                    </h3>
                <?php } else { ?>
                    <h3 class="text-muted"><i class="fa-solid fa-ban me-2"></i>Not Set</h3>
                <?php } ?>
            </div>

            <?php if($msg != ""){ ?>
                <div class="custom-alert <?= $msgType ?>">
                    <?= $msg ?>
                </div>
            <?php } ?>

            <form method="post">

                <div class="mb-4">
                    <label class="form-label">Consultation Start Time</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-hourglass-start"></i></span>
                        <input type="time" name="start_time" class="form-control" required value="<?= $current_start ?? '' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Consultation End Time</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-hourglass-end"></i></span>
                        <input type="time" name="end_time" class="form-control" required value="<?= $current_end ?? '' ?>">
                    </div>
                </div>

                <button type="submit" name="save" class="btn-submit">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Update Working Hours
                </button>

            </form>

            <a href="dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
            </a>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>