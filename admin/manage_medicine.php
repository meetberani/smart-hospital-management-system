<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$msg = "";

if(isset($_POST['add_medicine'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $qty = mysqli_real_escape_string($conn, $_POST['quantity']);

    mysqli_query($conn,"INSERT INTO medicines(medicine_name, price, stock) VALUES('$name', '$price', '$qty')");

    $msg = "✅ Medicine Added Successfully!";
}

$medicines = mysqli_query($conn,"SELECT * FROM medicines ORDER BY id DESC");
$total_meds = mysqli_num_rows($medicines);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines | Admin Panel</title>

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
            border-left: 5px solid #8b5cf6; /* Purple for Medicine */
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

        /* --- FORM CARD --- */
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            margin-bottom: 35px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card h4 { color: #1e293b; font-weight: 700; margin-bottom: 20px; }

        /* Custom Inputs */
        .input-group-text {
            background: #f8fafc;
            border-right: none;
            color: #8b5cf6;
            border-radius: 12px 0 0 12px;
        }
        .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px 15px;
            box-shadow: none !important;
            transition: 0.3s;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 0.25rem rgba(139, 92, 246, 0.1) !important;
        }

        .btn-add {
            background: linear-gradient(45deg, #8b5cf6, #6d28d9);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
            height: 100%;
            width: 100%;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
            color: white;
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
        }
        .table tbody td {
            padding: 15px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:hover { background: #f8fafc; }

        .med-icon {
            color: #8b5cf6;
            background: rgba(139, 92, 246, 0.1);
            padding: 10px;
            border-radius: 10px;
            margin-right: 15px;
        }

        /* Dynamic Stock Badges */
        .stock-badge { padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
        .stock-in { background: #d1fae5; color: #059669; }
        .stock-low { background: #fef3c7; color: #d97706; }
        .stock-out { background: #fee2e2; color: #dc2626; }

        .custom-alert {
            background: rgba(16, 185, 129, 0.1);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        /* --- EMPTY STATE --- */
        .empty-state { text-align: center; padding: 50px 20px; }
        .empty-state i { font-size: 50px; color: #8b5cf6; opacity: 0.5; margin-bottom: 15px; }
        .empty-state h5 { color: #1e293b; font-weight: 700; }

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
        <a class="active" href="#"><i class="fa-solid fa-pills"></i> Medicines</a>
        <a href="view_bills.php"><i class="fa-solid fa-file-invoice-dollar"></i> Bills</a>
        <a href="emergency.php"><i class="fa-solid fa-truck-fast"></i> Emergency</a>
        <a href="feedback.php"><i class="fa-solid fa-comments"></i> Feedback</a>
        
        <a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">

        <div class="header-box">
            <div>
                <h2>Medicine Inventory</h2>
                <p>Add and manage hospital pharmacy stock.</p>
            </div>
            <div>
                <span class="badge-count">
                    <i class="fa-solid fa-box-open me-1"></i> <?= $total_meds ?> Items
                </span>
            </div>
        </div>

        <?php if($msg != ""){ ?>
            <div class="custom-alert">
                <?= $msg ?>
            </div>
        <?php } ?>

        <div class="form-card">
            <h4><i class="fa-solid fa-plus-circle text-muted me-2"></i> Add New Medicine</h4>
            
            <form method="post">
                <div class="row g-3">
                    
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-capsules"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="Medicine Name" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-cubes"></i></span>
                            <input type="number" name="quantity" class="form-control" placeholder="Stock Qty" required>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" name="add_medicine" class="btn-add">
                            Add Item
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <div class="table-card">
            <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 20px;">Current Stock List</h4>
            
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Unit Price</th>
                            <th>Stock Status</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if($total_meds > 0){ ?>

                            <?php while($m = mysqli_fetch_assoc($medicines)){ 
                                
                                // Smart Stock UI Logic
                                $stockQty = (int)$m['stock'];
                                if($stockQty == 0) {
                                    $stockClass = "stock-out";
                                    $stockText = "0 (Out of Stock)";
                                } elseif ($stockQty <= 10) {
                                    $stockClass = "stock-low";
                                    $stockText = $stockQty . " (Low)";
                                } else {
                                    $stockClass = "stock-in";
                                    $stockText = $stockQty . " Units";
                                }
                            ?>

                            <tr>
                                <td class="text-muted fw-bold">#<?= $m['id'] ?></td>
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-prescription-bottle-medical med-icon"></i>
                                    <?= htmlspecialchars($m['medicine_name']) ?>
                                </td>
                                <td><span class="fw-semibold text-success">₹<?= number_format($m['price'], 2) ?></span></td>
                                <td>
                                    <span class="stock-badge <?= $stockClass ?>">
                                        <?= $stockText ?>
                                    </span>
                                </td>
                            </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-box-open"></i>
                                        <h5>Inventory is Empty</h5>
                                        <p class="text-muted">You haven't added any medicines to the stock yet.</p>
                                    </div>
                                </td>
                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>