<?php
/**
 * Memories (Gallery) API
 * GET    /api/memories      - List memories (public)
 * POST   /api/memories      - Create memory (auth required)
 * DELETE /api/memories/:id  - Delete memory (auth required)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

function handleGetMemories() {
    try {
        $pdo = getDbConnection();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 12;
        $offset = ($page - 1) * $limit;

        // Get total count
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM memories');
        $total = (int)$stmt->fetch()['total'];

        // Get paginated results
        $stmt = $pdo->prepare('SELECT * FROM memories ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$limit, $offset]);
        $rows = $stmt->fetchAll();

        echo json_encode([
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
            ],
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleCreateMemory() {
    authenticateToken();

    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $image_url = $input['image_url'] ?? '';

    if (empty($title) || empty($image_url)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and image URL are required.']);
        return;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('INSERT INTO memories (title, description, image_url) VALUES (?, ?, ?)');
        $stmt->execute([$title, $description, $image_url]);
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM memories WHERE id = ?');
        $stmt->execute([$id]);
        $memory = $stmt->fetch();

        http_response_code(201);
        echo json_encode($memory);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleDeleteMemory($id) {
    authenticateToken();

    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT * FROM memories WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Memory not found.']);
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM memories WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Memory deleted successfully.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}
