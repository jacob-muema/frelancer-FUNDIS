<?php
include "db_connect.php";

// Ensure fundi_id is provided
$fundi_id = isset($_GET['fundi_id']) ? intval($_GET['fundi_id']) : 0;

// Fetch fundi details
$sql = "SELECT name FROM users WHERE id = ? AND user_type = 'fundi'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fundi_id);
$stmt->execute();
$result = $stmt->get_result();
$fundi = $result->fetch_assoc();

if (!$fundi) {
    echo "<script>alert('Invalid Fundi selected.'); window.location.href='index.php';</script>";
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Fundi - FundiHire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">FundiHire</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="review-section">
        <div class="container">
            <h2>Leave a Review for <?php echo htmlspecialchars($fundi['name']); ?></h2>
            <form action="submit_review.php" method="POST">
                <input type="hidden" name="fundi_id" value="<?php echo $fundi_id; ?>">

                <label for="rating">Rating (1-5):</label>
                <select name="rating" id="rating" required>
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>

                <label for="comment">Review:</label>
                <textarea name="comment" id="comment" rows="5" required></textarea>

                <button type="submit" class="btn">Submit Review</button>
            </form>
        </div>
    </section>
</body>
</html>
