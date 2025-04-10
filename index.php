<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHero - Connect with Skilled Professionals</title>
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

        /* Hero Section */
        .hero {
            width: 100%;
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            padding: 0 20px;
            margin-top: 60px;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(12, 36, 97, 0.8), rgba(30, 55, 153, 0.7));
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
        }

        .hero h1 {
            font-size: 3.5em;
            font-weight: bold;
            margin-bottom: 20px;
            color: white;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero h1 span {
            color: #f1c40f;
        }

        .hero p {
            font-size: 1.4em;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            background: #f1c40f;
            color: #0c2461;
            padding: 15px 30px;
            font-size: 1.1em;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
            border: 2px solid #f1c40f;
            cursor: pointer;
        }

        .btn:hover {
            background: #f39c12;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #f1c40f;
            border-color: #f1c40f;
        }

        /* User Types Section */
        .user-types {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5em;
            color: #0c2461;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: #f1c40f;
            border-radius: 2px;
        }

        .section-title p {
            color: #666;
            font-size: 1.1em;
            max-width: 700px;
            margin: 0 auto;
        }

        .user-types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .user-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
        }

        .user-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .user-img {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .user-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .user-card:hover .user-img img {
            transform: scale(1.1);
        }

        .user-img::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 30%;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }

        .user-content {
            padding: 25px;
        }

        .user-content h3 {
            font-size: 1.5em;
            color: #0c2461;
            margin-bottom: 15px;
        }

        .user-content p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .user-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0c2461;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .user-btn:hover {
            background-color: #f1c40f;
            color: #0c2461;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Fundi Skills Section */
        .fundi-skills {
            padding: 60px 0;
            background-color: white;
        }

        .skills-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .skill-item {
            text-align: center;
            padding: 20px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .skill-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            background: #0c2461;
            color: white;
        }

        .skill-icon {
            font-size: 2.5em;
            color: #f1c40f;
            margin-bottom: 15px;
        }

        .skill-item:hover .skill-icon {
            color: #f1c40f;
        }

        .skill-name {
            font-weight: 600;
            font-size: 1.1em;
        }

        /* Featured Fundis Section */
        .featured-fundis {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .fundis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .fundi-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
        }

        .fundi-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .fundi-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 20px auto;
            overflow: hidden;
            border: 4px solid #f1c40f;
        }

        .fundi-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fundi-content {
            padding: 0 20px 25px;
        }

        .fundi-content h3 {
            font-size: 1.3em;
            color: #0c2461;
            margin-bottom: 5px;
        }

        .fundi-content .skill {
            color: #f39c12;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }

        .rating {
            color: #f39c12;
            margin-bottom: 15px;
        }

        .fundi-content p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 0.9em;
        }

        .fundi-btn {
            display: inline-block;
            padding: 8px 20px;
            background-color: #0c2461;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9em;
        }

        .fundi-btn:hover {
            background-color: #f1c40f;
            color: #0c2461;
        }

        /* How It Works Section */
        .how-it-works {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }

        .step {
            text-align: center;
            padding: 20px;
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background-color: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #0c2461;
            font-size: 2em;
            font-weight: bold;
            position: relative;
        }

        .step-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -40px;
            width: 40px;
            height: 2px;
            background-color: #e3f2fd;
            transform: translateY(-50%);
        }

        .steps .step:last-child .step-icon::after {
            display: none;
        }

        .step h3 {
            font-size: 1.3em;
            color: #0c2461;
            margin-bottom: 15px;
        }

        .step p {
            color: #666;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 0;
            background-color: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .testimonial-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .testimonial-card::before {
            content: '\201C';
            font-size: 80px;
            position: absolute;
            top: 20px;
            left: 20px;
            color: rgba(12, 36, 97, 0.1);
            font-family: Georgia, serif;
        }

        .testimonial-content {
            position: relative;
            z-index: 1;
        }

        .testimonial-content p {
            color: #555;
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .testimonial-author {
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

        .author-info span {
            color: #666;
            font-size: 0.9em;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: linear-gradient(to right, #0c2461, #1e3799);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 1.2em;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        footer {
            background: #222;
            color: white;
            padding: 60px 0 20px;
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

        /* Skill Tags for Fundis */
        .skill-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 15px 0;
            justify-content: center;
        }

        .skill-tag {
            background-color: #e3f2fd;
            color: #0c2461;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        /* Availability Badge */
        .availability {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .available {
            background-color: #e6f7e9;
            color: #2ecc71;
        }

        .busy {
            background-color: #ffeaea;
            color: #e74c3c;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.8em;
            }
            
            .hero p {
                font-size: 1.2em;
            }
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .hero h1 {
                font-size: 2.2em;
            }
            
            .hero p {
                font-size: 1.1em;
            }
            
            .btn {
                padding: 12px 25px;
                font-size: 1em;
            }
            
            .section-title h2 {
                font-size: 2em;
            }
            
            .step-icon::after {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 1.8em;
            }
            
            .hero p {
                font-size: 1em;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
            
            .section-title h2 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar/Navigation Menu -->
    <nav class="sidebar">
        <div class="container sidebar-container">
            <div class="logo"><span>Task</span>Hero</div>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#featured-fundis">Find Frelancers</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
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
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#featured-fundis">Find Fundis</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php" class="btn">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Connect with Skilled <span>Frelancers</span> Directly</h1>
                <p>Find reliable professionals for your projects or offer your skills as a Frelancer</p>
                <div class="btn-group">
                    <a href="register.php" class="btn">Get Started</a>
                    <a href="#user-types" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </section>

        <!-- User Types Section -->
        <section id="user-types" class="user-types">
            <div class="container">
                <div class="section-title">
                    <h2>Join TaskHero Today</h2>
                    <p>Whether you need to hire a skilled professional or you are a Frelancer looking for work, TaskHero connects you directly</p>
                </div>
                <div class="user-types-grid">
                    <!-- Client Card -->
                    <div class="user-card">
                        <div class="user-img">
                            <img src="https://images.unsplash.com/photo-1539037116277-4db20889f2d4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Client">
                        </div>
                        <div class="user-content">
                            <h3>I Need a Frelancer</h3>
                            <p>Post your job, connect with skilled professionals, and hire the best Frelancer for your project.</p>
                            <a href="register.php?type=client" class="user-btn">Register as Client</a>
                        </div>
                    </div>
                    
                    <!-- Fundi Card -->
                    <div class="user-card">
                        <div class="user-img">
                            <img src="https://images.unsplash.com/photo-1507152832244-10d45c7eda57?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80" alt="Fundi">
                        </div>
                        <div class="user-content">
                            <h3>I am a Frelancer</h3>
                            <p>Create your profile, showcase your skills, and connect with clients looking for your expertise.</p>
                            <a href="register.php?type=fundi" class="user-btn">Register as Frelancer</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Fundi Skills Section -->
        <section class="fundi-skills">
            <div class="container">
                <div class="section-title">
                    <h2>Showcase Your Skills as a Frelancer</h2>
                    <p>Join our platform and connect with clients looking for your specific expertise</p>
                </div>
                <div class="skills-container">
                    <!-- Skill 1 -->
                    <div class="skill-item">
                        <div class="skill-icon">
                            <i class="fas fa-hammer"></i>
                        </div>
                        <div class="skill-name">Carpentry</div>
                    </div>
                    
                    <!-- Skill 2 -->
                    <div class="skill-item">
                        <div class="skill-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="skill-name">Electrical</div>
                    </div>
                    
                    <!-- Skill 3 -->
                    <div class="skill-item">
                        <div class="skill-icon">
                            <i class="fas fa-faucet"></i>
                        </div>
                        <div class="skill-name">Plumbing</div>
                    </div>
                    
                    <!-- Skill 4 -->
                    <div class="skill-item">
                        <div class="skill-icon">
                            <i class="fas fa-paint-roller"></i>
                        </div>
                        <div class="skill-name">Painting</div>
                    </div>
                    
                    <!-- Skill 5 -->
                    <div class="skill-item">
                        <div class="skill-icon">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="skill-name">Masonry</div>
                    </div>
                    
                    <!-- Skill 6 -->
<div class="skill-item">
    <div class="skill-icon">
        <i class="fas fa-laptop-code"></i>
    </div>
    <div class="skill-name">Web Development</div>
</div>

<!-- Skill 7 -->
<div class="skill-item">
    <div class="skill-icon">
        <i class="fas fa-pencil-ruler"></i>
    </div>
    <div class="skill-name">Graphic Design</div>
</div>

<!-- Skill 8 -->
<div class="skill-item">
    <div class="skill-icon">
        <i class="fas fa-pen-nib"></i>
    </div>
    <div class="skill-name">Content Writing</div>
</div>

        </section>

        <!-- Featured Fundis Section -->
        <section id="featured-fundis" class="featured-fundis">
            <div class="container">
                <div class="section-title">
                    <h2>Top Rated Frelancers</h2>
                    <p>Discover our highest-rated professionals across different skills</p>
                </div>
                <div class="fundis-grid">
                    <!-- Fundi 1 -->
                    <div class="fundi-card">
                        <div class="fundi-img">
                            <img src="https://images.unsplash.com/photo-1506277886164-e25aa3f4ef7f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="John Doe">
                        </div>
                        <div class="fundi-content">
                            <h3>John Doe</h3>
                            <span class="skill">Carpenter</span>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>(4.5)</span>
                            </div>
                            <span class="availability available">Available Now</span>
                            <div class="skill-tags">
                                <span class="skill-tag">Furniture</span>
                                <span class="skill-tag">Cabinets</span>
                                <span class="skill-tag">Repairs</span>
                            </div>
                            <a href="register.php?type=client1" class="fundi-btn">Carpenter</a>
                        </div>
                    </div>
                    
                    <!-- Fundi 2 -->
                    <div class="fundi-card">
    <div class="fundi-img">
        <img src="https://images.unsplash.com/photo-1531384441138-2736e62e0919?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Jane Smith">
    </div>
    <div class="fundi-content">
        <h3>Jane Smith</h3>
        <span class="skill">Freelance Electrician</span>
        <div class="rating">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <span>(5.0)</span>
        </div>
        <span class="availability busy">Currently Busy</span>
        <div class="skill-tags">
            <span class="skill-tag">Wiring</span>
            <span class="skill-tag">Installations</span>
            <span class="skill-tag">Repairs</span>
        </div>
        <a href="register.php?type=client1" class="fundi-btn">Hire Electrician</a>
    </div>
</div>

                    <!-- Fundi 3 -->
                    <div class="fundi-card">
                        <div class="fundi-img">
                            <img src="https://images.unsplash.com/photo-1539701938214-0d9736e1c16b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="David Kamau">
                        </div>
                        <div class="fundi-content">
                            <h3>Mary wangari</h3>
                            <span class="skill">Plumber</span>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(4.0)</span>
                            </div>
                            <span class="availability available">Available Now</span>
                            <div class="skill-tags">
                                <span class="skill-tag">Installations</span>
                                <span class="skill-tag">Leaks</span>
                                <span class="skill-tag">Drainage</span>
                            </div>
                            <a href="register.php?type=client1" class="fundi-btn"> Plumber</a>
                        </div>
                    </div>
                    
                    <!-- Fundi 4 -->
                    <div class="fundi-card">
                        <div class="fundi-img">
                            <img src="https://images.unsplash.com/photo-1589156280159-27698a70f29e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=772&q=80" alt="Mary Wanjiku">
                        </div>
                        <div class="fundi-content">
                            <h3>Mary Wanjiku</h3>
                            <span class="skill">Painter</span>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>(4.7)</span>
                            </div>
                            <span class="availability available">Available Now</span>
                            <div class="skill-tags">
                                <span class="skill-tag">Interior</span>
                                <span class="skill-tag">Exterior</span>
                                <span class="skill-tag">Decorative</span>
                            </div>
                            <a href="register.php?type=client1" class="fundi-btn"> Painter</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="how-it-works">
            <div class="container">
                <div class="section-title">
                    <h2>How TaskHero Works</h2>
                    <p>Connecting clients and frelancers in three simple steps</p>
                </div>
                <div class="steps">
                    <!-- Step 1 -->
                    <div class="step">
                        <div class="step-icon">1</div>
                        <h3>Create Your Account</h3>
                        <p>Sign up as a client looking to hire or as a fundi offering your skills.</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="step">
                        <div class="step-icon">2</div>
                        <h3>Connect Directly</h3>
                        <p>Clients post jobs or browse fundi profiles. Fundis showcase their skills and apply for jobs.</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="step">
                        <div class="step-icon">3</div>
                        <h3>Complete Projects</h3>
                        <p>Hire your chosen fundi, complete the project, and leave a review of your experience.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container">
                <div class="section-title">
                    <h2>What Our Users Say</h2>
                    <p>Real experiences from clients and fundis on our platform</p>
                </div>
                <div class="testimonials-grid">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>I needed a carpenter urgently for my new home. Through FundiHire, I found John who did an excellent job with my cabinets. The direct connection made everything so much easier!</p>
                            <div class="testimonial-author">
                                <div class="author-img">
                                    <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=774&q=80" alt="Sarah">
                                </div>
                                <div class="author-info">
                                    <h4>Sarah Njeri</h4>
                                    <span>Client</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>As a plumber, TaskHero has connected me with many clients in my area. The platform is easy to use and I've been able to grow my business significantly.</p>
                            <div class="testimonial-author">
                                <div class="author-img">
                                    <img src="https://images.unsplash.com/photo-1522529599102-193c0d76b5b6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80" alt="Michael">
                                </div>
                                <div class="author-info">
                                    <h4>Michael Omondi</h4>
                                    <span> (Plumber)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>I've hired multiple frelancers through this platform for my renovation project. The direct communication and ability to see reviews helped me find the right professionals.</p>
                            <div class="testimonial-author">
                                <div class="author-img">
                                    <img src="https://images.unsplash.com/photo-1578635073855-a89b3dd5cc18?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80" alt="James">
                                </div>
                                <div class="author-info">
                                    <h4>James Mwangi</h4>
                                    <span>Client</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="container">
                <h2>Ready to Connect?</h2>
                <p>Join TaskHero today and experience the easiest way to connect clients with skilled frelancers.</p>
                <a href="register.php" class="btn">Sign Up Now</a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3><span style="color: #f1c40f;">Task</span>Hero</h3>
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
                    <h3>For Frelancers</h3>
                    <ul>
                        <li><a href="#">Create Profile</a></li>
                        <li><a href="#">Find Jobs</a></li>
                        <li><a href="#">Showcase Skills</a></li>
                        <li><a href="#">Get Paid</a></li>
                        <li><a href="#">Frelancers Success Stories</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> Taskhero. All rights reserved.</p>
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
                
                // Smooth scrolling for anchor links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function (e) {
                        e.preventDefault();
                        
                        const targetId = this.getAttribute('href');
                        if (targetId === "#") return;
                        
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 70,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            }
        });
    </script>

</body>
</html>