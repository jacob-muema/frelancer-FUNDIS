<?php
include "db_connect.php"; // Ensure database connection

// Fetch available jobs from the database
$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);

if (!$result) {
    die("Error fetching jobs: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs - FundiHire</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(7, 172, 43);
            margin: 0;
            padding: 0;
        }
        header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0056b3;
            padding: 10px 20px;
        }
        nav ul {
            list-style: none;
            display: flex;
        }
        nav ul li {
            margin: 0 10px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        nav ul li a:hover {
            background: #003d80;
        }
        .home-button {
            background: white;
            color: #0056b3;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
        }
        .home-button:hover {
            background: rgb(6, 155, 38);
        }

        /* Job Listings */
        .jobs-section {
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
        }
        .job-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin: 20px auto;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 400px;
        }
        .job-card h3 {
            color: #007bff;
        }
        .job-card p {
            margin: 8px 0;
            font-size: 16px;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }
        .btn:hover {
            background: #0056b3;
        }

        /* Application Form */
        .form-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }
        .form-container {
            text-align: left;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .cancel {
            background: red;
        }
        .cancel:hover {
            background: darkred;
        }

        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
    <script>
        function openForm(jobTitle, jobId) {
            document.getElementById("jobTitle").innerText = jobTitle;
            document.getElementById("job_id").value = jobId;
            document.getElementById("applyForm").style.display = "block";
        }

        function closeForm() {
            document.getElementById("applyForm").style.display = "none";
        }
    </script>
</head>
<body>

<header>FundiHire - Available Jobs</header>

<nav>
    <a href="index.php" class="home-button">Home</a>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<section class="jobs-section">
    <div class="container">
        <h2>Available Jobs</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Budget:</strong> Ksh <?php echo number_format($row['budget']); ?></p>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <button class="btn" onclick="openForm('<?php echo htmlspecialchars($row['title']); ?>', <?php echo $row['id']; ?>)">Apply Now</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No jobs available at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Application Form -->
<div id="applyForm" class="form-popup">
    <div class="form-container">
        <h2>Apply for <span id="jobTitle"></span></h2>
        <form action="process_application.php" method="POST">
            <input type="hidden" name="job_id" id="job_id">
            <label for="applicant_name">Your Name:</label>
            <input type="text" name="applicant_name" required>

            <label for="phone">Phone Number:</label>
            <input type="text" name="phone" required>

            <label for="message">Application Message:</label>
            <textarea name="message" required></textarea>

            <label for="expected_pay">Expected Payment (Ksh):</label>
            <input type="number" name="expected_pay" required>

            <button type="submit" class="btn">Submit Application</button>
            <button type="button" class="btn cancel" onclick="closeForm()">Cancel</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2025 FundiHire. All rights reserved.</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
