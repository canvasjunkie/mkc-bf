<?php
/**
 * MemoryKeep Bot Factory - Subscription Status API
 * 
 * Bot Factory app calls this to check user's subscription tier and limits.
 * 
 * Usage: GET /api/status.php
 * Headers: Authorization: Bearer <token>
 * OR: ?token=<token>
 * 
 * Returns:
 * {
 *   "success": true,
 *   "tier": "starter",
 *   "status": "active",
 *   "limits": {...},
 *   "usage": {...}
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

// Handle preflight
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

    // Check if we need to reset monthly messages
    $resetDate = new DateTime($user['messages_reset_date']);
    $now = new DateTime();
    if ($now->format('Y-m') !== $resetDate->format('Y-m')) {
        // New month, reset counter
        $stmt = $db->prepare("UPDATE users SET messages_used = 0, messages_reset_date = CURRENT_DATE WHERE id = ?");
        $stmt->execute([$user['id']]);
        $user['messages_used'] = 0;
    }

    $limits = TIER_LIMITS[$user['tier']] ?? TIER_LIMITS['free'];

    echo json_encode([
        'success' => true,
        'tier' => $user['tier'],
        'status' => $user['subscription_status'],
        'limits' => $limits,
        'usage' => [
            'messages_used' => (int) $user['messages_used'],
            'messages_limit' => $limits['messages_per_month'],
            'messages_remaining' => max(0, $limits['messages_per_month'] - $user['messages_used'])
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
