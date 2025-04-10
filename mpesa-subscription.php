<?php
// PayPal Configuration
define('PAYPAL_CLIENT_ID', 'AfHntxGpzTisemqSIxh_LsjaIVSiVMDUBbEX11Q0up3aDWHuZizSJ2qmP5yAqz1-Z3s5TvBtfcVGeGtA');
define('PAYPAL_SECRET', 'EPP7XsG2-23bRB_LB7rJTvPkTpBhiv3u6kr_Zg0_PjHH6UY-p5nS0m1oIgseqnqsOkO0FdjCrzTF0asG');
define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production

// Set the return and cancel URLs
define('PAYPAL_RETURN_URL', 'https://yourdomain.com/success.php');
define('PAYPAL_CANCEL_URL', 'https://yourdomain.com/cancel.php');

// Currency code
define('CURRENCY', 'USD');

// Function to create a PayPal payment
function createPayPalPayment($amount) {
    $apiUrl = (PAYPAL_MODE === 'sandbox') 
        ? 'https://api-m.sandbox.paypal.com' 
        : 'https://api-m.paypal.com';
    
    // Get access token
    $ch = curl_init($apiUrl . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'Failed to connect to PayPal: ' . $error];
    }
    
    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        return ['error' => 'Failed to get PayPal access token'];
    }
    
    $accessToken = $tokenData['access_token'];
    
    // Create payment
    $paymentData = [
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [
            [
                'amount' => [
                    'total' => number_format($amount, 2, '.', ''),
                    'currency' => CURRENCY
                ],
                'description' => 'Fundimtaa Membership Payment'
            ]
        ],
        'redirect_urls' => [
            'return_url' => PAYPAL_RETURN_URL,
            'cancel_url' => PAYPAL_CANCEL_URL
        ]
    ];
    
    $ch = curl_init($apiUrl . '/v1/payments/payment');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => 'Failed to create PayPal payment: ' . $error];
    }
    
    $paymentResponse = json_decode($response, true);
    
    if (isset($paymentResponse['id'])) {
        // Extract approval URL
        $approvalUrl = null;
        foreach ($paymentResponse['links'] as $link) {
            if ($link['rel'] === 'approval_url') {
                $approvalUrl = $link['href'];
                break;
            }
        }
        
        if ($approvalUrl) {
            return [
                'success' => true,
                'payment_id' => $paymentResponse['id'],
                'approval_url' => $approvalUrl
            ];
        }
    }
    
    return ['error' => 'Failed to create PayPal payment: ' . ($paymentResponse['message'] ?? 'Unknown error')];
}

$message = "";
$messageType = "";
$selectedAmount = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $amount = trim($_POST['amount']);
    $selectedAmount = $amount;
    
    if (!empty($email) && !empty($amount)) {
        $response = createPayPalPayment($amount);
        
        if (isset($response['success']) && $response['success']) {
            // Redirect to PayPal
            header('Location: ' . $response['approval_url']);
            exit;
        } else {
            $message = "Error: " . ($response['error'] ?? 'Something went wrong. Try again.');
            $messageType = "error";
        }
    } else {
        $message = "Please fill in all fields.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Membership - Fundimtaa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #0ea5e9;
            --secondary-dark: #0284c7;
            --success: #10b981;
            --error: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f8fc, #e9edf5);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            width: 100%;
            max-width: 350px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        .card.premium::before {
            content: "POPULAR";
            position: absolute;
            top: 1rem;
            right: -2rem;
            background: var(--primary);
            color: white;
            padding: 0.25rem 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            transform: rotate(45deg);
        }

        .card-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card-price {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .card.premium .card-price {
            color: var(--secondary);
        }

        .card-price span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--gray);
        }

        .card-description {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        .features {
            list-style: none;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .feature::before {
            content: "✓";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            background: #e0e7ff;
            color: var(--primary);
            border-radius: 50%;
            margin-right: 0.75rem;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .card.premium .feature::before {
            background: #e0f2fe;
            color: var(--secondary);
        }

        .select-btn {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .select-btn:hover {
            background: var(--primary);
            color: white;
        }

        .card.premium .select-btn {
            color: var(--secondary);
            border-color: var(--secondary);
        }

        .card.premium .select-btn:hover {
            background: var(--secondary);
            color: white;
        }

        .payment-form {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            display: none;
        }

        .payment-form.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }

        .message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-align: center;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .back-btn {
            background: transparent;
            color: var(--gray);
            border: none;
            padding: 0.5rem;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .back-btn:hover {
            color: var(--dark);
        }

        .back-btn svg {
            margin-right: 0.5rem;
        }

        .paypal-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .paypal-logo img {
            height: 30px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .cards-container {
                flex-direction: column;
                align-items: center;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Premium Membership Plans</h1>
            <p class="subtitle">Join our community of service providers and clients. Choose the plan that works best for you.</p>
        </header>

        <div class="cards-container" id="plansContainer">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Basic Package</h2>
                    <div class="card-price">4000 <span>USD</span></div>
                    <p class="card-description">Perfect for individuals getting started</p>
                </div>
                <ul class="features">
                    <li class="feature">Access to basic features</li>
                    <li class="feature">Connect with clients</li>
                    <li class="feature">Basic profile customization</li>
                    <li class="feature">Email support</li>
                </ul>
                <button class="select-btn" data-amount="4000">Select Plan</button>
            </div>

            <div class="card premium">
                <div class="card-header">
                    <h2 class="card-title">Advanced Package</h2>
                    <div class="card-price">5000 <span>USD</span></div>
                    <p class="card-description">For professionals seeking more opportunities</p>
                </div>
                <ul class="features">
                    <li class="feature">All Basic features</li>
                    <li class="feature">Priority listing in search</li>
                    <li class="feature">Advanced profile customization</li>
                    <li class="feature">Priority support</li>
                    <li class="feature">Performance analytics</li>
                </ul>
                <button class="select-btn" data-amount="4500">Select Plan</button>
            </div>
        </div>

        <div class="payment-form" id="paymentForm">
            <button class="back-btn" id="backBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Back to plans
            </button>
            <h2 class="form-title">Complete Your Payment</h2>
            <div class="paypal-logo">
                <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal Logo">
            </div>
            <form method="post">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
                </div>
                <input type="hidden" id="amountInput" name="amount" value="<?php echo $selectedAmount; ?>">
                <button type="submit" class="submit-btn">Proceed to PayPal</button>
                
                <?php if (!empty($message)) : ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const plansContainer = document.getElementById('plansContainer');
            const paymentForm = document.getElementById('paymentForm');
            const amountInput = document.getElementById('amountInput');
            const backBtn = document.getElementById('backBtn');
            const selectBtns = document.querySelectorAll('.select-btn');
            
            // Check if there's a message to display the payment form
            <?php if (!empty($message)) : ?>
                plansContainer.style.display = 'none';
                paymentForm.classList.add('active');
            <?php endif; ?>
            
            // Handle plan selection
            selectBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = this.getAttribute('data-amount');
                    amountInput.value = amount;
                    
                    plansContainer.style.display = 'none';
                    paymentForm.classList.add('active');
                });
            });
            
            // Handle back button
            backBtn.addEventListener('click', function(e) {
                e.preventDefault();
                paymentForm.classList.remove('active');
                setTimeout(() => {
                    plansContainer.style.display = 'flex';
                }, 300);
            });
        });
    </script>
</body>
</html>