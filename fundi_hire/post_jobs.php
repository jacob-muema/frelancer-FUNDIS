<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    die("Access denied! Only clients can post jobs.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - fundihire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">FundiHire</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="post_job.php">Post a Job</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="post-job-section">
        <div class="container">
            <h2>Post a Job</h2>
            <form action="process_post_job.php" method="POST">
                <label for="title">Job Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="description">Job Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>

                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="Plumbing">Plumbing</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Carpentry">Carpentry</option>
                    <option value="Masonry">Masonry</option>
                    <option value="Painting">Painting</option>
                </select>

                <label for="budget">Budget (Ksh):</label>
                <input type="number" id="budget" name="budget" required>

                <label for="deadline">Deadline:</label>
                <input type="date" id="deadline" name="deadline" required>

                <button type="submit" class="btn">Post Job</button>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 FundiHire. All rights reserved.</p>
    </footer>
</body>
</html>
