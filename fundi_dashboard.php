<?php
session_start();

// Check if user is logged in and is a fundi
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'fundi') {
header("Location: login.php");
exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "fundihire";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$message = "";

// Get fundi info
$fundi_query = "SELECT * FROM users WHERE id = $user_id";
$fundi_result = $conn->query($fundi_query);
$fundi_data = $fundi_result->fetch_assoc();
$fundi_name = $fundi_data['name'];

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$bio = mysqli_real_escape_string($conn, $_POST['bio']);
$skills = mysqli_real_escape_string($conn, $_POST['skills']);

// Update user profile
$update_profile = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', location = '$location', bio = '$bio', skills = '$skills' WHERE id = $user_id";
if ($conn->query($update_profile)) {
    $message = "Profile updated successfully!";
    // Refresh fundi data
    $fundi_result = $conn->query($fundi_query);
    $fundi_data = $fundi_result->fetch_assoc();
    $fundi_name = $fundi_data['name'];
} else {
    $message = "Error updating profile: " . $conn->error;
}
}

// Handle job application
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_job'])) {
$job_id = intval($_POST['job_id']);
$proposal = mysqli_real_escape_string($conn, $_POST['proposal']);
$bid_amount = floatval($_POST['bid_amount']);

// Check if already applied
$check_query = "SELECT * FROM job_applications WHERE job_id = $job_id AND fundi_id = $user_id";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    $message = "You have already applied for this job.";
} else {
    // Insert application
    $apply_query = "INSERT INTO job_applications (job_id, fundi_id, proposal, bid_amount, status, created_at) 
                    VALUES ($job_id, $user_id, '$proposal', $bid_amount, 'pending', NOW())";
    
    if ($conn->query($apply_query)) {
        $message = "Application submitted successfully!";
    } else {
        $message = "Error submitting application: " . $conn->error;
    }
}
}

// Handle job completion update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_job_status'])) {
    $job_id = intval($_POST['job_id']);
    $status_update = mysqli_real_escape_string($conn, $_POST['status_update']);
    
    // Check if fundi is assigned to this job
    $check_query = "SELECT j.* FROM jobs j 
                   JOIN job_applications ja ON j.id = ja.job_id 
                   WHERE j.id = $job_id AND ja.fundi_id = $user_id AND ja.status = 'accepted'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Update job progress
        $update_query = "UPDATE jobs SET progress_status = '$status_update', updated_at = NOW() WHERE id = $job_id";
        
        if ($conn->query($update_query)) {
            $message = "Job progress updated successfully!";
        } else {
            $message = "Error updating job progress: " . $conn->error;
        }
    } else {
        $message = "You are not authorized to update this job.";
    }
}

// Dashboard statistics
$stats_query = "SELECT 
            (SELECT COUNT(*) FROM job_applications WHERE fundi_id = $user_id) as total_applications,
            (SELECT COUNT(*) FROM job_applications WHERE fundi_id = $user_id AND status = 'accepted') as accepted_jobs,
            (SELECT COUNT(*) FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE ja.fundi_id = $user_id AND ja.status = 'accepted' AND j.status = 'completed') as completed_jobs,
            (SELECT SUM(ja.bid_amount) FROM job_applications ja JOIN jobs j ON ja.job_id = j.id WHERE ja.fundi_id = $user_id AND ja.status = 'accepted' AND j.status = 'completed') as total_earnings,
            (SELECT AVG(rating) FROM job_reviews WHERE reviewee_id = $user_id) as average_rating";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Initialize filter variables for reports
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get jobs for report
$jobs_query = "SELECT j.*, 
          ja.status as application_status, 
          ja.bid_amount,
          ja.created_at as application_date,
          u.name as client_name
          FROM jobs j 
          JOIN job_applications ja ON j.id = ja.job_id 
          JOIN users u ON j.client_id = u.id 
          WHERE ja.fundi_id = $user_id";

// Add date filters
if ($start_date && $end_date) {
$jobs_query .= " AND j.created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}

// Add status filter
if ($status_filter != 'all') {
$jobs_query .= " AND j.status = '$status_filter'";
}

$jobs_query .= " ORDER BY j.created_at DESC";
$jobs_result = $conn->query($jobs_query);

// Get available jobs
$available_jobs_query = "SELECT j.*, u.name as client_name, 
                    (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as applications_count
                    FROM jobs j 
                    JOIN users u ON j.client_id = u.id 
                    WHERE j.status = 'open' 
                    AND j.id NOT IN (SELECT job_id FROM job_applications WHERE fundi_id = $user_id)
                    ORDER BY j.is_featured DESC, j.created_at DESC";
$available_jobs_result = $conn->query($available_jobs_query);

// Get subscription status
$subscription_query = "SELECT * FROM subscriptions 
                  WHERE user_id = $user_id AND is_active = 1 AND end_date >= CURDATE() 
                  ORDER BY end_date DESC LIMIT 1";
$subscription_result = $conn->query($subscription_query);
$has_subscription = $subscription_result->num_rows > 0;
$subscription = $has_subscription ? $subscription_result->fetch_assoc() : null;

// Get reviews
$reviews_query = "SELECT jr.*, j.title as job_title, u.name as reviewer_name
            FROM job_reviews jr
            JOIN jobs j ON jr.job_id = j.id
            JOIN users u ON jr.reviewer_id = u.id
            WHERE jr.reviewee_id = $user_id
            ORDER BY jr.created_at DESC";
$reviews_result = $conn->query($reviews_query);

// Get skills for profile
$skills_query = "SELECT * FROM skills ORDER BY name ASC";
$skills_result = $conn->query($skills_query);
$skills_list = [];
while ($skill = $skills_result->fetch_assoc()) {
$skills_list[] = $skill;
}

// Get fundi skills
$fundi_skills = [];
if (!empty($fundi_data['skills'])) {
$fundi_skills = explode(',', $fundi_data['skills']);
$fundi_skills = array_map('trim', $fundi_skills);
}

// Set active tab based on URL parameter or default to overview
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get job details if viewing a specific job
$job_detail = null;
if ($active_tab == 'view-job' && isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    
    // Get job details
    $job_detail_query = "SELECT j.*, u.name as client_name, u.email as client_email, u.phone as client_phone,
                        ja.id as application_id, ja.proposal, ja.bid_amount, ja.status as application_status, 
                        ja.created_at as application_date
                        FROM jobs j 
                        JOIN users u ON j.client_id = u.id 
                        LEFT JOIN job_applications ja ON j.id = ja.job_id AND ja.fundi_id = $user_id
                        WHERE j.id = $job_id";
    $job_detail_result = $conn->query($job_detail_query);
    
    if ($job_detail_result->num_rows > 0) {
        $job_detail = $job_detail_result->fetch_assoc();
    }
}

// Get my applications
$my_applications_query = "SELECT j.*, 
                        ja.status as application_status, 
                        ja.bid_amount,
                        ja.proposal,
                        ja.created_at as application_date,
                        u.name as client_name
                        FROM jobs j 
                        JOIN job_applications ja ON j.id = ja.job_id 
                        JOIN users u ON j.client_id = u.id 
                        WHERE ja.fundi_id = $user_id
                        ORDER BY ja.created_at DESC";
$my_applications_result = $conn->query($my_applications_query);

// Get my active jobs (accepted applications)
$active_jobs_query = "SELECT j.*, 
                    ja.status as application_status, 
                    ja.bid_amount,
                    ja.created_at as application_date,
                    u.name as client_name,
                    u.phone as client_phone,
                    u.email as client_email
                    FROM jobs j 
                    JOIN job_applications ja ON j.id = ja.job_id 
                    JOIN users u ON j.client_id = u.id 
                    WHERE ja.fundi_id = $user_id AND ja.status = 'accepted' AND j.status = 'assigned'
                    ORDER BY j.created_at DESC";
$active_jobs_result = $conn->query($active_jobs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fundi Dashboard - FundiHire</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-light: #3b82f6;
        --secondary: #f59e0b;
        --secondary-dark: #d97706;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #3b82f6;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #64748b;
        --white: #ffffff;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --radius: 0.5rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    body {
        background-color: var(--light);
        color: var(--dark);
        line-height: 1.6;
    }

    .dashboard {
        display: grid;
        grid-template-columns: 280px 1fr;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        background: linear-gradient(to bottom, var(--primary-dark), var(--primary));
        color: var(--white);
        padding: 0;
        position: fixed;
        width: 280px;
        height: 100vh;
        overflow-y: auto;
        z-index: 100;
        box-shadow: var(--shadow);
    }

    .sidebar-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo span {
        color: var(--secondary);
    }

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 32px;
        color: var(--white);
        border: 3px solid rgba(255, 255, 255, 0.2);
    }

    .user-name {
        font-weight: 600;
        font-size: 18px;
        margin-bottom: 5px;
    }

    .user-role {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
        background-color: rgba(255, 255, 255, 0.1);
        padding: 3px 12px;
        border-radius: 20px;
    }

    .user-rating {
        margin-top: 10px;
        display: flex;
        align-items: center;
    }

    .user-rating .stars {
        color: var(--secondary);
        margin-right: 5px;
    }

    .sidebar-menu {
        padding: 20px 0;
    }

    .menu-section {
        margin-bottom: 10px;
    }

    .menu-title {
        padding: 10px 20px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.5);
    }

    .menu-items {
        list-style: none;
    }

    .menu-item {
        margin-bottom: 5px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        cursor: pointer;
    }

    .menu-link:hover, .menu-link.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--white);
        border-left-color: var(--secondary);
    }

    .menu-link i {
        margin-right: 12px;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    /* Main Content */
    .main-content {
        grid-column: 2;
        padding: 30px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark);
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        background-color: var(--primary);
        color: var(--white);
        border: none;
        border-radius: var(--radius);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: var(--shadow-sm);
    }

    .btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    .btn i {
        margin-right: 8px;
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: var(--white);
    }

    .btn-success {
        background-color: var(--success);
    }

    .btn-success:hover {
        background-color: #0da271;
    }

    .btn-danger {
        background-color: var(--danger);
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }

    .btn-warning {
        background-color: var(--warning);
    }

    .btn-warning:hover {
        background-color: var(--secondary-dark);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    /* Dashboard Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
    }

    .stat-card.primary::before {
        background-color: var(--primary);
    }

    .stat-card.success::before {
        background-color: var(--success);
    }

    .stat-card.warning::before {
        background-color: var(--warning);
    }

    .stat-card.danger::before {
        background-color: var(--danger);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 24px;
    }

    .stat-icon.primary {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--primary);
    }

    .stat-icon.success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .stat-icon.warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .stat-icon.danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .stat-info {
        flex: 1;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        color: var(--gray);
        font-size: 14px;
    }

    /* Dashboard Sections */
    .dashboard-section {
        margin-bottom: 30px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark);
    }

    .section-action {
        font-size: 14px;
        color: var(--primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .section-action i {
        margin-left: 5px;
    }

    /* Cards */
    .card {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
        display: flex;
        align-items: center;
    }

    .card-title i {
        margin-right: 10px;
        color: var(--primary);
    }

    .card-body {
        padding: 20px;
    }

    .card-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Tables */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .table th {
        font-weight: 600;
        color: var(--dark);
        background-color: rgba(0, 0, 0, 0.02);
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover td {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-open {
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--primary);
    }

    .status-assigned {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .status-completed {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-cancelled {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-accepted {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-rejected {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    /* Tabs */
    .tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .tab {
        padding: 12px 20px;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-row .form-group {
        flex: 1;
    }

    .form-check {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .form-check-input {
        margin-right: 8px;
    }

    /* Alerts */
    .alert {
        padding: 15px;
        border-radius: var(--radius);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .alert i {
        margin-right: 10px;
        font-size: 18px;
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }

    .alert-warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-left: 4px solid var(--warning);
    }

    .alert-info {
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--info);
        border-left: 4px solid var(--info);
    }

    /* Job Cards */
    .job-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .job-card {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }

    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .job-card-header {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .job-card-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark);
    }

    .job-card-body {
        padding: 15px;
        flex: 1;
    }

    .job-card-footer {
        padding: 15px;
        border-top: 1px solid var(--border-color);
        background-color: rgba(0, 0, 0, 0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .job-meta {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        color: var(--gray);
        font-size: 14px;
    }

    .job-meta i {
        margin-right: 5px;
        width: 16px;
        text-align: center;
    }

    .job-budget {
        font-weight: 600;
        color: var(--primary);
    }

    .job-featured {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: var(--secondary);
        color: var(--white);
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Reviews */
    .review-card {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 20px;
        margin-bottom: 15px;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .review-author {
        font-weight: 600;
    }

    .review-job {
        color: var(--gray);
        font-size: 14px;
    }

    .review-rating {
        color: var(--secondary);
    }

    .review-date {
        color: var(--gray);
        font-size: 14px;
    }

    .review-content {
        color: var(--dark);
        font-size: 14px;
        line-height: 1.6;
    }

    /* Filter Section */
    .filter-section {
        background-color: var(--white);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
    }

    .filter-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Subscription Banner */
    .subscription-banner {
        background: linear-gradient(to right, var(--primary), var(--primary-dark));
        color: var(--white);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .subscription-info {
        flex: 1;
    }

    .subscription-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .subscription-description {
        font-size: 14px;
        opacity: 0.9;
    }

    /* Job Detail */
    .job-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .job-detail-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .job-detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }

    .job-detail-meta-item {
        display: flex;
        align-items: center;
        color: var(--gray);
    }

    .job-detail-meta-item i {
        margin-right: 8px;
        color: var(--primary);
    }

    .job-detail-description {
        margin-bottom: 30px;
        line-height: 1.8;
    }

    .job-detail-section {
        margin-bottom: 30px;
    }

    .job-detail-section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    /* Application Cards */
    .application-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .application-card {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .application-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .application-card-header {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .application-card-title {
        font-size: 16px;
        font-weight: 600;
    }

    .application-card-body {
        padding: 15px;
    }

    .application-card-footer {
        padding: 15px;
        border-top: 1px solid var(--border-color);
        background-color: rgba(0, 0, 0, 0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: var(--white);
        border-radius: var(--radius);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: var(--gray);
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Skills Tags */
    .skills-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .skill-tag {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--primary);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .skill-tag:hover {
        background-color: var(--primary);
        color: var(--white);
    }

    .skill-tag.selected {
        background-color: var(--primary);
        color: var(--white);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .dashboard {
            grid-template-columns: 1fr;
        }

        .sidebar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
        }

        .sidebar.active {
            display: block;
        }

        .main-content {
            grid-column: 1;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .job-cards {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }

        .mobile-menu-toggle {
            display: block;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background-color: var(--primary);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .header-actions {
            width: 100%;
        }
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none;
    }

    @media (min-width: 993px) {
        .mobile-menu-close {
            display: none;
        }
    }

    .mobile-menu-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: var(--white);
        font-size: 24px;
        cursor: pointer;
    }
</style>
</head>
<body>
<div class="dashboard">
<!-- Mobile Menu Toggle -->
<div class="mobile-menu-toggle" id="mobile-menu-toggle">
    <i class="fas fa-bars"></i>
</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <button class="mobile-menu-close" id="mobile-menu-close">
        <i class="fas fa-times"></i>
    </button>
    <div class="sidebar-header">
        <div class="logo"><span>Fundi</span>Hire</div>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="user-name"><?php echo $fundi_name; ?></div>
            <div class="user-role">Fundi</div>
            <div class="user-rating">
                <div class="stars">
                    <?php 
                    $rating = round($stats['average_rating'] ?: 0);
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                            echo '<i class="fas fa-star"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <span><?php echo number_format($stats['average_rating'] ?: 0, 1); ?>/5</span>
            </div>
        </div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-title">MAIN</div>
            <ul class="menu-items">
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'overview' ? 'active' : ''; ?>" data-tab="overview">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'available-jobs' ? 'active' : ''; ?>" data-tab="available-jobs">
                        <i class="fas fa-briefcase"></i> Available Jobs
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'my-applications' ? 'active' : ''; ?>" data-tab="my-applications">
                        <i class="fas fa-file-alt"></i> My Applications
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'active-jobs' ? 'active' : ''; ?>" data-tab="active-jobs">
                        <i class="fas fa-hammer"></i> Active Jobs
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'reports' ? 'active' : ''; ?>" data-tab="reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'reviews' ? 'active' : ''; ?>" data-tab="reviews">
                        <i class="fas fa-star"></i> Reviews
                    </a>
                </li>
            </ul>
        </div>
        <div class="menu-section">
            <div class="menu-title">ACCOUNT</div>
            <ul class="menu-items">
                <li class="menu-item">
                    <a class="menu-link" id="profile-link">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                </li>
                <li class="menu-item">
                    <a href="logout.php" class="menu-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Fundi Dashboard</h1>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!$has_subscription): ?>
        <div class="subscription-banner">
            <div class="subscription-info">
                <div class="subscription-title">Upgrade to Premium</div>
                <div class="subscription-description">Get early access to new jobs, priority applications, and more for just KES 1,000 per month.</div>
            </div>
            <a href="mpesa-subscription.php" class="btn btn-warning">
                <i class="fas fa-crown"></i> Upgrade Now
            </a>
        </div>
    <?php else: ?>
        <div class="subscription-banner">
            <div class="subscription-info">
                <div class="subscription-title">Premium Subscription Active</div>
                <div class="subscription-description">Your premium benefits are active until <?php echo date('F d, Y', strtotime($subscription['end_date'])); ?>.</div>
            </div>
            <a href="mpesa-subscription.php" class="btn btn-warning">
                <i class="fas fa-crown"></i> Manage Subscription
            </a>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_applications'] ?: 0; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['accepted_jobs'] ?: 0; ?></div>
                <div class="stat-label">Accepted Jobs</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon warning">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['completed_jobs'] ?: 0; ?></div>
                <div class="stat-label">Completed Jobs</div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon danger">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">KES <?php echo number_format($stats['total_earnings'] ?: 0, 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab <?php echo $active_tab == 'overview' ? 'active' : ''; ?>" data-tab="overview">Overview</div>
        <div class="tab <?php echo $active_tab == 'available-jobs' ? 'active' : ''; ?>" data-tab="available-jobs">Available Jobs</div>
        <div class="tab <?php echo $active_tab == 'my-applications' ? 'active' : ''; ?>" data-tab="my-applications">My Applications</div>
        <div class="tab <?php echo $active_tab == 'active-jobs' ? 'active' : ''; ?>" data-tab="active-jobs">Active Jobs</div>
        <div class="tab <?php echo $active_tab == 'reports' ? 'active' : ''; ?>" data-tab="reports">Reports</div>
        <div class="tab <?php echo $active_tab == 'reviews' ? 'active' : ''; ?>" data-tab="reviews">Reviews</div>
        <?php if ($active_tab == 'view-job'): ?>
            <div class="tab active" data-tab="view-job">Job Details</div>
        <?php endif; ?>
    </div>

    <!-- Overview Tab -->
    <div id="overview" class="tab-content <?php echo $active_tab == 'overview' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Available Jobs</h2>
                <a class="section-action" data-tab="available-jobs">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="job-cards">
                <?php 
                $count = 0;
                if ($available_jobs_result->num_rows > 0): 
                    while ($job = $available_jobs_result->fetch_assoc()): 
                        if ($count++ < 3): // Show only 3 recent jobs
                ?>
                    <div class="job-card">
                        <?php if ($job['is_featured']): ?>
                            <div class="job-featured">Featured</div>
                        <?php endif; ?>
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <div class="job-meta">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($job['client_name']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-money-bill-wave"></i> KES <?php echo number_format($job['budget'], 2); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-calendar-alt"></i> Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-users"></i> Applications: <?php echo $job['applications_count']; ?>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-primary btn-sm apply-job-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>" data-budget="<?php echo $job['budget']; ?>">
                                <i class="fas fa-paper-plane"></i> Apply
                            </button>
                        </div>
                    </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No available jobs found. Check back later.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">My Recent Applications</h2>
                <a class="section-action" data-tab="my-applications">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Client</th>
                                    <th>Bid Amount</th>
                                    <th>Application Status</th>
                                    <th>Job Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 0;
                                if ($my_applications_result->num_rows > 0): 
                                    while ($job = $my_applications_result->fetch_assoc()): 
                                        if ($count++ < 5): // Show only 5 recent applications
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                                        <td>KES <?php echo number_format($job['bid_amount'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $job['application_status']; ?>"><?php echo ucfirst($job['application_status']); ?></span></td>
                                        <td><span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($job['application_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-outline btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                        endif;
                                    endwhile; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No applications found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Active Jobs</h2>
                <a class="section-action" data-tab="active-jobs">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="job-cards">
                <?php 
                $count = 0;
                if ($active_jobs_result->num_rows > 0): 
                    while ($job = $active_jobs_result->fetch_assoc()): 
                        if ($count++ < 3): // Show only 3 active jobs
                ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <div class="job-meta">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($job['client_name']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-money-bill-wave"></i> KES <?php echo number_format($job['bid_amount'], 2); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-calendar-alt"></i> Assigned: <?php echo date('M d, Y', strtotime($job['updated_at'])); ?>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-success btn-sm update-progress-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>">
                                <i class="fas fa-tasks"></i> Update Progress
                            </button>
                        </div>
                    </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any active jobs. Apply for jobs to get started.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Available Jobs Tab -->
    <div id="available-jobs" class="tab-content <?php echo $active_tab == 'available-jobs' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All Available Jobs</h2>
            </div>
            
            <div class="filter-section">
                <h3 class="filter-title">Filter Jobs</h3>
                <form class="filter-form" method="get" id="jobs-filter-form">
                    <input type="hidden" name="tab" value="available-jobs">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="Enter location">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min Budget</label>
                        <input type="number" name="min_budget" class="form-control" placeholder="Minimum budget">
                    </div>
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="job-cards">
                <?php 
                if ($available_jobs_result->num_rows > 0): 
                    mysqli_data_seek($available_jobs_result, 0); // Reset pointer
                    while ($job = $available_jobs_result->fetch_assoc()): 
                ?>
                    <div class="job-card">
                        <?php if ($job['is_featured']): ?>
                            <div class="job-featured">Featured</div>
                        <?php endif; ?>
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <div class="job-meta">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($job['client_name']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-money-bill-wave"></i> KES <?php echo number_format($job['budget'], 2); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-calendar-alt"></i> Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-users"></i> Applications: <?php echo $job['applications_count']; ?>
                            </div>
                            <p class="mt-3"><?php echo substr(htmlspecialchars($job['description']), 0, 100) . '...'; ?></p>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-primary btn-sm apply-job-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>" data-budget="<?php echo $job['budget']; ?>">
                                <i class="fas fa-paper-plane"></i> Apply
                            </button>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No available jobs found. Check back later.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My Applications Tab -->
    <div id="my-applications" class="tab-content <?php echo $active_tab == 'my-applications' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All My Applications</h2>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Client</th>
                                    <th>Bid Amount</th>
                                    <th>Application Status</th>
                                    <th>Job Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($my_applications_result->num_rows > 0): 
                                    mysqli_data_seek($my_applications_result, 0); // Reset pointer
                                    while ($job = $my_applications_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                                        <td>KES <?php echo number_format($job['bid_amount'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $job['application_status']; ?>"><?php echo ucfirst($job['application_status']); ?></span></td>
                                        <td><span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($job['application_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-outline btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No applications found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Jobs Tab -->
    <div id="active-jobs" class="tab-content <?php echo $active_tab == 'active-jobs' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">My Active Jobs</h2>
            </div>
            <div class="job-cards">
                <?php 
                if ($active_jobs_result->num_rows > 0): 
                    mysqli_data_seek($active_jobs_result, 0); // Reset pointer
                    while ($job = $active_jobs_result->fetch_assoc()): 
                ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <div class="job-meta">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($job['client_name']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($job['client_phone']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($job['client_email']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-money-bill-wave"></i> KES <?php echo number_format($job['bid_amount'], 2); ?>
                            </div>
                            <div class="job-meta">
                                <i class="fas fa-calendar-alt"></i> Assigned: <?php echo date('M d, Y', strtotime($job['updated_at'])); ?>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn btn-success btn-sm update-progress-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>">
                                <i class="fas fa-tasks"></i> Update Progress
                            </button>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You don't have any active jobs. Apply for jobs to get started.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Job Detail Tab -->
    <?php if ($active_tab == 'view-job' && $job_detail): ?>
    <div id="view-job" class="tab-content active">
        <div class="job-detail-header">
            <div>
                <h2 class="job-detail-title"><?php echo htmlspecialchars($job_detail['title']); ?></h2>
                <div class="job-detail-meta">
                    <div class="job-detail-meta-item">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($job_detail['client_name']); ?>
                    </div>
                    <div class="job-detail-meta-item">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job_detail['location']); ?>
                    </div>
                    <div class="job-detail-meta-item">
                        <i class="fas fa-money-bill-wave"></i> Budget: KES <?php echo number_format($job_detail['budget'], 2); ?>
                    </div>
                    <div class="job-detail-meta-item">
                        <i class="fas fa-calendar-alt"></i> Posted: <?php echo date('M d, Y', strtotime($job_detail['created_at'])); ?>
                    </div>
                    <div class="job-detail-meta-item">
                        <i class="fas fa-tag"></i> Status: <span class="status-badge status-<?php echo $job_detail['status']; ?>"><?php echo ucfirst($job_detail['status']); ?></span>
                    </div>
                </div>
            </div>
            <div>
                <?php if (!$job_detail['application_id'] && $job_detail['status'] == 'open'): ?>
                    <button class="btn btn-primary apply-job-btn" data-id="<?php echo $job_detail['id']; ?>" data-title="<?php echo htmlspecialchars($job_detail['title']); ?>" data-budget="<?php echo $job_detail['budget']; ?>">
                        <i class="fas fa-paper-plane"></i> Apply for this Job
                    </button>
                <?php elseif ($job_detail['application_status'] == 'accepted' && $job_detail['status'] == 'assigned'): ?>
                    <button class="btn btn-success update-progress-btn" data-id="<?php echo $job_detail['id']; ?>" data-title="<?php echo htmlspecialchars($job_detail['title']); ?>">
                        <i class="fas fa-tasks"></i> Update Progress
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="job-detail-description">
            <h3 class="job-detail-section-title">Job Description</h3>
            <p><?php echo nl2br(htmlspecialchars($job_detail['description'])); ?></p>
        </div>

        <?php if ($job_detail['application_id']): ?>
        <div class="job-detail-section">
            <h3 class="job-detail-section-title">Your Application</h3>
            <div class="card">
                <div class="card-body">
                    <div class="job-meta">
                        <i class="fas fa-money-bill-wave"></i> Your Bid: KES <?php echo number_format($job_detail['bid_amount'], 2); ?>
                    </div>
                    <div class="job-meta">
                        <i class="fas fa-tag"></i> Application Status: <span class="status-badge status-<?php echo $job_detail['application_status']; ?>"><?php echo ucfirst($job_detail['application_status']); ?></span>
                    </div>
                    <div class="job-meta">
                        <i class="fas fa-calendar-alt"></i> Applied: <?php echo date('M d, Y', strtotime($job_detail['application_date'])); ?>
                    </div>
                    
                    <h4 class="mt-3">Your Proposal</h4>
                    <p><?php echo nl2br(htmlspecialchars($job_detail['proposal'])); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($job_detail['application_status'] == 'accepted' && $job_detail['status'] == 'assigned'): ?>
        <div class="job-detail-section">
            <h3 class="job-detail-section-title">Client Contact Information</h3>
            <div class="card">
                <div class="card-body">
                    <div class="job-meta">
                        <i class="fas fa-user"></i> Name: <?php echo htmlspecialchars($job_detail['client_name']); ?>
                    </div>
                    <div class="job-meta">
                        <i class="fas fa-phone"></i> Phone: <?php echo htmlspecialchars($job_detail['client_phone']); ?>
                    </div>
                    <div class="job-meta">
                        <i class="fas fa-envelope"></i> Email: <?php echo htmlspecialchars($job_detail['client_email']); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reports Tab -->
    <div id="reports" class="tab-content <?php echo $active_tab == 'reports' ? 'active' : ''; ?>">
        <div class="filter-section">
            <h3 class="filter-title">Filter Reports</h3>
            <form class="filter-form" method="get" id="reports-filter-form">
                <input type="hidden" name="tab" value="reports">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="open" <?php echo $status_filter == 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="assigned" <?php echo $status_filter == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Applications Report</h3>
                <div>
                    <button class="btn btn-outline btn-sm" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Client</th>
                                <th>Bid Amount</th>
                                <th>Application Status</th>
                                <th>Job Status</th>
                                <th>Application Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jobs_result->num_rows > 0): ?>
                                <?php mysqli_data_seek($jobs_result, 0); // Reset pointer ?>
                                <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                                        <td>KES <?php echo number_format($job['bid_amount'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $job['application_status']; ?>"><?php echo ucfirst($job['application_status']); ?></span></td>
                                        <td><span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($job['application_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-outline btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No applications found for the selected period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Tab -->
    <div id="reviews" class="tab-content <?php echo $active_tab == 'reviews' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">My Reviews</h2>
            </div>
            
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <div class="review-author"><?php echo htmlspecialchars($review['reviewer_name']); ?></div>
                                <div class="review-job">Job: <?php echo htmlspecialchars($review['job_title']); ?></div>
                            </div>
                            <div class="review-rating">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="review-content">
                            <?php echo htmlspecialchars($review['review']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No reviews found. Complete jobs to receive reviews from clients.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>

<!-- Profile Modal -->
<div id="profile-modal" class="modal">
<div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-user-circle"></i> My Profile</h3>
        <button class="modal-close">&times;</button>
    </div>
    <form id="profile-form" method="post">
        <input type="hidden" name="update_profile" value="1">
        <div class="modal-body">
            <div class="form-group">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($fundi_data['name']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($fundi_data['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($fundi_data['phone']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($fundi_data['location']); ?>">
            </div>

            <div class="form-group">
                <label for="bio" class="form-label">Bio</label>
                <textarea id="bio" name="bio" class="form-control"><?php echo htmlspecialchars($fundi_data['bio']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="skills" class="form-label">Skills</label>
                <input type="text" id="skills" name="skills" class="form-control" value="<?php echo htmlspecialchars($fundi_data['skills']); ?>" placeholder="e.g., Plumbing, Electrical, Carpentry">
                <div class="skills-container">
                    <?php foreach ($skills_list as $skill): ?>
                        <div class="skill-tag <?php echo in_array($skill['name'], $fundi_skills) ? 'selected' : ''; ?>" data-skill="<?php echo htmlspecialchars($skill['name']); ?>"><?php echo htmlspecialchars($skill['name']); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Save Changes</button>
        </div>
    </form>
</div>
</div>

<!-- Apply Job Modal -->
<div id="apply-job-modal" class="modal">
<div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-paper-plane"></i> Apply for <span id="job-title-placeholder"></span></h3>
        <button class="modal-close">&times;</button>
    </div>
    <form id="apply-job-form" method="post">
        <input type="hidden" name="apply_job" value="1">
        <input type="hidden" name="job_id" id="job_id">
        <div class="modal-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Provide a compelling proposal to increase your chances of getting hired.
            </div>
            
            <div class="form-group">
                <label for="proposal" class="form-label">Your Proposal *</label>
                <textarea id="proposal" name="proposal" class="form-control" required></textarea>
                <small>Explain why you're the best fit for this job and how you plan to approach it.</small>
            </div>
            
            <div class="form-group">
                <label for="bid_amount" class="form-label">Your Bid (KES) *</label>
                <input type="number" id="bid_amount" name="bid_amount" class="form-control" min="1" required>
                <small>Suggested budget: KES <span id="suggested-budget"></span></small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Submit Application</button>
        </div>
    </form>
</div>
</div>

<!-- Update Progress Modal -->
<div id="update-progress-modal" class="modal">
<div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-tasks"></i> Update Progress for <span id="progress-job-title"></span></h3>
        <button class="modal-close">&times;</button>
    </div>
    <form id="update-progress-form" method="post">
        <input type="hidden" name="update_job_status" value="1">
        <input type="hidden" name="job_id" id="progress_job_id">
        <div class="modal-body">
            <div class="form-group">
                <label for="status_update" class="form-label">Progress Update *</label>
                <select id="status_update" name="status_update" class="form-control" required>
                    <option value="started">Just Started</option>
                    <option value="in_progress">In Progress (25%)</option>
                    <option value="halfway">Halfway Done (50%)</option>
                    <option value="almost_done">Almost Done (75%)</option>
                    <option value="completed">Completed (100%)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="progress_notes" class="form-label">Additional Notes</label>
                <textarea id="progress_notes" name="progress_notes" class="form-control"></textarea>
                <small>Provide any additional information about the current status of the job.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Update Progress</button>
        </div>
    </form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Sidebar menu links
    const menuLinks = document.querySelectorAll('.menu-link[data-tab]');
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Update active tab
            document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Section actions (View All links)
    const sectionActions = document.querySelectorAll('.section-action[data-tab]');
    sectionActions.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
            
            // Update sidebar active link
            document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
            document.querySelector(`.menu-link[data-tab="${tabId}"]`).classList.add('active');
        });
    });
    
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const sidebar = document.getElementById('sidebar');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
        });
    }
    
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
    
    // Profile Modal
    const profileModal = document.getElementById('profile-modal');
    const profileLink = document.getElementById('profile-link');
    
    if (profileLink) {
        profileLink.addEventListener('click', function(e) {
            e.preventDefault();
            profileModal.classList.add('active');
        });
    }
    
    // Apply Job Modal
    const applyJobModal = document.getElementById('apply-job-modal');
    const applyJobBtns = document.querySelectorAll('.apply-job-btn');
    const jobIdInput = document.getElementById('job_id');
    const jobTitlePlaceholder = document.getElementById('job-title-placeholder');
    const suggestedBudget = document.getElementById('suggested-budget');
    
    applyJobBtns.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-id');
            const jobTitle = this.getAttribute('data-title');
            const budget = this.getAttribute('data-budget');
            
            jobIdInput.value = jobId;
            jobTitlePlaceholder.textContent = jobTitle;
            suggestedBudget.textContent = Number(budget).toLocaleString();
            
            applyJobModal.classList.add('active');
        });
    });
    
    // Update Progress Modal
    const updateProgressModal = document.getElementById('update-progress-modal');
    const updateProgressBtns = document.querySelectorAll('.update-progress-btn');
    const progressJobIdInput = document.getElementById('progress_job_id');
    const progressJobTitle = document.getElementById('progress-job-title');
    
    updateProgressBtns.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-id');
            const jobTitle = this.getAttribute('data-title');
            
            progressJobIdInput.value = jobId;
            progressJobTitle.textContent = jobTitle;
            
            updateProgressModal.classList.add('active');
        });
    });
    
    // View Job functionality
    const viewJobBtns = document.querySelectorAll('.view-job-btn');
    viewJobBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.getAttribute('data-id');
            
            // Update URL and navigate to job details page
            const url = new URL(window.location);
            url.searchParams.set('tab', 'view-job');
            url.searchParams.set('id', jobId);
            window.location.href = url.toString();
        });
    });
    
    // Close all modals
    const closeButtons = document.querySelectorAll('.modal-close, .modal-close-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.classList.remove('active');
            });
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Skills tags
    const skillTags = document.querySelectorAll('.skill-tag');
    const skillsInput = document.getElementById('skills');
    
    if (skillTags.length > 0 && skillsInput) {
        skillTags.forEach(tag => {
            tag.addEventListener('click', function() {
                const skill = this.getAttribute('data-skill');
                let currentSkills = skillsInput.value.split(',').map(s => s.trim()).filter(s => s !== '');
                
                if (this.classList.contains('selected')) {
                    // Remove skill
                    currentSkills = currentSkills.filter(s => s !== skill);
                    this.classList.remove('selected');
                } else {
                    // Add skill
                    if (!currentSkills.includes(skill)) {
                        currentSkills.push(skill);
                    }
                    this.classList.add('selected');
                }
                
                skillsInput.value = currentSkills.join(', ');
            });
        });
    }
    
    // Handle form submissions to stay on the same page
    const filterForms = document.querySelectorAll('#reports-filter-form, #jobs-filter-form');
    filterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            // Update URL without page reload
            const url = new URL(window.location);
            for (const [key, value] of params) {
                url.searchParams.set(key, value);
            }
            window.history.pushState({}, '', url);
            
            // Reload the page to apply filters
            window.location.reload();
        });
    });
});

// Export to CSV function
function exportToCSV() {
    const table = document.querySelector('.tab-content.active table');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Get the text content and clean it
            let data = cols[j].textContent.replace(/(\r\n|\n|\r)/gm, '').trim();
            
            // If this is the last column (Actions), skip it
            if (cols[j].querySelector('.btn')) {
                continue;
            }
            
            // Quote the data and escape any quotes
            data = '"' + data.replace(/"/g, '""') + '"';
            row.push(data);
        }
        
        csv.push(row.join(','));
    }
    
    // Create a CSV file and download it
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'fundihire_report_<?php echo date('Y-m-d'); ?>.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</body>
</html>

