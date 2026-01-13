<?php
/**
 * MemoryKeep Bot Factory - Increment Message Usage API
 * 
 * Bot Factory calls this after each AI message to track usage.
 * 
 * Usage: POST /api/use-message.php
 * Headers: Authorization: Bearer <token>
 * OR Body: { "token": "<token>" }
 * 
 * Returns:
 * {
 *   "success": true,
 *   "messages_used": 5,
 *   "messages_remaining": 995
 * }
 */
require_once '../config.php';

header('Content-Type: application/json');
// SECURITY: Only allow specific origins, not wildcard
$allowedOrigins = defined('ALLOWED_ORIGINS') ? ALLOWED_ORIGINS : [
    'https://bf.memorykeep.cloud',
    'https://memorykeep.cloud',
    'http://localhost:5173',
    'http://localhost:8888'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: ' . $allowedOrigins[0]);
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Authenticate the request
$user = authenticateApiRequest();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please provide a valid auth token.']);
    exit;
}

try {
    $db = getDB();

    $limits = TIER_LIMITS[$user['tier']] ?? TIER_LIMITS['free'];
    $messageLimit = $limits['messages_per_month'];

    // SECURITY FIX: Atomic increment with limit check to prevent race conditions
    // This single query increments ONLY if we're still under the limit
    $stmt = $db->prepare("UPDATE users SET messages_used = messages_used + 1 WHERE id = ? AND messages_used < ?");
    $stmt->execute([$user['id'], $messageLimit]);

    // Check if the update actually happened (affected rows > 0)
    if ($stmt->rowCount() === 0) {
        // Either user doesn't exist or they're at/over the limit
        echo json_encode([
            'success' => false,
            'error' => 'Message limit exceeded',
            'messages_used' => (int) $user['messages_used'],
            'messages_remaining' => 0
        ]);
        exit;
    }

    // Get the new count
    $stmt = $db->prepare("SELECT messages_used FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $newCount = (int) $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'messages_used' => $newCount,
        'messages_remaining' => max(0, $messageLimit - $newCount)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
