<?php
/**
 * Blogs API
 * GET    /api/blogs       - List blogs with excerpts (public)
 * GET    /api/blogs/:id   - Get single blog (public)
 * POST   /api/blogs       - Create blog (auth required)
 * PUT    /api/blogs/:id   - Update blog (auth required)
 * DELETE /api/blogs/:id   - Delete blog (auth required)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

function handleGetBlogs() {
    try {
        $pdo = getDbConnection();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 6;
        $search = $_GET['search'] ?? '';
        $offset = ($page - 1) * $limit;

        $query = 'SELECT id, title, LEFT(content, 200) as excerpt, cover_image, created_at FROM blogs';
        $countQuery = 'SELECT COUNT(*) as total FROM blogs';
        $params = [];

        if (!empty($search)) {
            $where = ' WHERE title LIKE ? OR content LIKE ?';
            $query .= $where;
            $countQuery .= $where;
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Get total count
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute($params);
        $total = (int)$stmt->fetch()['total'];

        // Get paginated results
        $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
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

function handleGetBlog($id) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $blog = $stmt->fetch();

        if (!$blog) {
            http_response_code(404);
            echo json_encode(['error' => 'Blog post not found.']);
            return;
        }

        echo json_encode($blog);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleCreateBlog() {
    authenticateToken();

    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? '';
    $content = $input['content'] ?? '';
    $cover_image = $input['cover_image'] ?? null;

    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and content are required.']);
        return;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('INSERT INTO blogs (title, content, cover_image) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $cover_image]);
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $blog = $stmt->fetch();

        http_response_code(201);
        echo json_encode($blog);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleUpdateBlog($id) {
    authenticateToken();

    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Blog post not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $title = !empty($input['title']) ? $input['title'] : $existing['title'];
        $content = !empty($input['content']) ? $input['content'] : $existing['content'];
        $cover_image = array_key_exists('cover_image', $input) ? $input['cover_image'] : $existing['cover_image'];

        $stmt = $pdo->prepare('UPDATE blogs SET title = ?, content = ?, cover_image = ? WHERE id = ?');
        $stmt->execute([$title, $content, $cover_image, $id]);

        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $updated = $stmt->fetch();

        echo json_encode($updated);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleDeleteBlog($id) {
    authenticateToken();

    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Blog post not found.']);
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Blog post deleted successfully.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}
