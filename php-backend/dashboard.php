<?php
/**
 * MemoryKeep Bot Factory - Dashboard
 * ALL PHP logic must come before any HTML output for redirects to work
 */
ob_start(); // Enable output buffering to ensure redirects work
require_once 'config.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$limits = TIER_LIMITS[$user['tier']];
$messageLimit = $limits['messages_per_month'];
$messagesUsed = $user['messages_used'];
$messagePercent = $messageLimit > 0 ? min(100, ($messagesUsed / $messageLimit) * 100) : 0;

$botLimitText = $limits['bots'] === -1 ? 'Unlimited' : $limits['bots'];
$faqLimitText = $limits['faqs'] === -1 ? 'Unlimited' : $limits['faqs'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MemoryKeep Bot Factory</title>
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
            color: #fff;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
            margin: 0 auto 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tier-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .tier-free {
            background: #374151;
            color: #9ca3af;
        }

        .tier-starter {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .tier-pro {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #000;
        }

        .logout {
            color: #8b5cf6;
            text-decoration: none;
            font-size: 14px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .welcome {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid #22c55e;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: #86efac;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
        }

        .card h2 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .stat {
            font-size: 36px;
            font-weight: 700;
            color: #8b5cf6;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 14px;
            margin-top: 5px;
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8b5cf6, #06b6d4);
            border-radius: 4px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #8b5cf6;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .check {
            color: #22c55e;
        }

        .x {
            color: #ef4444;
        }

        .token-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            word-break: break-all;
            margin: 15px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #06b6d4;
        }

        .copy-btn {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid #8b5cf6;
            color: #8b5cf6;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: #8b5cf6;
            color: #fff;
        }

        .upgrade-cta {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo">MemoryKeep Bot Factory</div>
        <div class="user-info">
            <span class="tier-badge tier-<?= $user['tier'] ?>">
                <?= ucfirst($user['tier']) ?>
            </span>
            <span>
                <?= htmlspecialchars($user['email']) ?>
            </span>
            <a href="logout.php" class="logout">Log out</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['welcome'])): ?>
            <div class="welcome">
                🎉 Welcome to
                <?= ucfirst($user['tier']) ?>! Your subscription is now active.
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <h2>📊 Message Usage</h2>
                <div class="stat">
                    <?= number_format($messagesUsed) ?>
                </div>
                <div class="stat-label">of
                    <?= number_format($messageLimit) ?> messages this month
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $messagePercent ?>%"></div>
                </div>
            </div>

            <div class="card">
                <h2>🤖 Your Bots</h2>
                <div class="stat">
                    <?= $botLimitText ?>
                </div>
                <div class="stat-label">chatbots allowed</div>
                <div style="margin-top: 20px;">
                    <a href="<?= BOT_FACTORY_URL ?>" class="btn" target="_blank">Open Bot Factory →</a>
                </div>
            <div class="card">
                <h2>✨ Your Features</h2>
                <ul class="features-list">
                    <li><span>Custom Avatars</span><span class="<?= $limits['avatars'] ? 'check' : 'x' ?>">
                            <?= $limits['avatars'] ? '✓' : '✗' ?>
                        </span></li>
                    <li><span>Lead Capture</span><span class="<?= $limits['lead_capture'] ? 'check' : 'x' ?>">
                            <?= $limits['lead_capture'] ? '✓' : '✗' ?>
                        </span></li>
                    <li><span>HTML Export</span><span class="<?= $limits['export'] ? 'check' : 'x' ?>">
                            <?= $limits['export'] ? '✓' : '✗' ?>
                        </span></li>
                    <li><span>Custom Prompts</span><span class="<?= $limits['custom_prompt'] ? 'check' : 'x' ?>">
                            <?= $limits['custom_prompt'] ? '✓' : '✗' ?>
                        </span></li>
                    <li><span>FAQs</span><span>
                            <?= $faqLimitText ?>
                        </span></li>
                </ul>
                <?php if ($user['tier'] !== 'pro'): ?>
                    <div class="upgrade-cta">
                        <a href="payment.php?plan=<?= $user['tier'] === 'free' ? 'starter' : 'pro' ?>"
                            class="btn btn-outline">
                            Upgrade to
                            <?= $user['tier'] === 'free' ? 'Starter' : 'Pro' ?> →
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>🔑 API Auth Token</h2>
                <div class="stat-label">Use this token to authorize your bots</div>
                <div class="token-box" id="tokenText"><?= htmlspecialchars($user['auth_token']) ?></div>
                <button class="copy-btn" onclick="copyToken()">Copy Token</button>
            </div>
        </div>
    </div>

    <script>
        function copyToken() {
            const token = document.getElementById('tokenText').innerText;
            navigator.clipboard.writeText(token).then(() => {
                const btn = document.querySelector('.copy-btn');
                const originalText = btn.innerText;
                btn.innerText = 'Copied!';
                setTimeout(() => {
                    btn.innerText = originalText;
                }, 2000);
            });
        }
    </script>
</body>

</html>