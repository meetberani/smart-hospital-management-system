<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$msg = "";
$msgType = "";

$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id='$doctor_id'");
$total = mysqli_fetch_assoc($res)['total'];

$patients = mysqli_query($conn, "
    SELECT DISTINCT u.id, u.name
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id='$doctor_id' AND a.status='approved'
");

if(isset($_POST['save_history'])){
    $pid = intval($_POST['patient_id']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $insert = mysqli_query($conn, "
        INSERT INTO patient_history(patient_id, doctor_id, notes)
        VALUES('$pid', '$doctor_id', '$notes')
    ");

    if($insert) {
        $msg = "✅ Medical history and prescription saved successfully!";
        $msgType = "success";
    } else {
        $msg = "⚠️ Error saving records.";
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | Smart HMS</title>

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

        /* Status Action Buttons */
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

        .doc-avatar {
            width: 55px; height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0072ff;
            padding: 2px;
            box-shadow: 0 4px 10px rgba(0, 114, 255, 0.2);
        }

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

        /* --- FORM CARD --- */
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            max-width: 800px;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card h3 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Custom Alerts */
        .custom-alert {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .custom-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .custom-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* Form Inputs */
        .form-label { font-weight: 600; color: #475569; font-size: 0.95rem; }
        
        .form-select, .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            transition: 0.3s;
            box-shadow: none !important;
        }
        .form-select:focus, .form-control:focus {
            background: white;
            border-color: #0072ff;
            box-shadow: 0 0 0 4px rgba(0, 114, 255, 0.1) !important;
        }

        textarea.form-control {
            min-height: 140px;
            resize: none;
        }

        .count-box {
            text-align: right;
            font-size: 0.85rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 8px;
            transition: 0.3s;
        }
        .count-box.limit-reached { color: #ef4444; font-weight: 700; }

        /* Button */
        .btn-submit {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: 0.4s ease;
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
            width: 100%;
            margin-top: 20px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 114, 255, 0.4);
            color: white;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-user-doctor me-2"></i> DocPanel</h3>

        <a class="active" href="#"><i class="fa-solid fa-house-medical"></i> Dashboard</a>
        <a href="view_appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
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
                <h2>Doctor's Desk</h2>
                <p>Welcome back! Manage your patients and appointments.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="stat-badge">
                    <i class="fa-solid fa-clipboard-user"></i> <b><?= $total ?></b> Total Appts
                </div>
                <img src="../assets/images/doctor.jpeg" class="doc-avatar" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3774/3774299.png'" alt="Doctor Avatar">
            </div>
        </div>

        <div class="form-card">
            
            <h3><i class="fa-solid fa-notes-medical text-primary"></i> Patient Medical History</h3>
            <p class="text-muted mb-4">Record diagnosis, prescriptions, and consultation notes for your patients.</p>

            <?php if($msg != ""){ ?>
                <div class="custom-alert <?= $msgType ?>">
                    <?= $msg ?>
                </div>
            <?php } ?>

            <form method="post" id="historyForm" onsubmit="return validateNotes()">

                <div class="mb-4">
                    <label class="form-label">Select Patient</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-primary border-end-0"><i class="fa-solid fa-user-injured"></i></span>
                        <select name="patient_id" class="form-select border-start-0" required>
                            <option value="" disabled selected>-- Choose an Approved Patient --</option>
                            <?php 
                            // Reset pointer just in case
                            mysqli_data_seek($patients, 0);
                            while($p = mysqli_fetch_assoc($patients)){ 
                            ?>
                                <option value="<?= $p['id']?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Medical Notes / Prescription</label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        class="form-control" 
                        placeholder="Write diagnosis, symptoms, and prescribed medicines here..." 
                        maxlength="300"
                        required
                    ></textarea>
                </div>
                
                <div class="count-box" id="countBox">
                    <span id="chars">0</span> / 300
                </div>

                <button type="submit" name="save_history" class="btn-submit">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Medical Record
                </button>

            </form>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const textarea = document.getElementById('notes');
            const charCount = document.getElementById('chars');
            const countBox = document.getElementById('countBox');

            textarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCount.textContent = currentLength;

                if (currentLength >= 290) {
                    countBox.classList.add('limit-reached');
                } else {
                    countBox.classList.remove('limit-reached');
                }
            });
        });

        function validateNotes(){
            let notes = document.getElementById("notes").value.trim();
            if(notes.length < 5){
                alert("⚠️ Please enter a detailed medical note.");
                return false;
            }
            return true;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>