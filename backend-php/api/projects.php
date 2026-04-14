<?php
/**
 * Projects API
 * GET    /api/projects      - List projects (public)
 * POST   /api/projects      - Create project (auth required)
 * PUT    /api/projects/:id  - Update project (auth required)
 * DELETE /api/projects/:id  - Delete project (auth required)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

function handleGetProjects() {
    try {
        $pdo = getDbConnection();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $search = $_GET['search'] ?? '';
        $tech = $_GET['tech'] ?? '';
        $offset = ($page - 1) * $limit;

        $query = 'SELECT * FROM projects';
        $countQuery = 'SELECT COUNT(*) as total FROM projects';
        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = '(title LIKE ? OR description LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($tech)) {
            $conditions[] = 'tech_stack LIKE ?';
            $params[] = "%$tech%";
        }

        if (!empty($conditions)) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
            $query .= $where;
            $countQuery .= $where;
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

function handleCreateProject() {
    authenticateToken();

    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? '';
    $image_url = $input['image_url'] ?? null;
    $tech_stack = $input['tech_stack'] ?? null;
    $live_url = $input['live_url'] ?? null;
    $github_url = $input['github_url'] ?? null;

    if (empty($title) || empty($description)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and description are required.']);
        return;
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO projects (title, description, image_url, tech_stack, live_url, github_url) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $description, $image_url, $tech_stack, $live_url, $github_url]);
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        http_response_code(201);
        echo json_encode($project);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleUpdateProject($id) {
    authenticateToken();

    try {
        $pdo = getDbConnection();

        // Check if exists
        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Project not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $title = !empty($input['title']) ? $input['title'] : $existing['title'];
        $description = !empty($input['description']) ? $input['description'] : $existing['description'];
        $image_url = array_key_exists('image_url', $input) ? $input['image_url'] : $existing['image_url'];
        $tech_stack = array_key_exists('tech_stack', $input) ? $input['tech_stack'] : $existing['tech_stack'];
        $live_url = array_key_exists('live_url', $input) ? $input['live_url'] : $existing['live_url'];
        $github_url = array_key_exists('github_url', $input) ? $input['github_url'] : $existing['github_url'];

        $stmt = $pdo->prepare(
            'UPDATE projects SET title = ?, description = ?, image_url = ?, tech_stack = ?, live_url = ?, github_url = ? WHERE id = ?'
        );
        $stmt->execute([$title, $description, $image_url, $tech_stack, $live_url, $github_url, $id]);

        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $updated = $stmt->fetch();

        echo json_encode($updated);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}

function handleDeleteProject($id) {
    authenticateToken();

    try {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Project not found.']);
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Project deleted successfully.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error.']);
    }
}
