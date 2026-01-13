<?php
/**
 * MemoryKeep Bot Factory - Configuration Template
 * pay.memorykeep.cloud
 * 
 * SETUP INSTRUCTIONS:
 * 1. Copy this file to config.php
 * 2. Replace all placeholder values with your actual credentials
 * 3. NEVER commit config.php to version control
 * 4. On production servers, consider using environment variables instead
 */

// Database Configuration - Use environment variables in production!
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database_name');
define('DB_USER', getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_secure_password');

// PayPal Configuration - Use environment variables in production!
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'your_paypal_client_id');
define('PAYPAL_STARTER_PLAN_ID', getenv('PAYPAL_STARTER_PLAN_ID') ?: 'your_starter_plan_id');
define('PAYPAL_PRO_PLAN_ID', getenv('PAYPAL_PRO_PLAN_ID') ?: 'your_pro_plan_id');

// Site Configuration
define('SITE_URL', 'https://pay.memorykeep.cloud');
define('BOT_FACTORY_URL', 'https://bf.memorykeep.cloud');

// Allowed CORS origins - Add your domains here
define('ALLOWED_ORIGINS', [
    'https://bf.memorykeep.cloud',
    'https://memorykeep.cloud',
    'http://localhost:5173',  // Vite dev server
    'http://localhost:8888',  // Netlify dev
]);

// Tier Limits
define('TIER_LIMITS', [
    'free' => [
        'bots' => 1,
        'messages_per_month' => 300,
        'faqs' => 10,
        'avatars' => false,
        'lead_capture' => false,
        'export' => true,
        'custom_prompt' => false
    ],
    'starter' => [
        'bots' => 3,
        'messages_per_month' => 1000,
        'faqs' => 50,
        'avatars' => true,
        'lead_capture' => true,
        'export' => true,
        'custom_prompt' => true
    ],
    'pro' => [
        'bots' => -1, // unlimited
        'messages_per_month' => 10000,
        'faqs' => -1, // unlimited
        'avatars' => true,
        'lead_capture' => true,
        'export' => true,
        'custom_prompt' => true,
        'own_api_key' => true
    ]
]);

// Database Connection
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode(['success' => false, 'error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get allowed CORS origin for this request
 */
function getCorsOrigin(): string
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS)) {
        return $origin;
    }
    // Default to first allowed origin for non-browser requests
    return ALLOWED_ORIGINS[0];
}

/**
 * Generate a secure random auth token
 */
function generateAuthToken(): string
{
    return bin2hex(random_bytes(32)); // 64 character hex string
}

/**
 * Hash an auth token for secure storage
 * Store the hash in DB, compare hashes on authentication
 */
function hashAuthToken(string $token): string
{
    return hash('sha256', $token);
}

/**
 * Create or refresh auth token for a user
 * Returns the UNHASHED token (store hash in DB, give token to user)
 */
function refreshAuthToken(int $userId): string
{
    $db = getDB();
    $token = generateAuthToken();
    $tokenHash = hashAuthToken($token);
    $stmt = $db->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
    $stmt->execute([$tokenHash, $userId]);
    return $token; // Return unhashed token to give to user
}

/**
 * Authenticate an API request using auth token
 * Returns user data if valid, null if invalid
 */
function authenticateApiRequest(): ?array
{
    // Check for token in header or query param
    $token = null;

    // Check Authorization header first (Bearer token)
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s+(.+)$/i', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        }
    }

    // Fallback to POST body (for backwards compatibility)
    if (!$token) {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? null;
    }

    if (!$token) {
        return null;
    }

    // Hash the provided token and compare with stored hash
    $tokenHash = hashAuthToken($token);

    // Look up user by token hash
    $db = getDB();
    $stmt = $db->prepare("SELECT id, email, tier, subscription_status, messages_used, messages_reset_date FROM users WHERE auth_token = ?");
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch() ?: null;

    if ($user) {
        checkRateLimit($user['id']);
    }

    return $user;
}

/**
 * Database-based rate limiting (more reliable than session-based)
 * Uses a simple sliding window approach
 */
function checkRateLimit(int $userId, int $limit = 60, int $period = 60)
{
    // For now, use session-based as fallback
    // TODO: Implement database or Redis-based rate limiting for production
    if (!isset($_SESSION['api_requests'])) {
        $_SESSION['api_requests'] = [];
    }

    $now = time();
    // Clean old requests
    $_SESSION['api_requests'] = array_filter($_SESSION['api_requests'], function ($time) use ($now, $period) {
        return $time > ($now - $period);
    });

    if (count($_SESSION['api_requests']) >= $limit) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Too many requests. Please slow down.']);
        exit;
    }

    $_SESSION['api_requests'][] = $now;
}
