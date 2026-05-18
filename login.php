<?php
session_start();
include('db.php');

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check staff table
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    // If not found, check patients table
    if(mysqli_num_rows($result) == 0) {
        $sql = "SELECT * FROM patients WHERE national_id='$username' AND password='$password'";
        $result = mysqli_query($conn, $sql);
    }

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $username;
        $_SESSION['role'] = isset($row['role']) ? $row['role'] : 'patient';
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Smart Health Passport</title>
    <style>
        :root { --primary: #005a8d; --accent: #ffcc00; --sidebar: #1a1a2e; --success: #218838; }
        
        body { 
            background: #f4f7f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
            overflow: hidden; 
        }

        /* HEADER & NAVIGATION */
        .header-branding { background: var(--primary); color: white; padding: 12px; text-align: center; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 10; }
        .header-branding h1 { margin: 0; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .nav-links { position: absolute; right: 30px; top: 18px; display: flex; gap: 10px; }
        .nav-links a { color: white; text-decoration: none; font-size: 0.9rem; font-weight: bold; cursor: pointer; border: 1px solid white; padding: 5px 12px; border-radius: 20px; transition: 0.3s; }
        .nav-links a:hover { background: white; color: var(--primary); }

        /* MOVING MARQUEE */
        .marquee-bar { background: white; border-bottom: 1px solid #ddd; padding: 8px 0; z-index: 5; }

        /* LOGIN WRAPPER */
        .login-wrapper { 
            flex: 1; 
            display: flex; 
            flex-direction: row; 
            max-width: 950px; 
            max-height: 480px; 
            margin: auto; 
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
        }
        
        .hospital-hero { position: relative; width: 45%; height: 100%; }
        .hospital-hero img { width: 100%; height: 100%; object-fit: cover; }
        .hero-overlay { 
            position: absolute; bottom: 0; left: 0; right: 0; 
            background: linear-gradient(transparent, rgba(0, 51, 153, 0.9)); 
            color: white; padding: 25px; 
        }
        .hero-overlay h2 { margin: 0; font-size: 1.4rem; }

        .login-card-container { width: 55%; padding: 40px; display: flex; flex-direction: column; justify-content: center; }
        .login-card-container h2 { color: #2c3e50; margin-bottom: 25px; text-align: center; font-weight: 400; margin-top: 0; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; color: #5a6a7a; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; }
        
        .login-btn { width: 100%; padding: 14px; background: var(--success); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: 0.2s; }
        .login-btn:hover { background: #1e7e34; }

        /* MODAL STYLES */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75); backdrop-filter: blur(3px); }
        .modal-content { 
            background: white; margin: 4% auto; padding: 35px; width: 85%; max-width: 800px; 
            border-radius: 15px; position: relative; max-height: 85vh; overflow-y: auto;
        }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 30px; cursor: pointer; color: #aaa; }
        
        .modal-section-title { color: var(--primary); border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-top: 0; }
        .about-grid, .faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .item h4 { color: var(--primary); margin: 0 0 8px 0; border-left: 4px solid var(--accent); padding-left: 10px; }
        .item p { font-size: 0.9rem; color: #444; line-height: 1.5; margin: 0; }

        /* FOOTER */
        .official-footer { background: var(--sidebar); color: #f4f4f4; padding: 20px 30px; border-top: 4px solid var(--accent); text-align: center; font-size: 0.75rem; }
        .footer-content { max-width: 1100px; margin: 0 auto; display: flex; justify-content: space-between; }
        .footer-section p { margin: 2px 0; opacity: 0.7; }

        /* WHATSAPP FLOAT */
        .whatsapp-float {
            position: fixed; width: 60px; height: 60px; bottom: 30px; right: 30px;
            background-color: #25d366; color: #FFF; border-radius: 50px;
            text-align: center; font-size: 30px; box-shadow: 2px 5px 15px rgba(0,0,0,0.2);
            z-index: 1000; display: flex; align-items: center; justify-content: center;
            transition: transform 0.3s ease; text-decoration: none;
        }
        .whatsapp-float:hover { transform: scale(1.1); background-color: #128C7E; }
        .whatsapp-icon { width: 35px; height: 35px; }

        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; max-height: none; height: auto; width: 90%; margin: 20px auto; }
            .hospital-hero, .login-card-container { width: 100%; height: auto; }
            .footer-content { flex-direction: column; gap: 15px; }
            .about-grid, .faq-grid { grid-template-columns: 1fr; }
            body { overflow-y: auto; height: auto; }
        }
    </style>
</head>
<body>

    <div class="header-branding">
        <h1>Ministry of Health and Social Services</h1>
        <small>Republic of Namibia</small>
        <div class="nav-links">
            <a onclick="openModal('faqModal')">FAQ</a>
            <a onclick="openModal('aboutModal')">About Us</a>
        </div>
    </div>

    <div class="marquee-bar">
        <marquee behavior="scroll" direction="left" scrollamount="7">
            <span style="color: #005a8d; font-size: 18px; font-weight: 800; text-transform: uppercase;">
                Welcome to AI Health Smart Passport — Digitalizing Healthcare for all Namibians — Secure, Real-time Medical Records Access
            </span>
        </marquee>
    </div>

    <div class="login-wrapper">
        <div class="hospital-hero">
            <img src="https://images.unsplash.com/photo-1586773860418-d3b97978c65c?auto=format&fit=crop&q=80&w=1200" alt="Hospital Entrance">
            <div class="hero-overlay">
                <h2>Serving Namibia with Care</h2>
                <p>Oshakati Intermediate Hospital | Digital Health Division</p>
            </div>
        </div>

        <div class="login-card-container">
            <h2>Login to Passport</h2>

            <?php if(isset($error)): ?>
                <div style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; border: 1px solid #f5c6cb;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username / ID Number</label>
                    <input type="text" name="username" placeholder="e.g. 96021300997" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="login-btn">Sign In</button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot_password.php" style="color: var(--primary); text-decoration: none; font-size: 0.8rem;">Forgot your password?</a>
                </div>
            </form>
        </div>
    </div>

    <div id="faqModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('faqModal')">&times;</span>
            <h2 class="modal-section-title">Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="item">
                    <h4>How do I access my health records?</h4>
                    <p>Log in using your National ID number. Navigate to 'My Passport' to see clinical history, immunizations, and prescriptions.</p>
                </div>
                <div class="item">
                    <h4>Is my medical data secure?</h4>
                    <p>Yes. Data is encrypted and managed according to MoHSS privacy standards and Namibian Data Protection guidelines.</p>
                </div>
                <div class="item">
                    <h4>Why digitalization?</h4>
                    <p>Digitalization allows for instant access to life-saving information, reduces paper waste, and prevents loss of patient history during hospital transfers.</p>
                </div>
                <div class="item">
                    <h4>Forgot Password?</h4>
                    <p>Contact the IT department at Oshakati Hospital or use the support contact information provided in the footer.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="aboutModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('aboutModal')">&times;</span>
            <h2 class="modal-section-title">About AI Smart Health Passport</h2>
            <p>The <b>AI Smart Health Passport</b> is a national initiative by the Ministry of Health and Social Services (MoHSS) to transform Namibia’s healthcare into a digital-first system.</p>
            
            <div class="about-grid">
                <div class="item">
                    <h4>Integrated DHIS2</h4>
                    <p>Fully compatible with national health data standards for seamless ward management and matron oversight.</p>
                </div>
                <div class="item">
                    <h4>Secure Access</h4>
                    <p>Advanced encryption ensures that patient records and staff data are protected under Namibian cybersecurity laws.</p>
                </div>
                <div class="item">
                    <h4>Real-time Tracking</h4>
                    <p>Track medical history, immunization, and clinical results instantly across different regional hospitals.</p>
                </div>
                <div class="item">
                    <h4>Smart Diagnosis</h4>
                    <p>Utilizing AI modules to assist healthcare providers in early trend detection and patient care optimization.</p>
                </div>
            </div>
            
            <div style="margin-top: 25px; background: #eef2f5; padding: 15px; border-radius: 8px; font-size: 0.85rem;">
                <strong>Institutional Support:</strong> Developed in collaboration with Namibia University of Science and Technology (NUST) and the MoHSS Digital Transformation Initiative.
            </div>
        </div>
    </div>

    <footer class="official-footer">
        <div class="footer-content">
            <div class="footer-section">
                <p><b>Republic of Namibia</b></p>
                <p>MoHSS Digital Health Division</p>
            </div>
            <div class="footer-section">
                <p><b>Oshakati Intermediate</b></p>
                <p>Oshana Region, Namibia</p>
            </div>
            <div class="footer-section">
                <p><b>Contact Support</b></p>
                <p>+264 (65) 223 3000</p>
                <p>health-passport@nust.na</p>
            </div>
        </div>
        <div style="margin-top: 15px; opacity: 0.5;">&copy; 2026 AI Smart Health Passport System</div>
    </footer>

    <a href="https://wa.me/264818759989" class="whatsapp-float" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Chat" class="whatsapp-icon">
    </a>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>