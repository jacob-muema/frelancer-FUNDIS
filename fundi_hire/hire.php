<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=hire.php" . (isset($_GET['id']) ? "?id=" . $_GET['id'] : ""));
    exit();
}

// Check if user is a client
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != 'client') {
    header("Location: index.php");
    exit();
}

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

$fundi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$fundi_data = null;
$hire_message = "";

// Get fundi data
if ($fundi_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, skills, rating FROM users WHERE id = ? AND user_type = 'fundi'");
    $stmt->bind_param("i", $fundi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $fundi_data = $result->fetch_assoc();
    } else {
        header("Location: index.php");
        exit();
    }
    $stmt->close();
}

// Process job request submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_SESSION['user_id'];
    $fundi_id = $conn->real_escape_string($_POST['fundi_id']);
    $job_title = $conn->real_escape_string($_POST['job_title']);
    $job_description = $conn->real_escape_string($_POST['job_description']);
    $budget = $conn->real_escape_string($_POST['budget']);
    $location = $conn->real_escape_string($_POST['location']);
    $timeline = $conn->real_escape_string($_POST['timeline']);
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO job_requests (client_id, fundi_id, job_title, job_description, budget, location, timeline, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssss", $client_id, $fundi_id, $job_title, $job_description, $budget, $location, $timeline, $status, $created_at);
    
    if ($stmt->execute()) {
        $hire_message = "success";
    } else {
        $hire_message = "error";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire a Fundi - FundiHire</title>
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

        /* Hire Form Card */
        .hire-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex: 1;
            gap: 30px;
            flex-wrap: wrap;
        }

        .hire-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 700px;
            transition: var(--transition);
            position: relative;
            margin-bottom: 30px;
        }

        .hire-card:hover {
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

        /* Fundi Info Card */
        .fundi-info-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 350px;
            transition: var(--transition);
            position: sticky;
            top: 20px;
        }

        .fundi-info-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .fundi-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 20px;
            text-align: center;
        }

        .fundi-header h3 {
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .fundi-header p {
            opacity: 0.9;
            font-size: 1em;
        }

        .fundi-body {
            padding: 25px 20px;
            text-align: center;
        }

        .fundi-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 4px solid var(--accent);
        }

        .fundi-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fundi-rating {
            color: var(--accent);
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .fundi-skills {
            margin-bottom: 20px;
        }

        .skill-tag {
            display: inline-block;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.9em;
            margin: 5px;
        }

        .view-profile-btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            margin-top: 10px;
        }

        .view-profile-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
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

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(241, 196, 15, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Success Message */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message i {
            font-size: 1.5em;
        }

        /* Error Message */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 1.5em;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e5ee;
        }

        .form-section-title {
            font-size: 1.2em;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: var(--accent);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-col {
            flex: 1;
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

        .hire-card, .fundi-info-card {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hire-container {
                flex-direction: column-reverse;
                align-items: center;
            }

            .fundi-info-card {
                position: static;
                max-width: 700px;
                margin-bottom: 30px;
            }
        }

        @media (max-width: 768px) {
            .card-header, .card-body {
                padding: 25px 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container">
            <header class="header">
                <a href="index.php" class="logo"><span>Fundi</span>Hire</a>
                <a href="<?php echo isset($_GET['id']) ? 'profile.php?id=' . $_GET['id'] : 'index.php'; ?>" class="home-btn">
                    <i class="fas fa-arrow-left"></i> 
                    <?php echo isset($_GET['id']) ? 'Back to Profile' : 'Back to Home'; ?>
                </a>
            </header>

            <div class="hire-container">
                <div class="hire-card">
                    <div class="card-header">
                        <h2>Hire a Fundi</h2>
                        <p>Submit your job request and get connected with the right professional</p>
                    </div>
                    <div class="card-body">
                        <?php if ($hire_message === "success"): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Success!</strong> Your job request has been submitted successfully. The fundi will contact you soon.
                                    <p><a href="dashboard.php" style="color: inherit; text-decoration: underline;">View your job requests</a></p>
                                </div>
                            </div>
                        <?php elseif ($hire_message === "error"): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <div>
                                    <strong>Error!</strong> There was a problem submitting your job request. Please try again.
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="hire.php<?php echo isset($_GET['id']) ? '?id=' . $_GET['id'] : ''; ?>" id="hireForm">
                            <input type="hidden" name="fundi_id" value="<?php echo $fundi_id; ?>">
                            
                            <div class="form-section">
                                <h3 class="form-section-title"><i class="fas fa-briefcase"></i> Job Details</h3>
                                
                                <div class="form-group">
                                    <label for="job_title">Job Title</label>
                                    <i class="fas fa-heading input-icon"></i>
                                    <input type="text" id="job_title" name="job_title" class="form-control input-with-icon" placeholder="e.g., Kitchen Renovation, Electrical Repair" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="job_description">Job Description</label>
                                    <textarea id="job_description" name="job_description" class="form-control" placeholder="Describe the job in detail including requirements, materials needed, and any specific instructions" required></textarea>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Location & Timeline</h3>
                                
                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <i class="fas fa-map-marker-alt input-icon"></i>
                                            <input type="text" id="location" name="location" class="form-control input-with-icon" placeholder="e.g., Nairobi, Westlands" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="timeline">Timeline</label>
                                            <i class="fas fa-calendar-alt input-icon"></i>
                                            <input type="text" id="timeline" name="timeline" class="form-control input-with-icon" placeholder="e.g., 2 days, 1 week, ASAP" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title"><i class="fas fa-money-bill-wave"></i> Budget</h3>
                                
                                <div class="form-group">
                                    <label for="budget">Estimated Budget (KSh)</label>
                                    <i class="fas fa-money-bill-wave input-icon"></i>
                                    <input type="text" id="budget" name="budget" class="form-control input-with-icon" placeholder="e.g., 5000, 10000-15000, Negotiable" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i> Submit Job Request
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($fundi_data): ?>
                <div class="fundi-info-card">
                    <div class="fundi-header">
                        <h3>Fundi Information</h3>
                        <p>You're hiring:</p>
                    </div>
                    <div class="fundi-body">
                        <div class="fundi-image">
                            <?php
                            // Use a random professional image based on fundi_id
                            $images = [
                                'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80',
                                'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=776&q=80',
                                'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80',
                                'https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1061&q=80'
                            ];
                            $image_index = $fundi_id % count($images);
                            ?>
                            <img src="<?php echo $images[$image_index]; ?>" alt="<?php echo $fundi_data['name']; ?>">
                        </div>
                        <h3><?php echo $fundi_data['name']; ?></h3>
                        <div class="fundi-rating">
                            <?php 
                            $rating = isset($fundi_data['rating']) ? $fundi_data['rating'] : rand(35, 50) / 10;
                            for($i = 1; $i <= 5; $i++): 
                                if($i <= floor($rating)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif($i - $rating < 1 && $i - $rating > 0): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif;
                            endfor; ?>
                            <span>(<?php echo $rating; ?>)</span>
                        </div>
                        <div class="fundi-skills">
                            <?php 
                            $skills = isset($fundi_data['skills']) ? explode(',', $fundi_data['skills']) : ['Carpenter', 'Furniture Maker'];
                            foreach($skills as $skill): 
                                $skill = trim($skill);
                                if(!empty($skill)):
                            ?>
                                <span class="skill-tag"><?php echo $skill; ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <a href="profile.php?id=<?php echo $fundi_id; ?>" class="view-profile-btn">View Full Profile</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('hireForm').addEventListener('submit', function(e) {
            const jobTitle = document.getElementById('job_title').value;
            const jobDescription = document.getElementById('job_description').value;
            const budget = document.getElementById('budget').value;
            
            if (jobTitle.length < 5) {
                e.preventDefault();
                alert('Please enter a more descriptive job title (at least 5 characters)');
                return false;
            }
            
            if (jobDescription.length < 20) {
                e.preventDefault();
                alert('Please provide a more detailed job description (at least 20 characters)');
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