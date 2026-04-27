<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Our Team | Smart HMS</title>

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
            min-height: 100vh;
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0284c7, #0d9488);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            padding: 40px 0;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .contact-box {
            max-width: 1100px;
            width: 90%;
            text-align: center;
            z-index: 10;
        }

        .title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
            text-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.7;
            margin-bottom: 60px;
            font-weight: 300;
        }

        /* Team Grid */
        .team-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
        }

        /* Team Card */
        .team-card {
            background: rgba(255, 255, 255, 0.07);
            padding: 40px 30px;
            border-radius: 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            animation: float 6s ease-in-out infinite;
        }

        .team-card:nth-child(even) {
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* Glow Effect on Hover */
        .team-card::before {
            content: "";
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(0, 255, 213, 0.2), transparent 70%);
            top: -25%;
            left: -25%;
            opacity: 0;
            transition: 0.6s;
            pointer-events: none;
        }

        .team-card:hover::before {
            opacity: 1;
        }

        .team-card:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(0, 255, 213, 0.4);
            transform: scale(1.05) translateY(-20px) !important;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }

        /* Icon Styling */
        .dev-icon {
            font-size: 45px;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #00ffd5, #0072ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .team-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(0, 255, 213, 0.1);
            color: #00ffd5;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid rgba(0, 255, 213, 0.2);
        }

        /* Email Section */
        .email-container {
            margin-top: 70px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .email-box {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            padding: 18px 40px;
            border-radius: 50px;
            background: linear-gradient(45deg, #0072ff, #00ffd5);
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(0, 114, 255, 0.4);
            transition: 0.4s;
        }

        .email-box:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0, 114, 255, 0.6);
            color: white;
        }

        .back-home {
            display: block;
            margin-top: 30px;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            transition: 0.3s;
        }

        .back-home:hover {
            color: #00ffd5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .title { font-size: 2.2rem; }
            .team-container { grid-template-columns: 1fr; width: 80%; margin: auto; }
        }
    </style>
</head>

<body>

    <div class="contact-box">
        <h2 class="title"><i class="fa-solid fa-code me-2 text-info"></i> Meet Our Team</h2>
        <p class="subtitle">The Creative Minds Behind Smart HMS</p>

        <div class="team-container">

            <div class="team-card">
                <i class="fa-solid fa-user-gear dev-icon"></i>
                <h3>Berani Meet</h3>
                <span class="role-badge">System Developer</span>
            </div>

            <div class="team-card">
                <i class="fa-solid fa-palette dev-icon"></i>
                <h3>Jeel Bhanderi</h3>
                <span class="role-badge">Frontend Designer</span>
            </div>

            <div class="team-card">
                <i class="fa-solid fa-database dev-icon"></i>
                <h3>Smit Bhesaniya</h3>
                <span class="role-badge">Backend Developer</span>
            </div>

            <div class="team-card">
                <i class="fa-solid fa-server dev-icon"></i>
                <h3>Rathod Aryan</h3>
                <span class="role-badge">Database Manager</span>
            </div>

        </div>

        <div class="email-container">
            <a href="mailto:hospital@gmail.com" class="email-box">
                <i class="fa-solid fa-envelope-open-text"></i> hospital@gmail.com
            </a>
            <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left me-2"></i> Back to Home</a>
        </div>
    </div>

</body>
</html>