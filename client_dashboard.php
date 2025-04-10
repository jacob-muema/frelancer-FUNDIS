<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
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

// Get client info
$client_query = "SELECT * FROM users WHERE id = $user_id";
$client_result = $conn->query($client_query);
$client_data = $client_result->fetch_assoc();
$client_name = $client_data['name'];

// Handle job posting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_job'])) {
$title = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$budget = floatval($_POST['budget']);
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$skills = isset($_POST['skills']) ? mysqli_real_escape_string($conn, $_POST['skills']) : '';

// Insert job into database
$insert_query = "INSERT INTO jobs (client_id, title, description, location, budget, is_featured, status, payment_status, created_at) 
                VALUES ($user_id, '$title', '$description', '$location', $budget, $is_featured, 'open', 'unpaid', NOW())";

if ($conn->query($insert_query)) {
    $job_id = $conn->insert_id;
    
    // Record transaction for job posting fee if applicable
    $fee_query = "SELECT * FROM platform_fees WHERE fee_type = 'job_posting' AND is_active = 1";
    $fee_result = $conn->query($fee_query);
    if ($fee_result->num_rows > 0) {
        $fee_data = $fee_result->fetch_assoc();
        $posting_fee = $fee_data['fixed_amount'];
        
        if ($posting_fee > 0) {
            $transaction_query = "INSERT INTO transactions (user_id, job_id, transaction_type, amount, status, payment_method) 
                                VALUES ($user_id, $job_id, 'fee_payment', $posting_fee, 'completed', 'direct')";
            $conn->query($transaction_query);
        }
    }
    
    $message = "Job posted successfully!";
} else {
    $message = "Error posting job: " . $conn->error;
}
}

// Handle application approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_action'])) {
    $application_id = intval($_POST['application_id']);
    $job_id = intval($_POST['job_id']);
    $action = $_POST['action']; // 'accept' or 'reject'
    
    if ($action == 'accept') {
        // Update application status to accepted
        $update_application = "UPDATE job_applications SET status = 'accepted' WHERE id = $application_id";
        
        // Update job status to assigned
        $update_job = "UPDATE jobs SET status = 'assigned' WHERE id = $job_id AND client_id = $user_id";
        
        // Reject all other applications for this job
        $reject_others = "UPDATE job_applications SET status = 'rejected' 
                         WHERE job_id = $job_id AND id != $application_id AND status = 'pending'";
        
        if ($conn->query($update_application) && $conn->query($update_job) && $conn->query($reject_others)) {
            $message = "Application accepted successfully. The fundi has been assigned to this job.";
        } else {
            $message = "Error accepting application: " . $conn->error;
        }
    } elseif ($action == 'reject') {
        // Update application status to rejected
        $update_application = "UPDATE job_applications SET status = 'rejected' WHERE id = $application_id";
        
        if ($conn->query($update_application)) {
            $message = "Application rejected successfully.";
        } else {
            $message = "Error rejecting application: " . $conn->error;
        }
    }
}

// Handle job completion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_job'])) {
    $job_id = intval($_POST['job_id']);
    
    // Update job status to completed
    $update_job = "UPDATE jobs SET status = 'completed' WHERE id = $job_id AND client_id = $user_id";
    
    if ($conn->query($update_job)) {
        $message = "Job marked as completed successfully. Please proceed to payment.";
    } else {
        $message = "Error completing job: " . $conn->error;
    }
}

// Handle fee payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_fee'])) {
$job_id = intval($_POST['job_id']);
$amount = floatval($_POST['amount']);
$fee_amount = floatval($_POST['fee_amount']);

// Update job payment status
$update_job = "UPDATE jobs SET payment_status = 'paid' WHERE id = $job_id AND client_id = $user_id";
if ($conn->query($update_job)) {
    // Record the transaction
    $insert_transaction = "INSERT INTO transactions (user_id, job_id, transaction_type, amount, fee_amount, status, payment_method) 
                        VALUES ($user_id, $job_id, 'fee_payment', $amount, $fee_amount, 'completed', 'direct')";
    $conn->query($insert_transaction);
    
    $message = "Payment successful! Your job has been marked as paid.";
} else {
    $message = "Error: Unable to process payment. Please try again.";
}
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$bio = mysqli_real_escape_string($conn, $_POST['bio']);

// Update user profile
$update_profile = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', location = '$location', bio = '$bio' WHERE id = $user_id";
if ($conn->query($update_profile)) {
    $message = "Profile updated successfully!";
    // Refresh client data
    $client_result = $conn->query($client_query);
    $client_data = $client_result->fetch_assoc();
    $client_name = $client_data['name'];
} else {
    $message = "Error updating profile: " . $conn->error;
}
}

// Get platform fees
$fees_query = "SELECT * FROM platform_fees WHERE is_active = 1";
$fees_result = $conn->query($fees_query);
$fees = [];
while ($fee = $fees_result->fetch_assoc()) {
$fees[$fee['fee_type']] = $fee;
}

// Get completion fee percentage
$completion_fee_percentage = isset($fees['job_completion']) ? floatval($fees['job_completion']['percentage']) : 5;

// Dashboard statistics
$stats_query = "SELECT 
          (SELECT COUNT(*) FROM jobs WHERE client_id = $user_id) as total_jobs,
          (SELECT COUNT(*) FROM jobs WHERE client_id = $user_id AND status = 'completed') as completed_jobs,
          (SELECT COUNT(*) FROM jobs WHERE client_id = $user_id AND status = 'open') as open_jobs,
          (SELECT COUNT(*) FROM jobs WHERE client_id = $user_id AND status = 'assigned') as assigned_jobs,
          (SELECT SUM(budget) FROM jobs WHERE client_id = $user_id AND status = 'completed') as total_spent,
          (SELECT SUM(fee_amount) FROM transactions WHERE user_id = $user_id AND transaction_type = 'fee_payment') as total_fees_paid";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Initialize filter variables for reports
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get jobs for report
$jobs_query = "SELECT j.*, 
        (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as applications_count
        FROM jobs j 
        WHERE j.client_id = $user_id";

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

// Get recent transactions
$transactions_query = "SELECT t.*, j.title as job_title 
                FROM transactions t 
                LEFT JOIN jobs j ON t.job_id = j.id 
                WHERE t.user_id = $user_id 
                ORDER BY t.created_at DESC LIMIT 5";
$transactions_result = $conn->query($transactions_query);

// Get subscription status
$subscription_query = "SELECT * FROM subscriptions 
                WHERE user_id = $user_id AND is_active = 1 AND end_date >= CURDATE() 
                ORDER BY end_date DESC LIMIT 1";
$subscription_result = $conn->query($subscription_query);
$has_subscription = $subscription_result->num_rows > 0;
$subscription = $has_subscription ? $subscription_result->fetch_assoc() : null;

// Get skills for job posting
$skills_query = "SELECT * FROM skills ORDER BY name ASC";
$skills_result = $conn->query($skills_query);
$skills_list = [];
while ($skill = $skills_result->fetch_assoc()) {
$skills_list[] = $skill;
}

// Set active tab based on URL parameter or default to overview
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Get job details if viewing a specific job
$job_detail = null;
$job_applications = [];
if ($active_tab == 'view-job' && isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    
    // Get job details
    $job_detail_query = "SELECT j.*, u.name as client_name 
                        FROM jobs j 
                        JOIN users u ON j.client_id = u.id 
                        WHERE j.id = $job_id AND j.client_id = $user_id";
    $job_detail_result = $conn->query($job_detail_query);
    
    if ($job_detail_result->num_rows > 0) {
        $job_detail = $job_detail_result->fetch_assoc();
        
        // Get applications for this job
        $applications_query = "SELECT ja.*, u.name as fundi_name, u.email as fundi_email, u.phone as fundi_phone, 
                              u.location as fundi_location, u.skills as fundi_skills, u.bio as fundi_bio,
                              (SELECT AVG(rating) FROM job_reviews WHERE reviewee_id = ja.fundi_id) as fundi_rating
                              FROM job_applications ja 
                              JOIN users u ON ja.fundi_id = u.id 
                              WHERE ja.job_id = $job_id
                              ORDER BY ja.created_at DESC";
        $applications_result = $conn->query($applications_query);
        
        while ($application = $applications_result->fetch_assoc()) {
            $job_applications[] = $application;
        }
    }
}

// Get my posted jobs
$my_jobs_query = "SELECT j.*, 
                 (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as applications_count,
                 (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id AND status = 'pending') as pending_applications
                 FROM jobs j 
                 WHERE j.client_id = $user_id
                 ORDER BY j.created_at DESC";
$my_jobs_result = $conn->query($my_jobs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Dashboard - FundiHire</title>
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

    .status-paid {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-unpaid {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .status-accepted {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .status-rejected {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
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

    .fundi-info {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .fundi-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 20px;
        color: var(--white);
    }

    .fundi-details {
        flex: 1;
    }

    .fundi-name {
        font-weight: 600;
        margin-bottom: 3px;
    }

    .fundi-rating {
        color: var(--secondary);
        font-size: 14px;
    }

    .fundi-meta {
        margin-bottom: 10px;
        color: var(--gray);
        font-size: 14px;
    }

    .fundi-meta i {
        margin-right: 5px;
        width: 16px;
        text-align: center;
    }

    .application-proposal {
        margin-bottom: 15px;
        line-height: 1.6;
    }

    .application-bid {
        font-weight: 600;
        color: var(--primary);
        font-size: 18px;
        margin-bottom: 15px;
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
        position: relative;
    }

    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .job-card-header {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .job-card-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .job-card-body {
        padding: 15px;
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

    .job-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .job-badge-applications {
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--primary);
    }

    .job-badge-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
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

        .job-cards, .application-cards {
            grid-template-columns: 1fr;
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
        <div class="logo"><span>Task</span>Hero</div>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-name"><?php echo $client_name; ?></div>
            <div class="user-role">Client</div>
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
                    <a class="menu-link <?php echo $active_tab == 'my-jobs' ? 'active' : ''; ?>" data-tab="my-jobs">
                        <i class="fas fa-briefcase"></i> My Jobs
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" id="post-job-link">
                        <i class="fas fa-plus-circle"></i> Post a Job
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'reports' ? 'active' : ''; ?>" data-tab="reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link <?php echo $active_tab == 'payments' ? 'active' : ''; ?>" data-tab="payments">
                        <i class="fas fa-credit-card"></i> Payments
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
        <h1 class="page-title">Client Dashboard</h1>
        <div class="header-actions">
            <button class="btn" id="post-job-btn">
                <i class="fas fa-plus"></i> Post a New Job
            </button>
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
                <div class="subscription-description">Get featured job listings, priority support, and more for just KES 1,000 per month.</div>
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
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_jobs'] ?: 0; ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['completed_jobs'] ?: 0; ?></div>
                <div class="stat-label">Completed Jobs</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon warning">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['assigned_jobs'] ?: 0; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon danger">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">KES <?php echo number_format($stats['total_spent'] ?: 0, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab <?php echo $active_tab == 'overview' ? 'active' : ''; ?>" data-tab="overview">Overview</div>
        <div class="tab <?php echo $active_tab == 'my-jobs' ? 'active' : ''; ?>" data-tab="my-jobs">My Jobs</div>
        <div class="tab <?php echo $active_tab == 'reports' ? 'active' : ''; ?>" data-tab="reports">Reports</div>
        <div class="tab <?php echo $active_tab == 'payments' ? 'active' : ''; ?>" data-tab="payments">Payments</div>
        <?php if ($active_tab == 'view-job'): ?>
            <div class="tab active" data-tab="view-job">Job Details</div>
        <?php endif; ?>
    </div>

    <!-- Overview Tab -->
    <div id="overview" class="tab-content <?php echo $active_tab == 'overview' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">My Recent Jobs</h2>
                <a class="section-action" data-tab="my-jobs">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="job-cards">
                <?php 
                $count = 0;
                if ($my_jobs_result->num_rows > 0): 
                    mysqli_data_seek($my_jobs_result, 0); // Reset pointer
                    while ($job = $my_jobs_result->fetch_assoc()): 
                        if ($count++ < 3): // Show only 3 recent jobs
                ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <?php if ($job['pending_applications'] > 0): ?>
                                <span class="job-badge job-badge-pending"><?php echo $job['pending_applications']; ?> pending</span>
                            <?php else: ?>
                                <span class="job-badge job-badge-applications"><?php echo $job['applications_count']; ?> applications</span>
                            <?php endif; ?>
                        </div>
                        <div class="job-card-body">
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
                                <i class="fas fa-tag"></i> Status: <span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <?php if ($job['status'] == 'assigned'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="complete_job" value="1">
                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Mark Complete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You haven't posted any jobs yet. Click "Post a Job" to get started.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Recent Transactions</h2>
                <a class="section-action" data-tab="payments">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($transactions_result->num_rows > 0): ?>
                                    <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $transaction['id']; ?></td>
                                            <td><?php echo $transaction['job_title'] ? htmlspecialchars($transaction['job_title']) : 'N/A'; ?></td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $transaction['transaction_type'])); ?></td>
                                            <td>KES <?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td><span class="status-badge status-<?php echo $transaction['status']; ?>"><?php echo ucfirst($transaction['status']); ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Jobs Tab -->
    <div id="my-jobs" class="tab-content <?php echo $active_tab == 'my-jobs' ? 'active' : ''; ?>">
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">All My Posted Jobs</h2>
                <button class="btn btn-sm" id="my-jobs-post-btn">
                    <i class="fas fa-plus"></i> Post New Job
                </button>
            </div>
            
            <div class="filter-section">
                <h3 class="filter-title">Filter Jobs</h3>
                <form class="filter-form" method="get" id="jobs-filter-form">
                    <input type="hidden" name="tab" value="my-jobs">
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
            
            <div class="job-cards">
                <?php 
                if ($my_jobs_result->num_rows > 0): 
                    mysqli_data_seek($my_jobs_result, 0); // Reset pointer
                    while ($job = $my_jobs_result->fetch_assoc()): 
                ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <?php if ($job['pending_applications'] > 0): ?>
                                <span class="job-badge job-badge-pending"><?php echo $job['pending_applications']; ?> pending</span>
                            <?php else: ?>
                                <span class="job-badge job-badge-applications"><?php echo $job['applications_count']; ?> applications</span>
                            <?php endif; ?>
                        </div>
                        <div class="job-card-body">
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
                                <i class="fas fa-tag"></i> Status: <span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span>
                            </div>
                            <?php if ($job['status'] == 'completed'): ?>
                                <div class="job-meta">
                                    <i class="fas fa-credit-card"></i> Payment: <span class="status-badge status-<?php echo $job['payment_status']; ?>"><?php echo ucfirst($job['payment_status']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="job-card-footer">
                            <button class="btn btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <?php if ($job['status'] == 'assigned'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="complete_job" value="1">
                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Mark Complete
                                    </button>
                                </form>
                            <?php elseif ($job['status'] == 'completed' && $job['payment_status'] == 'unpaid'): ?>
                                <button class="btn btn-warning btn-sm pay-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>" data-amount="<?php echo $job['budget']; ?>">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You haven't posted any jobs yet. Click "Post a Job" to get started.
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
                <?php if ($job_detail['status'] == 'assigned'): ?>
                    <form method="post">
                        <input type="hidden" name="complete_job" value="1">
                        <input type="hidden" name="job_id" value="<?php echo $job_detail['id']; ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>
                <?php elseif ($job_detail['status'] == 'completed' && $job_detail['payment_status'] == 'unpaid'): ?>
                    <button class="btn btn-warning pay-btn" data-id="<?php echo $job_detail['id']; ?>" data-title="<?php echo htmlspecialchars($job_detail['title']); ?>" data-amount="<?php echo $job_detail['budget']; ?>">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="job-detail-description">
            <h3 class="job-detail-section-title">Job Description</h3>
            <p><?php echo nl2br(htmlspecialchars($job_detail['description'])); ?></p>
        </div>

        <div class="job-detail-section">
            <h3 class="job-detail-section-title">Applications (<?php echo count($job_applications); ?>)</h3>
            
            <?php if (empty($job_applications)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No applications received yet.
                </div>
            <?php else: ?>
                <div class="application-cards">
                    <?php foreach ($job_applications as $application): ?>
                        <div class="application-card">
                            <div class="application-card-header">
                                <h3 class="application-card-title">Application #<?php echo $application['id']; ?></h3>
                                <span class="status-badge status-<?php echo $application['status']; ?>"><?php echo ucfirst($application['status']); ?></span>
                            </div>
                            <div class="application-card-body">
                                <div class="fundi-info">
                                    <div class="fundi-avatar">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="fundi-details">
                                        <div class="fundi-name"><?php echo htmlspecialchars($application['fundi_name']); ?></div>
                                        <div class="fundi-rating">
                                            <?php 
                                            $rating = round($application['fundi_rating'] ?: 0);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <span><?php echo number_format($application['fundi_rating'] ?: 0, 1); ?>/5</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="fundi-meta">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($application['fundi_location']); ?>
                                </div>
                                <div class="fundi-meta">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($application['fundi_email']); ?>
                                </div>
                                <div class="fundi-meta">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($application['fundi_phone']); ?>
                                </div>
                                
                                <div class="application-bid">
                                    Bid Amount: KES <?php echo number_format($application['bid_amount'], 2); ?>
                                </div>
                                
                                <h4>Proposal:</h4>
                                <div class="application-proposal">
                                    <?php echo nl2br(htmlspecialchars($application['proposal'])); ?>
                                </div>
                            </div>
                            <div class="application-card-footer">
                                <?php if ($application['status'] == 'pending'): ?>
                                    <form method="post">
                                        <input type="hidden" name="application_action" value="1">
                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                        <input type="hidden" name="job_id" value="<?php echo $job_detail['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="application_action" value="1">
                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                        <input type="hidden" name="job_id" value="<?php echo $job_detail['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                <?php elseif ($application['status'] == 'accepted'): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Accepted</span>
                                <?php elseif ($application['status'] == 'rejected'): ?>
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
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
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Jobs Report</h3>
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
                                <th>ID</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Budget</th>
                                <th>Applications</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jobs_result->num_rows > 0): ?>
                                <?php mysqli_data_seek($jobs_result, 0); // Reset pointer ?>
                                <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $job['id']; ?></td>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['location']); ?></td>
                                        <td>KES <?php echo number_format($job['budget'], 2); ?></td>
                                        <td><?php echo $job['applications_count']; ?></td>
                                        <td><span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                        <td><span class="status-badge status-<?php echo $job['payment_status']; ?>"><?php echo ucfirst($job['payment_status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($job['status'] == 'completed' && $job['payment_status'] == 'unpaid'): ?>
                                                    <button class="btn btn-success btn-sm pay-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>" data-amount="<?php echo $job['budget']; ?>">
                                                        <i class="fas fa-credit-card"></i> Pay
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline btn-sm view-job-btn" data-id="<?php echo $job['id']; ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No jobs found for the selected period.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Tab -->
    <div id="payments" class="tab-content <?php echo $active_tab == 'payments' ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Information</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> A service fee of <?php echo $completion_fee_percentage; ?>% is charged on all completed jobs. This fee helps us maintain and improve the platform.
                </div>
                
                <h4>Fee Structure:</h4>
                <ul>
                    <li>Job Posting Fee: KES <?php echo number_format($fees['job_posting']['fixed_amount'] ?? 0, 2); ?></li>
                    <li>Job Completion Fee: <?php echo $completion_fee_percentage; ?>% of job budget</li>
                    <li>Featured Listing Fee: KES <?php echo number_format($fees['featured_listing']['fixed_amount'] ?? 0, 2); ?></li>
                </ul>
                
                <p>Total fees paid to date: <strong>KES <?php echo number_format($stats['total_fees_paid'] ?: 0, 2); ?></strong></p>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Jobs Requiring Payment</h2>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Budget</th>
                                    <th>Status</th>
                                    <th>Completion Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $unpaid_jobs = false;
                                if ($jobs_result->num_rows > 0): 
                                    mysqli_data_seek($jobs_result, 0); // Reset pointer
                                    while ($job = $jobs_result->fetch_assoc()): 
                                        if ($job['status'] == 'completed' && $job['payment_status'] == 'unpaid'):
                                            $unpaid_jobs = true;
                                ?>
                                    <tr>
                                        <td>#<?php echo $job['id']; ?></td>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td>KES <?php echo number_format($job['budget'], 2); ?></td>
                                        <td><span class="status-badge status-completed">Completed</span></td>
                                        <td><?php echo date('M d, Y', strtotime($job['updated_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-success btn-sm pay-btn" data-id="<?php echo $job['id']; ?>" data-title="<?php echo htmlspecialchars($job['title']); ?>" data-amount="<?php echo $job['budget']; ?>">
                                                <i class="fas fa-credit-card"></i> Pay Now
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                        endif;
                                    endwhile; 
                                endif;
                                
                                if (!$unpaid_jobs):
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No jobs requiring payment.</td>
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
                <h2 class="section-title">Payment History</h2>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($transactions_result->num_rows > 0): 
                                    mysqli_data_seek($transactions_result, 0); // Reset pointer
                                    while ($transaction = $transactions_result->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td>#<?php echo $transaction['id']; ?></td>
                                        <td><?php echo $transaction['job_title'] ? htmlspecialchars($transaction['job_title']) : 'N/A'; ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $transaction['transaction_type'])); ?></td>
                                        <td>KES <?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td>KES <?php echo number_format($transaction['fee_amount'] ?? 0, 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $transaction['status']; ?>"><?php echo ucfirst($transaction['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></td>
                                    </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No payment history found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
<div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title">Pay for <span id="job-title-placeholder"></span></h3>
        <button class="modal-close">&times;</button>
    </div>
    <form id="payment-form" method="post">
        <input type="hidden" name="pay_fee" value="1">
        <input type="hidden" name="job_id" id="job_id">
        <input type="hidden" name="amount" id="amount">
        <input type="hidden" name="fee_amount" id="fee_amount">
        <div class="modal-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> A service fee of <?php echo $completion_fee_percentage; ?>% is charged on all completed jobs.
            </div>
            
            <div class="form-group">
                <label for="job_amount" class="form-label">Job Amount</label>
                <input type="text" id="job_amount" class="form-control" readonly>
            </div>
            
            <div class="form-group">
                <label for="fee_amount_display" class="form-label">Service Fee (<?php echo $completion_fee_percentage; ?>%)</label>
                <input type="text" id="fee_amount_display" class="form-control" readonly>
            </div>
            
            <div class="form-group">
                <label for="total_amount" class="form-label">Total Amount to Pay</label>
                <input type="text" id="total_amount" class="form-control" readonly>
            </div>
            
            <p><strong>Note:</strong> In a real system, this would connect to a payment gateway. For demonstration purposes, clicking "Pay Now" will mark the job as paid.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Pay Now</button>
        </div>
    </form>
</div>
</div>

<!-- Post Job Modal -->
<div id="post-job-modal" class="modal">
<div class="modal-content">
    <div class="modal-header">
        <h3 class="modal-title"><i class="fas fa-plus-circle"></i> Post a New Job</h3>
        <button class="modal-close">&times;</button>
    </div>
    <form id="post-job-form" method="post">
        <input type="hidden" name="post_job" value="1">
        <div class="modal-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php if (isset($fees['job_posting']) && $fees['job_posting']['fixed_amount'] > 0): ?>
                    A job posting fee of KES <?php echo number_format($fees['job_posting']['fixed_amount'], 2); ?> will be charged when you post this job.
                <?php else: ?>
                    Fill in all the details below to post your job. The more details you provide, the better responses you'll get from fundis.
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="title" class="form-label">Job Title *</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Job Description *</label>
                <textarea id="description" name="description" class="form-control" required></textarea>
                <small>Provide a detailed description of the job, including requirements and expectations.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location" class="form-label">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="budget" class="form-label">Budget (KES) *</label>
                    <input type="number" id="budget" name="budget" class="form-control" min="1" required>
                </div>
            </div>

            <div class="form-group">
                <label for="skills" class="form-label">Required Skills</label>
                <input type="text" id="skills" name="skills" class="form-control" placeholder="e.g., Plumbing, Electrical, Carpentry">
                <div class="skills-container">
                    <?php foreach ($skills_list as $skill): ?>
                        <div class="skill-tag" data-skill="<?php echo htmlspecialchars($skill['name']); ?>"><?php echo htmlspecialchars($skill['name']); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input">
                <label for="is_featured" class="form-check-label">Feature this job (KES <?php echo number_format($fees['featured_listing']['fixed_amount'] ?? 500, 2); ?> extra)</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Post Job</button>
        </div>
    </form>
</div>
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
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($client_data['name']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($client_data['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone *</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($client_data['phone']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($client_data['location']); ?>">
            </div>

            <div class="form-group">
                <label for="bio" class="form-label">Bio</label>
                <textarea id="bio" name="bio" class="form-control"><?php echo htmlspecialchars($client_data['bio']); ?></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close-btn">Cancel</button>
            <button type="submit" class="btn btn-success">Save Changes</button>
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
    
    // Payment Modal
    const paymentModal = document.getElementById('payment-modal');
    const payButtons = document.querySelectorAll('.pay-btn');
    const closeButtons = document.querySelectorAll('.modal-close, .modal-close-btn');
    const jobIdInput = document.getElementById('job_id');
    const amountInput = document.getElementById('amount');
    const feeAmountInput = document.getElementById('fee_amount');
    const jobAmountDisplay = document.getElementById('job_amount');
    const feeAmountDisplay = document.getElementById('fee_amount_display');
    const totalAmountDisplay = document.getElementById('total_amount');
    const jobTitlePlaceholder = document.getElementById('job-title-placeholder');
    const feePercentage = <?php echo $completion_fee_percentage; ?> / 100;
    
    payButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = this.getAttribute('data-id');
            const jobTitle = this.getAttribute('data-title');
            const amount = parseFloat(this.getAttribute('data-amount'));
            const feeAmount = amount * feePercentage;
            const totalAmount = amount + feeAmount;
            
            jobIdInput.value = jobId;
            amountInput.value = amount;
            feeAmountInput.value = feeAmount;
            jobTitlePlaceholder.textContent = jobTitle;
            
            jobAmountDisplay.value = 'KES ' + amount.toFixed(2);
            feeAmountDisplay.value = 'KES ' + feeAmount.toFixed(2);
            totalAmountDisplay.value = 'KES ' + totalAmount.toFixed(2);
            
            paymentModal.classList.add('active');
        });
    });
    
    // Post Job Modal
    const postJobModal = document.getElementById('post-job-modal');
    const postJobBtn = document.getElementById('post-job-btn');
    const postJobLink = document.getElementById('post-job-link');
    const myJobsPostBtn = document.getElementById('my-jobs-post-btn');
    
    if (postJobBtn) {
        postJobBtn.addEventListener('click', function() {
            postJobModal.classList.add('active');
        });
    }
    
    if (postJobLink) {
        postJobLink.addEventListener('click', function(e) {
            e.preventDefault();
            postJobModal.classList.add('active');
        });
    }
    
    if (myJobsPostBtn) {
        myJobsPostBtn.addEventListener('click', function() {
            postJobModal.classList.add('active');
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
            
            // Remove the # from the ID column
            if (j === 0 && data.startsWith('#')) {
                data = data.substring(1);
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