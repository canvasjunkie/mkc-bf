<?php
/**
 * MemoryKeep Bot Factory - Signup Page
 * ALL PHP logic must come before any HTML output for redirects to work
 */
ob_start(); // Enable output buffering to ensure redirects work
require_once 'config.php';

$plan = isset($_GET['plan']) ? strtolower($_GET['plan']) : 'free';
if (!in_array($plan, ['free', 'starter', 'pro'])) {
    $plan = 'free';
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$email) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();

        try {
            // Check if email exists
            $stmt = $db->prepare("SELECT id, email, password_hash, tier, subscription_status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                // Email exists - try to log them in with the provided password
                if (password_verify($password, $existingUser['password_hash'])) {
                    // Password correct! Log them in automatically
                    $_SESSION['user_id'] = $existingUser['id'];
                    $_SESSION['user_email'] = $existingUser['email'];
                    $_SESSION['user_tier'] = $existingUser['tier'];

                    // Check if they have a pending payment
                    if ($existingUser['subscription_status'] === 'pending' && $existingUser['tier'] !== 'free') {
                        header('Location: payment.php?plan=' . $existingUser['tier']);
                    } else {
                        header('Location: dashboard.php?welcome=back');
                    }
                    exit;
                } else {
                    // Wrong password - redirect to login with friendly message
                    header('Location: login.php?existing=1&email=' . urlencode($email));
                    exit;
                }
            } else {
                // Create new account with auth token
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $authToken = generateAuthToken();
                $stmt = $db->prepare("INSERT INTO users (email, password_hash, auth_token, tier, subscription_status) VALUES (?, ?, ?, ?, ?)");
                $status = ($plan === 'free') ? 'active' : 'pending';

                if (!$stmt->execute([$email, $hash, $authToken, $plan, $status])) {
                    $error = 'Failed to create account. Please try again.';
                } else {
                    // Log them in
                    $_SESSION['user_id'] = $db->lastInsertId();
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_tier'] = $plan;
                    $_SESSION['auth_token'] = $authToken;

                    if ($plan === 'free') {
                        // Free users go straight to dashboard
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        // Paid users need to complete payment
                        header('Location: payment.php?plan=' . $plan);
                        exit;
                    }
                }
            }
        } catch (PDOException $e) {
            // Database error - show user-friendly message
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$planInfo = [
    'free' => ['name' => 'Free', 'price' => '$0', 'features' => ['1 AI Chatbot', '300 messages/month', 'Basic customization', '10 FAQs']],
    'starter' => ['name' => 'Starter', 'price' => '$9/mo', 'features' => ['3 AI Chatbots', '1,000 messages/month', 'Full customization', 'Lead capture', '50 FAQs']],
    'pro' => ['name' => 'Pro', 'price' => '$29/mo', 'features' => ['Unlimited Chatbots', '10,000 messages/month', 'White-label export', 'Unlimited FAQs', 'Own API key']]
];
$info = $planInfo[$plan];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MemoryKeep Bot Factory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            margin: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .plan-badge {
            text-align: center;
            margin-bottom: 30px;
            padding: 8px 20px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border-radius: 20px;
            display: inline-block;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }

        .plan-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #a0a0a0;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #8b5cf6;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #fca5a5;
        }

        .success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #86efac;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #a0a0a0;
        }

        .login-link a {
            color: #8b5cf6;
            text-decoration: none;
        }

        .plan-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .plan-info h3 {
            margin-bottom: 8px;
            color: #8b5cf6;
        }

        .plan-info ul {
            padding-left: 20px;
            color: #a0a0a0;
        }

        .plan-info li {
            margin: 5px 0;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Create Account</h1>
        <div class="plan-container">
            <span class="plan-badge">
                <?= htmlspecialchars($info['name']) ?> Plan -
                <?= $info['price'] ?>
            </span>
        </div>

        <div class="plan-info">
            <h3>What you get:</h3>
            <ul>
                <?php foreach ($info['features'] as $feature): ?>
                    <li>
                        <?= htmlspecialchars($feature) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">
                <?= $plan === 'free' ? 'Create Free Account' : 'Continue to Payment' ?>
            </button>
        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </div>
</body>

</html>