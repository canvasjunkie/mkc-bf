<?php
/**
 * MemoryKeep Bot Factory - Login Page
 * ALL PHP logic must come before any HTML output for redirects to work
 */
ob_start(); // Enable output buffering to ensure redirects work
require_once 'config.php';

$error = '';
$info = '';
$prefillEmail = '';

// Check if redirected from signup (existing account)
if (isset($_GET['existing']) && $_GET['existing'] == '1') {
    $info = 'Welcome back! Looks like you already have an account. Just enter your password to continue.';
    $prefillEmail = $_GET['email'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, password_hash, auth_token, tier, subscription_status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tier'] = $user['tier'];

            // Get or generate auth token
            if ($user['auth_token']) {
                $_SESSION['auth_token'] = $user['auth_token'];
            } else {
                // Generate token for users who don't have one yet
                $_SESSION['auth_token'] = refreshAuthToken($user['id']);
            }

            // Check if they have a pending payment
            if ($user['subscription_status'] === 'pending' && $user['tier'] !== 'free') {
                header('Location: payment.php?plan=' . $user['tier']);
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - MemoryKeep Bot Factory</title>
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
            max-width: 400px;
            margin: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #a0a0a0;
        }

        .signup-link a {
            color: #8b5cf6;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Welcome Back</h1>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
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
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Log In</button>
        </form>

        <p class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </p>
    </div>
</body>

</html>