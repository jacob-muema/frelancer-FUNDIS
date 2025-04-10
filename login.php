<?php
session_start();

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

$login_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Check if users table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->num_rows == 0) {
        die("Error: The 'users' table does not exist. Please check your database setup.");
    }

    // Select user from users table
    $stmt = $conn->prepare("SELECT id, email, password, user_type FROM users WHERE email = ? AND user_type = ?");
    $stmt->bind_param("ss", $email, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check password (handles both hashed and plain text passwords)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_type'] = $row['user_type'];

            // Redirect user based on type
            header("Location: " . ($row['user_type'] == 'fundi' ? "fundi_dashboard.php" : "client_dashboard.php"));
            exit();
        } else {
            $login_message = "Incorrect password. Please try again.";
        }
    } else {
        $login_message = "Email not found or wrong user type.";
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
    <title>Login - Taskhero</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
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
            color: white;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-container h2 {
            margin-bottom: 15px;
            color: #333;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: 0.3s;
        }
        input:focus, select:focus {
            border-color: #2980b9;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 16px;
        }
        .btn:hover {
            background: #1f6691;
        }
        .register-link {
            margin-top: 15px;
            display: block;
            font-size: 14px;
        }
        .register-link a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <a href="index.php" class="home-button">Home</a>

    <div class="login-container">
        <h2>Login to FundiHire</h2>
        <?php if (!empty($login_message)) : ?>
            <p class="error-message"><?php echo $login_message; ?></p>
        <?php endif; ?>
        <form method="post">
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Login as:</label>
            <select name="user_type">
                <option value="client">Client</option>
                <option value="fundi">Frelancer</option>
            </select>

            <button type="submit" class="btn">Login</button>
        </form>
        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>

</body>
</html>
