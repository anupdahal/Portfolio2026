<?php
/**
 * Authentication API
 * POST /api/login
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

function handleLogin() {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required.']);
        return;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials.']);
            return;
        }

        $secret = getenv('JWT_SECRET') ?: 'default_jwt_secret_change_this';
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60), // 24 hours
        ];

        $token = jwt_encode($payload, $secret);

        echo json_encode([
            'token' => $token,
            'username' => $user['username'],
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}
