<?php
session_start();

// Check if job_id is provided
if (!isset($_GET['job_id'])) {
    die("Job not found.");
}

$job_id = $_GET['job_id'];

// Database connection
$host = "localhost";
$user = "root"; // Default XAMPP MySQL user
$password = ""; // Default is empty
$database = "fundihire"; // Your database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch job details
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fundi_name = $_POST['fundi_name'];
    $contact = $_POST['contact'];
    $message = $_POST['message'];

    // Prevent SQL Injection
    $fundi_name = $conn->real_escape_string($fundi_name);
    $contact = $conn->real_escape_string($contact);
    $message = $conn->real_escape_string($message);

    // Insert into database
    $sql = "INSERT INTO applications (job_id, fundi_name, contact, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $job_id, $fundi_name, $contact, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Application submitted successfully!'); window.location.href='fundi_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error applying for job. Try again.');</script>";
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
    <title>Apply for Job</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .home-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }
        .home-button:hover {
            background: #2980b9;
            color: white}
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            display: block;
            width: 100%;
            background: #2980b9;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .btn:hover {
            background: #1f6691;
        }
    </style>
</head>
<body>
<a href="index.php" class="home-button">Home</a>
    <div class="container">
        <h2>Apply for "<?php echo htmlspecialchars($job['title']); ?>"</h2>
        <form method="POST">
            <label>Your Name</label>
            <input type="text" name="fundi_name" required>

            <label>Contact Info</label>
            <input type="text" name="contact" required>

            <label>Why are you suitable for this job?</label>
            <textarea name="message" rows="4" required></textarea>

            <button type="submit" class="btn">Submit Application</button>
        </form>
    </div>

</body>
</html>
