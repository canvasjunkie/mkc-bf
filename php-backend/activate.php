<?php
/**
 * Activate Subscription
 * Called after successful PayPal payment
 */
require_once 'config.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$subscriptionId = $input['subscription_id'] ?? null;
$plan = $input['plan'] ?? null;

if (!$subscriptionId || !in_array($plan, ['starter', 'pro'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE users 
        SET tier = ?, 
            paypal_subscription_id = ?, 
            subscription_status = 'active',
            messages_used = 0,
            messages_reset_date = CURRENT_DATE
        WHERE id = ?
    ");
    $stmt->execute([$plan, $subscriptionId, $_SESSION['user_id']]);

    // Update session
    $_SESSION['user_tier'] = $plan;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
