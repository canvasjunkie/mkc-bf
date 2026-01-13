<?php
/**
 * MemoryKeep Bot Factory - Payment Page
 * ALL PHP logic must come before any HTML output for redirects to work
 */
ob_start(); // Enable output buffering to ensure redirects work
require_once 'config.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$plan = isset($_GET['plan']) ? strtolower($_GET['plan']) : 'starter';
if (!in_array($plan, ['starter', 'pro'])) {
    $plan = 'starter';
}

$planData = [
    'starter' => [
        'name' => 'Starter',
        'price' => 9,
        'plan_id' => PAYPAL_STARTER_PLAN_ID,
        'features' => ['3 AI Chatbots', '1,000 messages/month', 'Full widget customization', 'Lead capture with CSV export', 'Custom system prompts', '50 FAQs per bot']
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 29,
        'plan_id' => PAYPAL_PRO_PLAN_ID,
        'features' => ['Unlimited AI Chatbots', '10,000 messages/month', 'White-label HTML export', 'Unlimited FAQs', 'Bring your own API key', 'Priority support']
    ]
];

$selected = $planData[$plan];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - MemoryKeep Bot Factory</title>
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
            max-width: 500px;
            margin: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 10px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: #a0a0a0;
            margin-bottom: 30px;
        }

        .plan-card {
            background: rgba(139, 92, 246, 0.1);
            border: 2px solid #8b5cf6;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .plan-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .plan-price {
            font-size: 36px;
            font-weight: 700;
            color: #8b5cf6;
        }

        .plan-price span {
            font-size: 16px;
            color: #a0a0a0;
        }

        .features {
            text-align: left;
            margin-top: 20px;
        }

        .features li {
            padding: 8px 0;
            color: #a0a0a0;
            list-style: none;
        }

        .features li::before {
            content: "✓ ";
            color: #22c55e;
        }

        #paypal-button-container {
            margin-top: 20px;
        }

        .secure-note {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #8b5cf6;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Complete Your Subscription</h1>
        <p class="subtitle">You're almost there,
            <?= htmlspecialchars($_SESSION['user_email']) ?>!
        </p>

        <div class="plan-card">
            <div class="plan-name">
                <?= $selected['name'] ?> Plan
            </div>
            <div class="plan-price">$
                <?= $selected['price'] ?><span>/month</span>
            </div>
            <ul class="features">
                <?php foreach ($selected['features'] as $feature): ?>
                    <li>
                        <?= htmlspecialchars($feature) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div id="paypal-button-container"></div>

        <p class="secure-note">🔒 Secure payment powered by PayPal</p>

        <p class="back-link">
            <a href="signup.php?plan=free">Switch to Free plan instead</a>
        </p>
    </div>

    <script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&vault=true&intent=subscription"
        data-sdk-integration-source="button-factory"></script>
    <script>
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'gold',
                layout: 'vertical',
                label: 'subscribe'
            },
            createSubscription: function (data, actions) {
                return actions.subscription.create({
                    plan_id: '<?= $selected['plan_id'] ?>'
                });
            },
            onApprove: function (data, actions) {
                // Send subscription ID to our server
                fetch('activate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subscription_id: data.subscriptionID,
                        plan: '<?= $plan ?>'
                    })
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            window.location.href = 'dashboard.php?welcome=1';
                        } else {
                            alert('Error activating subscription. Please contact support.');
                        }
                    });
            },
            onError: function (err) {
                console.error('PayPal error:', err);
                alert('Payment error. Please try again.');
            }
        }).render('#paypal-button-container');
    </script>
</body>

</html>