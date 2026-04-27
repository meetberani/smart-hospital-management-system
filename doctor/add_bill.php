<?php
session_start();
include("../config/db.php");
require_once("../tcpdf/tcpdf.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$msg = "";
$msgType = "success"; // Default

/* Fetch patients */
$patients_result = mysqli_query($conn,"SELECT id,name FROM users WHERE role='patient' AND status='approved'");
$patients = mysqli_fetch_all($patients_result, MYSQLI_ASSOC);

/* Fetch medicines */
$medicines_result = mysqli_query($conn,"SELECT * FROM medicines WHERE stock>0");
$medicines = mysqli_fetch_all($medicines_result, MYSQLI_ASSOC);

/* Handle bill submission */
if(isset($_POST['add_bill'])){
    $patient_id = intval($_POST['patient_id']);
    $medicine_ids = $_POST['medicine_id'];
    $qtys = $_POST['qty'];

    $total = 0;
    $items = [];

    foreach($medicine_ids as $i => $med_id){
        $med_id = intval($med_id);
        $qty = intval($qtys[$i]);
        $med = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM medicines WHERE id='$med_id'"));

        if($qty > $med['stock']){
            $msg = "⚠️ Not enough stock for ".$med['medicine_name']." (Available: ".$med['stock'].")";
            $msgType = "error";
            break;
        }

        $subtotal = $qty * $med['price'];
        $total += $subtotal;

        $items[] = [
            'id'=>$med_id,
            'name'=>$med['medicine_name'],
            'qty'=>$qty,
            'price'=>$med['price'],
            'subtotal'=>$subtotal
        ];
    }

    if($msg == ""){
        mysqli_query($conn,"INSERT INTO bills(patient_id,doctor_id,total_amount,bill_date)
        VALUES('$patient_id','$doctor_id','$total',CURDATE())");
        $bill_id = mysqli_insert_id($conn);

        foreach($items as $item){
            mysqli_query($conn,"INSERT INTO bill_items(bill_id,medicine_id,qty,price)
            VALUES('$bill_id','".$item['id']."','".$item['qty']."','".$item['price']."')");
            mysqli_query($conn,"UPDATE medicines SET stock=stock-".$item['qty']." WHERE id='".$item['id']."'");
        }

        $pdf = new TCPDF();
        $pdf->AddPage();

        $html="<h2>Hospital Bill</h2><hr>
        Bill ID: $bill_id<br>
        Patient ID: $patient_id<br>
        Doctor ID: $doctor_id<br>
        Date: ".date("Y-m-d")."<br><br>
        <table border='1' cellpadding='4'>
        <tr><th>Medicine</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";

        foreach($items as $it){
            $html.="<tr>
            <td>{$it['name']}</td>
            <td>{$it['qty']}</td>
            <td>₹{$it['price']}</td>
            <td>₹{$it['subtotal']}</td>
            </tr>";
        }

        $html.="<tr><td colspan='3'><b>Total</b></td><td><b>₹$total</b></td></tr></table>";

        $pdf->writeHTML($html);
        $file="bill_$bill_id.pdf";
        $pdf->Output(__DIR__."/../pdf/$file","F");

        mysqli_query($conn,"UPDATE bills SET pdf_file='$file' WHERE id='$bill_id'");
        $msg = "✅ Bill Generated & PDF Created Successfully!";
        $msgType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bill | Doctor Panel</title>

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

        /* --- FORM CARD --- */
        .bill-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.04);
            max-width: 800px;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom Alerts */
        .custom-alert {
            padding: 12px 18px; border-radius: 12px; margin-bottom: 25px; font-weight: 500; font-size: 0.95rem;
        }
        .custom-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .custom-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* Inputs */
        .form-label { font-weight: 600; color: #475569; font-size: 0.95rem; margin-bottom: 8px; }
        .form-select, .form-control {
            border-radius: 12px; padding: 12px 15px; border: 1px solid #cbd5e1; background: white; color: #334155; transition: 0.3s; box-shadow: none !important;
        }
        .input-group-text { background: #f8fafc; border-right: none; color: #0072ff; border-radius: 12px 0 0 12px; }
        .form-control { border-left: none; border-radius: 0 12px 12px 0; }
        .form-select { border-left: none; border-radius: 0 12px 12px 0; }
        
        .input-group:focus-within .input-group-text, .input-group:focus-within .form-control, .input-group:focus-within .form-select {
            border-color: #0072ff; box-shadow: 0 0 0 4px rgba(0, 114, 255, 0.1) !important;
        }

        /* Med Row Animation */
        .medicine-row { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Buttons */
        .btn-sec {
            background: white; color: #0072ff; border: 2px dashed #0072ff; padding: 12px 20px; border-radius: 12px; font-weight: 600; transition: 0.3s; width: 100%; margin-bottom: 20px;
        }
        .btn-sec:hover { background: #eff6ff; }

        .btn-main {
            background: linear-gradient(45deg, #0072ff, #00c6ff); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 600; font-size: 1.05rem; transition: 0.4s ease; box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3); width: 100%;
        }
        .btn-main:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(0, 114, 255, 0.4); color: white; }

        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main { margin-left: 0; } }
    </style>

    <script>
        // Use PHP to safely pass medicines to JS
        let medicinesList = <?php echo json_encode($medicines); ?>;

        function addMedicineRow(){
            const container = document.getElementById("medicines_container");
            const row = document.createElement("div");
            row.className = "row g-3 mb-3 medicine-row align-items-center";

            let opt = '<option value="" disabled selected>-- Select Medicine --</option>';
            medicinesList.forEach(m => {
                opt += `<option value="${m.id}">${m.medicine_name} (₹${m.price} | Stock: ${m.stock})</option>`;
            });

            // Modern Bootstrap Grid layout for dynamically added rows
            row.innerHTML = `
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-pills"></i></span>
                        <select name="medicine_id[]" class="form-select" required>${opt}</select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
                        <input type="number" name="qty[]" min="1" class="form-control" placeholder="Quantity" required>
                    </div>
                </div>
            `;
            
            container.appendChild(row);
        }
    </script>
</head>
<body>

    <div class="sidebar">
        <h3><i class="fa-solid fa-user-doctor me-2"></i> DocPanel</h3>

        <a href="dashboard.php"><i class="fa-solid fa-house-medical"></i> Dashboard</a>
        <a href="view_appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
        <a class="active" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Add Bill</a>
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
                <h2>Generate Patient Bill</h2>
                <p>Create an invoice, deduct stock, and generate a PDF receipt.</p>
            </div>
            <div>
                <i class="fa-solid fa-file-pdf fa-2x text-danger opacity-75"></i>
            </div>
        </div>

        <div class="bill-card">

            <?php if($msg != ""){ ?>
                <div class="custom-alert <?= $msgType ?>">
                    <?= $msg ?>
                </div>
            <?php } ?>

            <form method="post">

                <div class="mb-4">
                    <label class="form-label">Select Patient</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-injured"></i></span>
                        <select name="patient_id" class="form-select" required>
                            <option value="" disabled selected>-- Choose Approved Patient --</option>
                            <?php foreach($patients as $p){ ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4 text-muted">

                <label class="form-label mb-3">Add Medicines & Quantities</label>

                <div id="medicines_container">
                    <div class="row g-3 mb-3 medicine-row align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-pills"></i></span>
                                <select name="medicine_id[]" class="form-select" required>
                                    <option value="" disabled selected>-- Select Medicine --</option>
                                    <?php foreach($medicines as $m){ ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['medicine_name']) ?> (₹<?= $m['price'] ?> | Stock: <?= $m['stock'] ?>)</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
                                <input type="number" name="qty[]" min="1" class="form-control" placeholder="Quantity" required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-sec" onclick="addMedicineRow()">
                    <i class="fa-solid fa-plus me-2"></i> Add Another Medicine
                </button>

                <button type="submit" name="add_bill" class="btn-main">
                    <i class="fa-solid fa-file-invoice me-2"></i> Generate Bill & PDF
                </button>

            </form>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>