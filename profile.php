<?php
session_start();

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$fundi_id = $_GET['id'];

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

// Get fundi data
$fundi = null;
$stmt = $conn->prepare("SELECT id, name, skills, user_type, email, phone FROM users WHERE id = ? AND user_type = 'fundi'");
$stmt->bind_param("i", $fundi_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $fundi = $result->fetch_assoc();
} else {
    header("Location: index.php");
    exit();
}
$stmt->close();
$conn->close();

// Set default values for missing data
$fundi['location'] = isset($fundi['location']) ? $fundi['location'] : 'Nairobi, Kenya';
$fundi['rating'] = 4.5; // Default rating
$fundi['reviews'] = 28; // Default number of reviews
$fundi['bio'] = 'Professional ' . $fundi['skills'] . ' with years of experience in the field.';
$fundi['experience'] = '5+ years';

// Sample projects and testimonials (in a real app, these would come from the database)
$fundi['projects'] = [
    [
        'title' => 'Recent Project 1',
        'description' => 'Completed a major project with excellent results.',
        'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'
    ],
    [
        'title' => 'Recent Project 2',
        'description' => 'Another successful project delivered on time and within budget.',
        'image' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'
    ]
];

$fundi['testimonials'] = [
    [
        'name' => 'Satisfied Client',
        'comment' => 'Excellent service and professionalism. Highly recommended!',
        'rating' => 5,
        'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80'
    ]
];

// Get a profile image based on fundi_id
$images = [
    'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80',
    'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=776&q=80',
    'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80',
    'https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1061&q=80'
];
$image_index = $fundi_id % count($images);
$fundi['image'] = $images[$image_index];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $fundi['name']; ?> - FundiHire Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            height: 100%;
            width: 100%;
            scroll-behavior: smooth;
        }

        body {
            display: flex;
            flex-direction: column;
            color: #333;
            line-height: 1.6;
            background-color: #f8f9fa;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Sidebar/Navigation Styling */
        .sidebar {
            width: 100%;
            background: linear-gradient(to right, #0c2461, #1e3799);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.8em;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .logo span {
            color: #f1c40f;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            gap: 30px;
            margin: 0;
            padding: 0;
        }

        .nav-menu li a {
            text-decoration: none;
            color: white;
            font-size: 1.1em;
            font-weight: 500;
            transition: 0.3s;
            padding: 8px 15px;
            border-radius: 4px;
        }

        .nav-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #f1c40f;
        }

        .nav-menu li a.btn {
            background-color: #f1c40f;
            color: #0c2461;
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .nav-menu li a.btn:hover {
            background-color: #f39c12;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .menu-toggle {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: none;
            transition: 0.3s;
        }

        .menu-toggle:hover {
            color: #f1c40f;
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 65px;
            left: 0;
            width: 100%;
            background: #1e3799;
            padding: 20px;
            z-index: 999;
            transform: translateY(-150%);
            transition: transform 0.4s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active {
            transform: translateY(0);
        }

        .mobile-menu ul {
            list-style: none;
        }

        .mobile-menu ul li {
            margin-bottom: 15px;
        }

        .mobile-menu ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.1em;
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: 0.3s;
        }

        .mobile-menu ul li a:hover {
            color: #f1c40f;
            padding-left: 10px;
        }

        .mobile-menu ul li a.btn {
            background-color: #f1c40f;
            color: #0c2461;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            border: none;
        }

        .mobile-menu ul li a.btn:hover {
            background-color: #f39c12;
            padding-left: 20px;
        }

        /* Profile Styles */
        .profile-container {
            margin-top: 80px;
            padding: 40px 0;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 40px;
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        @media (min-width: 768px) {
            .profile-header {
                flex-direction: row;
                text-align: left;
                align-items: flex-start;
            }
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 20px;
            border: 5px solid #f1c40f;
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .profile-image {
                margin-right: 30px;
                margin-bottom: 0;
            }
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-name {
            font-size: 2em;
            color: #0c2461;
            margin-bottom: 5px;
        }

        .profile-skill {
            font-size: 1.2em;
            color: #f39c12;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .profile-rating {
            color: #f39c12;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .profile-location {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #666;
        }

        .profile-location i {
            margin-right: 10px;
            color: #0c2461;
        }

        .profile-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            color: #666;
        }

        .contact-item i {
            margin-right: 10px;
            color: #0c2461;
        }

        .hire-btn {
            display: inline-block;
            background-color: #f1c40f;
            color: #0c2461;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .hire-btn:hover {
            background-color: #f39c12;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
        }

        .tab-btn {
            padding: 15px 25px;
            background: transparent;
            border: none;
            font-size: 1.1em;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-btn.active {
            color: #0c2461;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #f1c40f;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* About Section */
        .about-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.5em;
            color: #0c2461;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: #f1c40f;
        }

        .bio {
            margin-bottom: 20px;
            line-height: 1.7;
            color: #555;
        }

        .experience-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .experience-icon {
            width: 40px;
            height: 40px;
            background-color: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #0c2461;
        }

        /* Projects Section */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .project-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .project-img {
            height: 200px;
            overflow: hidden;
        }

        .project-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .project-card:hover .project-img img {
            transform: scale(1.1);
        }

        .project-content {
            padding: 20px;
        }

        .project-title {
            font-size: 1.2em;
            color: #0c2461;
            margin-bottom: 10px;
        }

        .project-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        /* Reviews Section */
        .reviews-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .review-card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .review-card::before {
            content: '\201C';
            font-size: 60px;
            position: absolute;
            top: 10px;
            left: 15px;
            color: rgba(12, 36, 97, 0.1);
            font-family: Georgia, serif;
        }

        .review-content {
            position: relative;
            z-index: 1;
        }

        .review-text {
            color: #555;
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .review-author {
            display: flex;
            align-items: center;
        }

        .author-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .author-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            color: #0c2461;
            margin-bottom: 5px;
        }

        .review-rating {
            color: #f39c12;
            font-size: 0.9em;
        }

        /* Contact Form */
        .contact-form {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0c2461;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #0c2461;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            display: inline-block;
            background-color: #0c2461;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1em;
        }

        .submit-btn:hover {
            background-color: #f1c40f;
            color: #0c2461;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        footer {
            background: #222;
            color: white;
            padding: 60px 0 20px;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.3em;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: #f1c40f;
        }

        .footer-column p {
            color: #bbb;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column ul li a {
            color: #bbb;
            text-decoration: none;
            transition: 0.3s;
        }

        .footer-column ul li a:hover {
            color: #f1c40f;
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            color: white;
            border-radius: 50%;
            transition: 0.3s;
        }

        .social-links a:hover {
            background-color: #f1c40f;
            color: #222;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            color: #bbb;
            font-size: 0.9em;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .profile-tabs {
                flex-wrap: wrap;
            }
            
            .tab-btn {
                padding: 10px 15px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar/Navigation Menu -->
    <nav class="sidebar">
        <div class="container sidebar-container">
            <div class="logo"><span>Fundi</span>Hire</div>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#how-it-works">How It Works</a></li>
                <li><a href="index.php#featured-fundis">Find Fundis</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php" class="btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#how-it-works">How It Works</a></li>
            <li><a href="index.php#featured-fundis">Find Fundis</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content -->
    <main class="profile-container">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image">
                    <img src="<?php echo $fundi['image']; ?>" alt="<?php echo $fundi['name']; ?>">
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo $fundi['name']; ?></h1>
                    <div class="profile-skill"><?php echo $fundi['skills']; ?></div>
                    <div class="profile-rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= floor($fundi['rating'])): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif($i - $fundi['rating'] < 1 && $i - $fundi['rating'] > 0): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span>(<?php echo $fundi['rating']; ?>) - <?php echo $fundi['reviews']; ?> reviews</span>
                    </div>
                    <div class="profile-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $fundi['location']; ?></span>
                    </div>
                    <div class="profile-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo $fundi['phone']; ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo $fundi['email']; ?></span>
                        </div>
                    </div>
                    
                    <!-- Direct link to hire.php with fundi ID -->
                    <a href="hire.php?id=<?php echo $fundi_id; ?>" class="hire-btn">
                        <i class="fas fa-handshake"></i> Hire <?php echo $fundi['name']; ?>
                    </a>
                </div>
            </div>

            <!-- Profile Tabs -->
            <div class="profile-tabs">
                <button class="tab-btn active" data-tab="about">About</button>
                <button class="tab-btn" data-tab="projects">Projects</button>
                <button class="tab-btn" data-tab="reviews">Reviews</button>
                <button class="tab-btn" data-tab="contact">Contact</button>
            </div>

            <!-- About Tab -->
            <div id="about" class="tab-content active">
                <div class="about-section">
                    <h2 class="section-title">About Me</h2>
                    <p class="bio"><?php echo $fundi['bio']; ?></p>
                    
                    <h3 class="section-title">Experience</h3>
                    <div class="experience-item">
                        <div class="experience-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div>
                            <strong>Professional Experience:</strong> <?php echo $fundi['experience']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects Tab -->
            <div id="projects" class="tab-content">
                <div class="projects-grid">
                    <?php foreach($fundi['projects'] as $project): ?>
                        <div class="project-card">
                            <div class="project-img">
                                <img src="<?php echo $project['image']; ?>" alt="<?php echo $project['title']; ?>">
                            </div>
                            <div class="project-content">
                                <h3 class="project-title"><?php echo $project['title']; ?></h3>
                                <p class="project-description"><?php echo $project['description']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content">
                <div class="reviews-container">
                    <?php foreach($fundi['testimonials'] as $testimonial): ?>
                        <div class="review-card">
                            <div class="review-content">
                                <p class="review-text"><?php echo $testimonial['comment']; ?></p>
                                <div class="review-author">
                                    <div class="author-img">
                                        <img src="<?php echo $testimonial['image']; ?>" alt="<?php echo $testimonial['name']; ?>">
                                    </div>
                                    <div class="author-info">
                                        <h4><?php echo $testimonial['name']; ?></h4>
                                        <div class="review-rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= floor($testimonial['rating'])): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php elseif($i - $testimonial['rating'] < 1 && $i - $testimonial['rating'] > 0): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact Tab -->
            <div id="contact" class="tab-content">
                <div class="contact-form">
                    <h2 class="section-title">Contact <?php echo $fundi['name']; ?></h2>
                    <form action="#" method="post">
                        <div class="form-group">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Your Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="project" class="form-label">Project Details</label>
                            <textarea id="project" name="project" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3><span style="color: #f1c40f;">Fundi</span>Hire</h3>
                    <p>The premier platform connecting clients with skilled fundis across Kenya.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>For Clients</h3>
                    <ul>
                        <li><a href="#">How to Hire</a></li>
                        <li><a href="#">Post a Job</a></li>
                        <li><a href="#">Browse Fundis</a></li>
                        <li><a href="#">Payment Methods</a></li>
                        <li><a href="#">Client Success Stories</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>For Fundis</h3>
                    <ul>
                        <li><a href="#">Create Profile</a></li>
                        <li><a href="#">Find Jobs</a></li>
                        <li><a href="#">Showcase Skills</a></li>
                        <li><a href="#">Get Paid</a></li>
                        <li><a href="#">Fundi Success Stories</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> FundiHire. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript for menu toggle
        document.addEventListener("DOMContentLoaded", function() {
            const menuToggle = document.getElementById("menuToggle");
            const mobileMenu = document.getElementById("mobileMenu");
            
            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener("click", function() {
                    mobileMenu.classList.toggle("active");
                    
                    // Change icon based on menu state
                    const icon = menuToggle.querySelector("i");
                    if (mobileMenu.classList.contains("active")) {
                        icon.classList.remove("fa-bars");
                        icon.classList.add("fa-times");
                    } else {
                        icon.classList.remove("fa-times");
                        icon.classList.add("fa-bars");
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener("click", function(event) {
                    if (!mobileMenu.contains(event.target) && !menuToggle.contains(event.target) && mobileMenu.classList.contains("active")) {
                        mobileMenu.classList.remove("active");
                        const icon = menuToggle.querySelector("i");
                        icon.classList.remove("fa-times");
                        icon.classList.add("fa-bars");
                    }
                });
                
                // Close mobile menu when clicking on a link
                const mobileLinks = mobileMenu.querySelectorAll("a");
                mobileLinks.forEach(link => {
                    link.addEventListener("click", function() {
                        mobileMenu.classList.remove("active");
                        const icon = menuToggle.querySelector("i");
                        icon.classList.remove("fa-times");
                        icon.classList.add("fa-bars");
                    });
                });
            }
            
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Check if there's a hash in the URL to open specific tab
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                const tabButton = document.querySelector(`.tab-btn[data-tab="${hash}"]`);
                if (tabButton) {
                    tabButton.click();
                }
            }
        });
    </script>

</body>
</html>

