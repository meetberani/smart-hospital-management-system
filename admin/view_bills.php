<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("location:../login.php");
    exit;
}

$bills = mysqli_query($conn,"
SELECT b.*, 
p.name AS patient_name,
d.name AS doctor_name
FROM bills b
JOIN users p ON b.patient_id=p.id
JOIN users d ON b.doctor_id=d.id
ORDER BY b.id DESC
");

$total_bills = mysqli_num_rows($bills);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Bills | Admin Panel</title>

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
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #10b981; /* Emerald Green for Finance */
        }

        .header-box h2 { margin: 0; font-weight: 700; color: #1e293b; }
        .header-box p { margin: 0; color: #64748b; font-size: 0.95rem; }

        .badge-count {
            background: #d1fae5;
            color: #059669;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* --- SEARCH BAR --- */
        .search-container {
            position: relative;
            max-width: 400px;
            margin-bottom: 25px;
        }

        .search-container i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        #search {
            width: 100%;
            padding: 14px 20px 14px 45px;
            border: 1px solid #cbd5e1;
            border-radius: 15px;
            background: white;
            font-size: 0.95rem;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }

        #search:focus {
            outline: none;
            border-color: #0072ff;
            box-shadow: 0 0 0 4px rgba(0, 114, 255, 0.1);
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

        .icon-patient { color: #10b981; margin-right: 8px; }
        .icon-doctor { color: #0072ff; margin-right: 8px; }

        .amount-text {
            color: #059669;
            font-weight: 700;
            font-size: 1.1rem;
            background: #d1fae5;
            padding: 4px 10px;
            border-radius: 8px;
        }

        /* --- DOWNLOAD BUTTON --- */
        .btn-download {
            background: linear-gradient(45deg, #ef4444, #dc2626); /* Red for PDF */
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s ease;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
            color: white;
            background: linear-gradient(45deg, #dc2626, #b91c1c);
        }

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
        <h3><i class="fa-solid fa-shield-halved me-2"></i> Admin</h3>

        <a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="approve_doctor.php"><i class="fa-solid fa-user-doctor"></i> Approve Doctors</a>
        <a href="approve_patient.php"><i class="fa-solid fa-user-check"></i> Approve Patients</a>
        <a href="manage_medicine.php"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a class="active" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a href="emergency.php"><i class="fa-solid fa-truck-fast"></i> Emergency</a>
        <a href="feedback.php"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Hospital Bills</h2>
                <p>Manage and view all generated patient invoices.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-solid fa-receipt me-1"></i> <?= $total_bills ?> Records
                </span>
            </div>
        </div>

        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search" placeholder="Search by patient or doctor name...">
        </div>

        <div class="table-card">
            
            <?php if($total_bills > 0){ ?>
                
                <div class="table-responsive">
                    <table class="table table-borderless" id="billTable">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Patient Name</th>
                                <th>Consulting Doctor</th>
                                <th>Total Amount</th>
                                <th>Billing Date</th>
                                <th>Invoice (PDF)</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while($b = mysqli_fetch_assoc($bills)){ ?>

                            <tr>
                                <td class="fw-bold text-muted">#<?= $b['id'] ?></td>
                                
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-bed-pulse icon-patient"></i> 
                                    <?= htmlspecialchars($b['patient_name']) ?>
                                </td>
                                
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-user-doctor icon-doctor"></i> 
                                    Dr. <?= htmlspecialchars($b['doctor_name']) ?>
                                </td>
                                
                                <td>
                                    <span class="amount-text">₹<?= number_format($b['total_amount'], 2) ?></span>
                                </td>
                                
                                <td class="text-muted fw-medium">
                                    <i class="fa-regular fa-calendar me-1"></i> 
                                    <?= date('d M, Y', strtotime($b['bill_date'])) ?>
                                </td>
                                
                                <td>
                                    <?php if($b['pdf_file'] != ""){ ?>
                                        <a class="btn-download" target="_blank" href="../pdf/<?= $b['pdf_file'] ?>">
                                            <i class="fa-solid fa-file-pdf"></i> Download
                                        </a>
                                    <?php } else { ?>
                                        <span class="badge bg-light text-muted border px-3 py-2">Not Available</span>
                                    <?php } ?>
                                </td>
                            </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="empty-state">
                    <i class="fa-solid fa-file-invoice"></i>
                    <h4>No Bills Generated</h4>
                    <p>There are currently no billing records in the system.</p>
                </div>

            <?php } ?>

        </div>

    </div>

    <script src="../assets/js/bills.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('search').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#billTable tbody tr');
            
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if(text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>