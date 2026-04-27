<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Smart HMS | Modern Healthcare Solution</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            color: #334155;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* --- HERO SECTION --- */
        .about-hero {
            padding: 100px 20px 60px;
            text-align: center;
            background: white;
            border-radius: 0 0 50px 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            animation: fadeInDown 1s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .about-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .about-text {
            max-width: 850px;
            margin: auto;
            font-size: 1.15rem;
            color: #64748b;
            font-weight: 400;
        }

        /* --- FEATURES SECTION --- */
        .features-section {
            padding: 80px 0;
        }

        .section-tag {
            text-align: center;
            font-weight: 700;
            color: #0072ff;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 40px;
            display: block;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 15px 35px rgba(0,0,0,0.03);
            transition: all 0.4s ease;
            text-align: center;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-12px);
            background: white;
            box-shadow: 0 20px 45px rgba(0, 114, 255, 0.1);
            border-color: #0072ff;
        }

        .feature-icon {
            font-size: 45px;
            margin-bottom: 20px;
            color: #0072ff;
            display: inline-block;
            transition: 0.3s;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h4 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #64748b;
            font-size: 0.95rem;
        }

        /* --- TEAM SECTION --- */
        .team-section {
            padding: 60px 0 100px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50px;
            margin: 0 20px;
        }

        .team-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            transition: 0.4s;
            text-align: center;
            border-bottom: 4px solid #0072ff;
        }

        .team-card:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .team-card h5 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .team-card p {
            color: #0072ff;
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        /* --- FOOTER --- */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 40px 20px;
            text-align: center;
            border-radius: 50px 50px 0 0;
        }

        footer strong {
            color: #00ffd5;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: white;
            text-decoration: none;
            background: #0072ff;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 114, 255, 0.3);
        }

    </style>
</head>

<body>

    <header class="about-hero">
        <div class="container">
            <h1 class="about-title"><i class="fa-solid fa-hospital-user me-2"></i> About Smart HMS</h1>
            <p class="about-text">
                Smart Hospital Management System is an advanced digital ecosystem engineered to redefine healthcare efficiency. 
                We bridge the gap between patients, doctors, and administration through seamless automation and secure data management.
            </p>
            <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left me-2"></i> Back to Home</a>
        </div>
    </header>

    <section class="container features-section">
        <span class="section-tag">Key Capabilities</span>
        <div class="row g-4">

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-user-doctor feature-icon"></i>
                    <h4>Doctor Desk</h4>
                    <p>Centralized management of specialist details, schedules, and verified professional status.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-file-medical feature-icon"></i>
                    <h4>Digital Records</h4>
                    <p>Instant access to patient medical history, prescriptions, and historical treatment logs.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-calendar-check feature-icon"></i>
                    <h4>Smart Booking</h4>
                    <p>Real-time appointment scheduling that eliminates waiting rooms and manual errors.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-file-invoice-dollar feature-icon"></i>
                    <h4>Automated Billing</h4>
                    <p>One-click PDF invoice generation with transparent medication and consultation pricing.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-truck-medical feature-icon"></i>
                    <h4>SOS Alerts</h4>
                    <p>Emergency alert system to notify medical staff instantly during critical patient situations.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-shield-halved feature-icon"></i>
                    <h4>Bank-Grade Security</h4>
                    <p>Role-based access control ensuring patient data remains confidential and encrypted.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="team-section container">
        <span class="section-tag">The Development Team</span>
        <div class="row g-4">

            <div class="col-md-3">
                <div class="team-card">
                    <h5>Berani Meet</h5>
                    <p>System Developer</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="team-card">
                    <h5>Jeel Bhanderi</h5>
                    <p>Frontend Designer</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="team-card">
                    <h5>Smit Bhesaniya</h5>
                    <p>Backend Developer</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="team-card">
                    <h5>Rathod Aryan</h5>
                    <p>Database Manager</p>
                </div>
            </div>

        </div>
    </section>

    <footer>
        <div class="container">
            <p>© <?= date("Y") ?> <strong>Smart HMS</strong> | All Rights Reserved.</p>
            <p class="small mt-2">Designed for the future of healthcare.</p>
        </div>
    </footer>

</body>
</html>