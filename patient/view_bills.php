<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['user_id'])){
    header("location:../login.php");
    exit;
}

$pid = $_SESSION['user_id'];

$bills = mysqli_query($conn,"SELECT * FROM bills WHERE patient_id='$pid' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medical Bills | Smart HMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            min-height: 100vh;
            padding-bottom: 50px;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Animated Shapes */
        .shape {
            position: absolute;
            filter: blur(70px);
            z-index: -1;
            opacity: 0.4;
        }
        .shape-1 { width: 400px; height: 400px; background: #0072ff; top: -50px; left: -100px; border-radius: 50%; }
        .shape-2 { width: 350px; height: 350px; background: #10b981; bottom: 0; right: -50px; border-radius: 50%; }

        /* Premium Header Banner */
        .header-banner {
            background: rgba(25, 118, 210, 0.9);
            backdrop-filter: blur(10px);
            color: white;
            padding: 30px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 15px 35px rgba(25, 118, 210, 0.2);
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease;
            position: relative;
            overflow: hidden;
        }

        .header-banner::after {
            content: '';
            position: absolute;
            top: -50%; right: -20%;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-banner img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.8);
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            z-index: 1;
        }

        .header-banner h4 { margin: 0; font-weight: 700; font-size: 1.8rem; letter-spacing: 0.5px; z-index: 1;}
        .header-banner small { color: #e2e8f0; font-size: 1rem; z-index: 1;}

        /* Bill Card UI */
        .bill-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            animation: slideUp 0.8s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bill-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .bill-header h5 { margin: 0; color: #1e293b; font-weight: 700; }
        .bill-icon { font-size: 24px; color: #0072ff; background: rgba(0, 114, 255, 0.1); padding: 10px; border-radius: 12px; }

        .amount-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin: 15px 0;
            border: 1px dashed #cbd5e1;
        }

        .amount-box h4 { margin: 0; color: #10b981; font-weight: 700; font-size: 1.6rem; }
        .amount-box span { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;}

        /* Custom Button */
        .btn-download {
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            padding: 12px 15px;
            border-radius: 12px;
            text-decoration: none;
            display: block;
            text-align: center;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            margin-top: auto; /* Pushes button to bottom */
        }

        .btn-download:hover {
            background: linear-gradient(45deg, #059669, #047857);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-download i { transition: 0.3s; }
        .btn-download:hover i { transform: translateY(3px); }

        .btn-back {
            background: white;
            color: #1e293b;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            border: 1px solid #e2e8f0;
        }

        .btn-back:hover {
            background: #f8fafc;
            color: #0072ff;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .empty-state img {
            width: 120px;
            opacity: 0.8;
            margin-bottom: 20px;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .empty-state h5 { color: #334155; font-weight: 600; font-size: 1.4rem;}
        .empty-state p { color: #64748b; }

    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <div class="container mt-5">

        <div class="header-banner">
            <img src="/hospital/assets/images/bill.jpeg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2936/2936746.png'" alt="Bill Icon">
            <div>
                <h4>My Medical Bills</h4>
                <small><i class="fa-solid fa-cloud-arrow-down me-1"></i> Access and download your invoices anytime</small>
            </div>
        </div>

        <div class="row g-4">

            <?php if(mysqli_num_rows($bills) > 0){ ?>

                <?php while($b = mysqli_fetch_assoc($bills)){ ?>

                <div class="col-lg-4 col-md-6">
                    <div class="bill-card">
                        
                        <div class="bill-header">
                            <h5><i class="fa-solid fa-hashtag text-muted me-1"></i><?= $b['id'] ?></h5>
                            <i class="fa-solid fa-file-invoice-dollar bill-icon"></i>
                        </div>

                        <p class="text-muted mb-1"><i class="fa-solid fa-notes-medical me-2"></i>Hospital Charges</p>
                        
                        <div class="amount-box">
                            <span>Total Amount</span>
                            <h4>₹<?= number_format($b['total_amount'], 2) ?></h4>
                        </div>

                        <a class="btn-download" href="/hospital/pdf/<?= $b['pdf_file'] ?>" target="_blank">
                            <i class="fa-solid fa-download me-2"></i> Download PDF
                        </a>

                    </div>
                </div>

                <?php } ?>

            <?php } else { ?>

                <div class="col-12">
                    <div class="empty-state">
                        <img src="/hospital/assets/images/booking.jpeg" onerror="this.src='https://cdn-icons-png.flaticon.com/512/7486/7486747.png'" alt="No Bills">
                        <h5>No Invoices Found</h5>
                        <p>You do not have any generated medical bills at the moment.</p>
                    </div>
                </div>

            <?php } ?>

        </div>

        <div class="mt-5 text-center">
            <a href="dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>