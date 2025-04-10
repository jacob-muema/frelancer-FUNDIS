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

// Initialize filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Fetch fundi jobs with filters
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

// Calculate statistics
$total_applications = $jobs_result->num_rows;
$accepted_jobs = 0;
$completed_jobs = 0;
$total_earnings = 0;

if ($total_applications > 0) {
  // Reset pointer
  mysqli_data_seek($jobs_result, 0);
  
  while ($job = $jobs_result->fetch_assoc()) {
    if ($job['application_status'] == 'accepted') {
      $accepted_jobs++;
      if ($job['status'] == 'completed') {
        $completed_jobs++;
        $total_earnings += $job['bid_amount'];
      }
    }
  }
  
  // Reset pointer again for later use
  mysqli_data_seek($jobs_result, 0);
}

// Get fundi info
$fundi_query = "SELECT * FROM users WHERE id = $user_id";
$fundi_result = $conn->query($fundi_query);
$fundi_data = $fundi_result->fetch_assoc();
$fundi_name = $fundi_data['name'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fundi Reports - FundiHire</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Add your existing CSS styles here */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
    }
    
    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #ddd;
    }
    
    .header h1 {
      color: #2563eb;
      margin: 0;
    }
    
    .btn {
      display: inline-block;
      background-color: #2563eb;
      color: white;
      padding: 8px 15px;
      border-radius: 4px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      font-size: 14px;
    }
    
    .btn-outline {
      background-color: transparent;
      border: 1px solid #2563eb;
      color: #2563eb;
    }
    
    .btn:hover {
      opacity: 0.9;
    }
    
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .stat-card {
      background-color: white;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-value {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 5px;
      color: #2563eb;
    }
    
    .stat-label {
      color: #666;
      font-size: 14px;
    }
    
    .filter-section {
      background-color: white;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: flex-end;
    }
    
    .filter-group {
      flex: 1;
      min-width: 200px;
    }
    
    .filter-label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      font-size: 14px;
    }
    
    .filter-control {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .table-container {
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: #f8f9fa;
      font-weight: bold;
    }
    
    tr:hover {
      background-color: #f5f5f5;
    }
    
    .status {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }
    
    .status-open {
      background-color: #e3f2fd;
      color: #1976d2;
    }
    
    .status-assigned {
      background-color: #fff8e1;
      color: #f57f17;
    }
    
    .status-completed {
      background-color: #e8f5e9;
      color: #388e3c;
    }
    
    .status-cancelled {
      background-color: #ffebee;
      color: #d32f2f;
    }
    
    .status-accepted {
      background-color: #e8f5e9;
      color: #388e3c;
    }
    
    .status-rejected {
      background-color: #ffebee;
      color: #d32f2f;
    }
    
    .status-pending {
      background-color: #fff8e1;
      color: #f57f17;
    }
    
    .fee-info {
      background-color: #e3f2fd;
      border: 1px solid #bbdefb;
      color: #1976d2;
      padding: 10px 15px;
      border-radius: 4px;
      margin-bottom: 15px;
    }
    
    @media (max-width: 768px) {
      .filter-form {
        flex-direction: column;
      }
      
      .filter-group {
        width: 100%;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .header .btn {
        margin-top: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Fundi Reports</h1>
      <div>
        <button class="btn btn-outline" onclick="window.print()">
          <i class="fas fa-print"></i> Print Report
        </button>
        <button class="btn" onclick="exportToCSV()">
          <i class="fas fa-download"></i> Export CSV
        </button>
        <a href="fundi-dashboard.php" class="btn btn-outline">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>
    
    <!-- Stats -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-value"><?php echo $total_applications; ?></div>
        <div class="stat-label">Total Applications</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $accepted_jobs; ?></div>
        <div class="stat-label">Accepted Jobs</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $completed_jobs; ?></div>
        <div class="stat-label">Completed Jobs</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">KES <?php echo number_format($total_earnings, 2); ?></div>
        <div class="stat-label">Total Earnings</div>
      </div>
    </div>
    
    <!-- Fee Info -->
    <div class="fee-info">
      <i class="fas fa-info-circle"></i> Note: A service fee of 5% is charged to clients for all completed jobs. This fee is paid by the client and does not affect your earnings.
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
      <h3>Filter Reports</h3>
      <form class="filter-form" method="get">
        <div class="filter-group">
          <label class="filter-label">Start Date</label>
          <input type="date" name="start_date" class="filter-control" value="<?php echo $start_date; ?>">
        </div>
        <div class="filter-group">
          <label class="filter-label">End Date</label>
          <input type="date" name="end_date" class="filter-control" value="<?php echo $end_date; ?>">
        </div>
        <div class="filter-group">
          <label class="filter-label">Job Status</label>
          <select name="status" class="filter-control">
            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="open" <?php echo $status_filter == 'open' ? 'selected' : ''; ?>>Open</option>
            <option value="assigned" <?php echo $status_filter == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>
        <div class="filter-group" style="flex: 0 0 auto;">
          <button type="submit" class="btn">
            <i class="fas fa-filter"></i> Apply Filters
          </button>
        </div>
      </form>
    </div>
    
    <!-- Jobs Table -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
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
            <?php while ($job = $jobs_result->fetch_assoc()): ?>
              <tr>
                <td>#<?php echo $job['id']; ?></td>
                <td><?php echo htmlspecialchars($job['title']); ?></td>
                <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                <td>KES <?php echo number_format($job['bid_amount'], 2); ?></td>
                <td><span class="status status-<?php echo $job['application_status']; ?>"><?php echo ucfirst($job['application_status']); ?></span></td>
                <td><span class="status status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                <td><?php echo date('M d, Y', strtotime($job['application_date'])); ?></td>
                <td>
                  <button class="btn btn-outline view-job" data-id="<?php echo $job['id']; ?>">
                    <i class="fas fa-eye"></i> View
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align: center;">No job applications found for the selected period.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // View job details
      const viewJobButtons = document.querySelectorAll('.view-job');
      viewJobButtons.forEach(button => {
        button.addEventListener('click', function() {
          const jobId = this.getAttribute('data-id');
          window.location.href = 'fundi-dashboard.php?view_job=' + jobId;
        });
      });
    });
    
    // Export to CSV function
    function exportToCSV() {
      const table = document.querySelector('table');
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
      link.setAttribute('download', 'fundihire_fundi_report_<?php echo date('Y-m-d'); ?>.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
</body>
</html>

