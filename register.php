<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "fundihire";

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$register_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $register_message = "Email already registered!";
        $checkEmail->close();
    } else {
        $checkEmail->close();

        if ($user_type == "fundi") {
            $skills = $conn->real_escape_string($_POST['skills']);
            $sql = "INSERT INTO users (name, email, phone, password, user_type, skills) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $email, $phone, $password, $user_type, $skills);
        } else {
            $sql = "INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $phone, $password, $user_type);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
            exit();
        } else {
            $register_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Get user type from URL if provided
$default_type = isset($_GET['type']) ? $_GET['type'] : 'client';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #0c2461;
            --primary-dark: #091b46;
            --primary-light: #1e3799;
            --accent: #f1c40f;
            --accent-dark: #f39c12;
            --text-dark: #333;
            --text-light: #666;
            --text-lighter: #999;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        html, body {
            height: 100%;
            width: 100%;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(12, 36, 97, 0.05) 0%, rgba(30, 55, 153, 0.1) 100%);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.05;
            z-index: -1;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 1.8em;
            font-weight: bold;
            letter-spacing: 1px;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo span {
            color: var(--accent);
        }

        .home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(12, 36, 97, 0.2);
        }

        .home-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(12, 36, 97, 0.3);
        }

        .home-btn i {
            font-size: 0.9em;
        }

        /* Registration Card */
        .registration-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
        }

        .registration-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            transition: var(--transition);
            position: relative;
        }

        .registration-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .card-header h2 {
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .card-header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: var(--bg-white);
            clip-path: polygon(0 100%, 100% 100%, 100% 0, 50% 100%, 0 0);
        }

        .card-body {
            padding: 40px 30px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95em;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 1em;
            transition: var(--transition);
            background-color: #f9fafc;
            color: var(--text-dark);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(12, 36, 97, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-lighter);
        }

        .input-icon {
            position: absolute;
            top: 42px;
            left: 15px;
            color: var(--text-lighter);
            font-size: 1.2em;
        }

        .input-with-icon {
            padding-left: 45px;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }

        .user-type-selector {
            display: flex;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e1e5ee;
        }

        .user-type-option {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            transition: var(--transition);
            background-color: #f9fafc;
            position: relative;
        }

        .user-type-option:first-child {
            border-right: 1px solid #e1e5ee;
        }

        .user-type-option:last-child {
            border-left: 1px solid #e1e5ee;
        }

        .user-type-option.active {
            background-color: var(--primary);
            color: white;
        }

        .user-type-option i {
            font-size: 1.5em;
            margin-bottom: 8px;
            display: block;
        }

        .user-type-option span {
            font-weight: 600;
        }

        .user-type-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, var(--accent), var(--accent-dark));
            color: var(--primary);
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 10px rgba(241, 196, 15, 0.3);
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(241, 196, 15, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-link a:hover {
            color: var(--accent);
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .registration-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .card-header {
                padding: 25px 20px;
            }

            .card-body {
                padding: 30px 20px;
            }

            .form-control {
                padding: 12px 15px;
            }

            .user-type-option i {
                font-size: 1.2em;
            }
        }

        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                gap: 15px;
            }

            .card-header h2 {
                font-size: 1.8em;
            }

            .user-type-selector {
                flex-direction: column;
            }

            .user-type-option:first-child {
                border-right: none;
                border-bottom: 1px solid #e1e5ee;
            }

            .user-type-option:last-child {
                border-left: none;
                border-top: 1px solid #e1e5ee;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container">
            <header class="header">
                <a href="index.php" class="logo"><span>Task</span>Hero</a>
                <a href="index.php" class="home-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </header>

            <div class="registration-container">
                <div class="registration-card">
                    <div class="card-header">
                        <h2>Create Account</h2>
                        <p>Join TaskHero and connect with skilled professionals</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($register_message)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $register_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php" id="registerForm">
                            <div class="user-type-selector">
                                <label class="user-type-option <?php echo ($default_type == 'client') ? 'active' : ''; ?>">
                                    <input type="radio" name="user_type" value="client" <?php echo ($default_type == 'client') ? 'checked' : ''; ?>>
                                    <i class="fas fa-user"></i>
                                    <span>Client</span>
                                </label>
                                <label class="user-type-option <?php echo ($default_type == 'fundi') ? 'active' : ''; ?>">
                                    <input type="radio" name="user_type" value="fundi" <?php echo ($default_type == 'fundi') ? 'checked' : ''; ?>>
                                    <i class="fas fa-laptop-code"></i> <!-- freelance dev, coding -->

                                    <span>Frelancer</span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="name" name="name" class="form-control input-with-icon" placeholder="Enter your full name" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control input-with-icon" placeholder="Enter your email" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <i class="fas fa-phone input-icon"></i>
                                <input type="text" id="phone" name="phone" class="form-control input-with-icon" placeholder="Enter your phone number" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="Create a password" required>
                            </div>

                            <div class="form-group" id="skills_field" style="display: <?php echo ($default_type == 'fundi') ? 'block' : 'none'; ?>">
                                <label for="skills">Your Skills</label>
                                <i class="fas fa-briefcase input-icon"></i>
                                <input type="text" id="skills" name="skills" class="form-control input-with-icon" placeholder="e.g., Plumber, Electrician, Carpenter">
                            </div>

                            <button type="submit" class="submit-btn">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>

                            <div class="login-link">
                                Already have an account? <a href="login.php">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User type selector
        const userTypeOptions = document.querySelectorAll('.user-type-option');
        const skillsField = document.getElementById('skills_field');
        
        userTypeOptions.forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            
            option.addEventListener('click', function() {
                // Update active class
                userTypeOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                // Check the radio button
                radio.checked = true;
                
                // Show/hide skills field
                if (radio.value === 'fundi') {
                    skillsField.style.display = 'block';
                } else {
                    skillsField.style.display = 'none';
                }
            });
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const userType = document.querySelector('input[name="user_type"]:checked').value;
            const skills = document.getElementById('skills').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
            
            if (userType === 'fundi' && skills.trim() === '') {
                e.preventDefault();
                alert('Please enter your skills');
                return false;
            }
            
            return true;
        });

        // Add animation to form fields
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach((control, index) => {
            control.style.opacity = '0';
            control.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                control.style.transition = 'all 0.5s ease';
                control.style.opacity = '1';
                control.style.transform = 'translateY(0)';
            }, 100 + (index * 100));
        });
    </script>
</body>
</html>