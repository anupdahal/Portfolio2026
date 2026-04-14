<?php
/**
 * JWT Authentication Middleware
 * Pure PHP implementation - no external libraries required
 */

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode($payload, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);

    $base64Header = base64url_encode($header);
    $base64Payload = base64url_encode($payload);

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Signature = base64url_encode($signature);

    return "$base64Header.$base64Payload.$base64Signature";
}

function jwt_decode($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    list($base64Header, $base64Payload, $base64Signature) = $parts;

    // Verify signature
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $expectedSignature = base64url_encode($signature);

    if (!hash_equals($expectedSignature, $base64Signature)) {
        return null;
    }

    $payload = json_decode(base64url_decode($base64Payload), true);
    if (!$payload) return null;

    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

/**
 * Authenticate the request using Bearer token
 * Returns user payload or sends 401/403 and exits
 */
function authenticateToken() {
    $secret = getenv('JWT_SECRET') ?: 'default_jwt_secret_change_this';

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader) || !preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Access denied. No token provided.']);
        exit;
    }

    $token = $matches[1];
    $decoded = jwt_decode($token, $secret);

    if (!$decoded) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid or expired token.']);
        exit;
    }

    return $decoded;
}
