<?php
include("config/db.php");

// CHECK DB CONNECTION
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// ================= VISITOR COUNTER =================
mysqli_query($conn, "UPDATE visitor_count SET total_visits = total_visits + 1 WHERE id = 1");

$result = mysqli_query($conn, "SELECT total_visits FROM visitor_count WHERE id = 1");
$row = mysqli_fetch_assoc($result);
$visits = $row['total_visits'] ?? 0;


// ================= REAL COUNTS =================

// Doctors count
$docQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors");
$docData = mysqli_fetch_assoc($docQ);
$totalDoctors = $docData['total'] ?? 0;

// Patients count (FIXED)
$patQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM patient_info");
$patData = mysqli_fetch_assoc($patQ);
$totalPatients = $patData['total'] ?? 0;

// Medicines count
$medQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM medicines");
$medData = mysqli_fetch_assoc($medQ);
$totalMedicines = $medData['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart HMS | AI Powered Healthcare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6; /* Soft modern background */
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .custom-nav {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 24px;
            letter-spacing: 1px;
            color: #ffffff !important;
        }

        .navbar-brand span {
            color: #00ffd5;
        }

        .nav-link {
            color: #e2e8f0 !important;
            font-weight: 500;
            margin: 0 10px;
            transition: 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #00ffd5;
            transition: 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #ffffff !important;
        }

        .btn-login {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            border: none;
            color: white;
            padding: 8px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 114, 255, 0.5);
            color: white;
        }

        /* Hero & Slider Section */
        .slider-wrapper {
            position: relative;
        }

        .slider-img {
            height: 80vh;
            object-fit: cover;
            filter: brightness(40%);
        }

        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-align: center;
            z-index: 10;
            width: 100%;
            animation: fadeInUp 1.5s ease-out;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 700;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
            margin-bottom: 15px;
        }

        .hero-content p {
            font-size: 1.5rem;
            font-weight: 300;
            color: #cbd5e1;
            margin-bottom: 30px;
        }

        /* Stats Boxes */
        .stats-section {
            margin-top: -60px;
            position: relative;
            z-index: 20;
        }

        .ai-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.4);
            transition: all 0.4s ease;
            text-align: center;
        }

        .ai-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }

        .ai-box i {
            font-size: 40px;
            margin-bottom: 15px;
            background: -webkit-linear-gradient(#0072ff, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ai-box h5 {
            color: #64748b;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .ai-box h2 {
            color: #0f172a;
            font-weight: 700;
            margin: 0;
        }

        /* Module Cards */
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            color: #1e293b;
        }

        .module-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            transition: 0.4s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-bottom: 5px solid transparent;
            height: 100%;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .module-card.admin:hover { border-color: #ef4444; }
        .module-card.doctor:hover { border-color: #10b981; }
        .module-card.patient:hover { border-color: #3b82f6; }

        .module-icon {
            font-size: 50px;
            margin-bottom: 20px;
        }

        .module-card.admin .module-icon { color: #ef4444; }
        .module-card.doctor .module-icon { color: #10b981; }
        .module-card.patient .module-icon { color: #3b82f6; }

        .module-card h4 {
            font-weight: 600;
            color: #334155;
        }

        .module-card p {
            color: #64748b;
            font-size: 0.95rem;
        }

        /* Chatbot CSS */
        #chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 114, 255, 0.4);
            transition: 0.3s;
            z-index: 1000;
        }

        #chat-btn:hover {
            transform: scale(1.1);
        }

        #chat-container {
            display: none;
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            z-index: 1000;
            border: 1px solid #e2e8f0;
            animation: scaleUp 0.3s ease;
        }

        .chat-header {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header i.close-btn {
            cursor: pointer;
            font-size: 20px;
        }

        #chat-body {
            height: 350px;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .chat-msg {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .chat-msg.user {
            background: #0072ff;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }

        .chat-msg.ai {
            background: #e2e8f0;
            color: #1e293b;
            align-self: flex-start;
            border-bottom-left-radius: 2px;
        }

        .chat-footer {
            display: flex;
            padding: 15px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        .chat-footer input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            outline: none;
            transition: 0.3s;
        }

        .chat-footer input:focus {
            border-color: #0072ff;
            box-shadow: 0 0 0 3px rgba(0,114,255,0.1);
        }

        .chat-footer button {
            background: #0072ff;
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-left: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .chat-footer button:hover {
            background: #005bb5;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }

        footer p {
            margin: 0;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translate(-50%, -40%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }
        
        @keyframes scaleUp {
            from { opacity: 0; transform: scale(0.8) translateY(20px); transform-origin: bottom right; }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Responsive Hero */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.5rem; }
            .stats-section { margin-top: 20px; }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top custom-nav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">🏥 Smart<span>HMS</span></a>
            
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fa fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a class="btn btn-login" href="auth.php">
                            <i class="fa fa-user me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="slider-wrapper">
        <div id="slider" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="./assets/images/slide1.jpeg" class="d-block w-100 slider-img" alt="Hospital View 1">
                </div>
                <div class="carousel-item">
                    <img src="./assets/images/slide2.jpeg" class="d-block w-100 slider-img" alt="Hospital View 2">
                </div>
                <div class="carousel-item">
                    <img src="./assets/images/slide3.jpeg" class="d-block w-100 slider-img" alt="Hospital View 3">
                </div>
            </div>
        </div>

        <div class="hero-content">
            <h1>Smart Hospital System</h1>
            <p>AI-Powered Healthcare for a Better Tomorrow</p>
            <a href="#modules" class="btn btn-login btn-lg px-4 py-2">Explore Services</a>
        </div>
    </div>

    <div class="container stats-section">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="ai-box">
                    <i class="fa-solid fa-user-doctor"></i>
                    <h5>Doctors</h5>
                    <h2><?= $totalDoctors ?>+</h2>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="ai-box">
                    <i class="fa-solid fa-bed-pulse"></i>
                    <h5>Patients</h5>
                    <h2><?= $totalPatients ?>+</h2>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="ai-box">
                    <i class="fa-solid fa-pills"></i>
                    <h5>Medicines</h5>
                    <h2><?= $totalMedicines ?>+</h2>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="ai-box">
                    <i class="fa-regular fa-eye"></i>
                    <h5>Visitors</h5>
                    <h2><?= $visits ?></h2>
                </div>
            </div>
        </div>
    </div>

    <section id="modules" class="container mt-5 pt-5">
        <h2 class="section-title">Core Modules</h2>
        <div class="row text-center g-4">
            
            <div class="col-md-4">
                <div class="module-card admin">
                    <i class="fa-solid fa-user-shield module-icon"></i>
                    <h4>Admin Panel</h4>
                    <p>Complete control over the system, staff management, and billing operations.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="module-card doctor">
                    <i class="fa-solid fa-stethoscope module-icon"></i>
                    <h4>Doctor Desk</h4>
                    <p>Effortlessly manage patient appointments, histories, and e-prescriptions.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="module-card patient">
                    <i class="fa-solid fa-wheelchair module-icon"></i>
                    <h4>Patient Care</h4>
                    <p>Hassle-free online booking, medical reports access, and quick support.</p>
                </div>
            </div>

        </div>
    </section>

    <footer>
        <div class="container">
            <p>© <?= date("Y") ?> <strong>Smart HMS</strong> | All Rights Reserved.</p>
        </div>
    </footer>

    <div id="chat-btn" onclick="toggleChat()">
        <i class="fa-solid fa-message"></i>
    </div>

    <div id="chat-container">
        <div class="chat-header">
            <span><i class="fa-solid fa-robot me-2"></i> HMS Assistant</span>
            <i class="fa-solid fa-xmark close-btn" onclick="toggleChat()"></i>
        </div>
        
        <div id="chat-body">
            <div class="chat-msg ai">Hi there! 👋 How can I help you with Smart HMS today?</div>
        </div>
        
        <div class="chat-footer">
            <input id="msg" type="text" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMsg()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Chat Toggle Logic
        function toggleChat() {
            let chat = document.getElementById("chat-container");
            chat.style.display = (chat.style.display === "none" || chat.style.display === "") ? "block" : "none";
        }

        // Allow pressing Enter to send message
        function handleKeyPress(e) {
            if(e.key === 'Enter'){
                sendMsg();
            }
        }

        // Send Message Logic
        function sendMsg() {
            let inputField = document.getElementById("msg");
            let msg = inputField.value.trim();

            if (msg === "") return;

            let body = document.getElementById("chat-body");

            // Append User Message
            let userHTML = `<div class="chat-msg user">${msg}</div>`;
            body.innerHTML += userHTML;
            
            // Scroll to bottom
            body.scrollTop = body.scrollHeight;

            // Call Backend API
            fetch("chatbot.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "message=" + encodeURIComponent(msg)
            })
            .then(res => res.text())
            .then(data => {
                // Append AI Response
                let aiHTML = `<div class="chat-msg ai">${data}</div>`;
                body.innerHTML += aiHTML;
                body.scrollTop = body.scrollHeight;
            })
            .catch(err => {
                let aiHTML = `<div class="chat-msg ai" style="color:red;">Error connecting to server.</div>`;
                body.innerHTML += aiHTML;
                body.scrollTop = body.scrollHeight;
            });

            // Clear Input
            inputField.value = "";
        }
    </script>

</body>
</html>