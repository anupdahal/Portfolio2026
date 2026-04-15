<?php
require_once __DIR__ . '/includes/config.php';

$pdo = getDB();
$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user'] = $user['username'];
                setFlash('Welcome back!', 'success');
                header('Location: admin.php?panel=dashboard');
                exit;
            } else {
                $error = 'Invalid credentials.';
            }
        } catch (Exception $e) {
            $error = 'Server error. Please try again.';
        }
    } else {
        $error = 'Database connection failed.';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    session_start();
    setFlash('Logged out.', 'info');
    header('Location: admin.php');
    exit;
}

// Handle CRUD actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && $pdo) {
    $action = $_POST['action'] ?? '';

    // --- Projects ---
    if ($action === 'create_project') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '') ?: null;
        $tech_stack = trim($_POST['tech_stack'] ?? '') ?: null;
        $live_url = trim($_POST['live_url'] ?? '') ?: null;
        $github_url = trim($_POST['github_url'] ?? '') ?: null;

        if ($title && $description) {
            try {
                $stmt = $pdo->prepare('INSERT INTO projects (title, description, image_url, tech_stack, live_url, github_url) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title, $description, $image_url, $tech_stack, $live_url, $github_url]);
                setFlash('Project created!', 'success');
            } catch (Exception $e) {
                setFlash('Failed to create project.', 'error');
            }
        } else {
            setFlash('Title and description are required.', 'error');
        }
        header('Location: admin.php?panel=projects');
        exit;
    }

    if ($action === 'update_project') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '') ?: null;
        $tech_stack = trim($_POST['tech_stack'] ?? '') ?: null;
        $live_url = trim($_POST['live_url'] ?? '') ?: null;
        $github_url = trim($_POST['github_url'] ?? '') ?: null;

        if ($id && $title && $description) {
            try {
                $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, image_url = ?, tech_stack = ?, live_url = ?, github_url = ? WHERE id = ?');
                $stmt->execute([$title, $description, $image_url, $tech_stack, $live_url, $github_url, $id]);
                setFlash('Project updated!', 'success');
            } catch (Exception $e) {
                setFlash('Failed to update project.', 'error');
            }
        }
        header('Location: admin.php?panel=projects');
        exit;
    }

    if ($action === 'delete_project') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('Project deleted.', 'success');
            } catch (Exception $e) {
                setFlash('Failed to delete project.', 'error');
            }
        }
        header('Location: admin.php?panel=projects');
        exit;
    }

    // --- Memories ---
    if ($action === 'create_memory') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '') ?: null;
        $image_url = trim($_POST['image_url'] ?? '');

        if ($title && $image_url) {
            try {
                $stmt = $pdo->prepare('INSERT INTO memories (title, description, image_url) VALUES (?, ?, ?)');
                $stmt->execute([$title, $description, $image_url]);
                setFlash('Memory added!', 'success');
            } catch (Exception $e) {
                setFlash('Failed to add memory.', 'error');
            }
        } else {
            setFlash('Title and image URL are required.', 'error');
        }
        header('Location: admin.php?panel=memories');
        exit;
    }

    if ($action === 'delete_memory') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM memories WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('Memory deleted.', 'success');
            } catch (Exception $e) {
                setFlash('Failed to delete memory.', 'error');
            }
        }
        header('Location: admin.php?panel=memories');
        exit;
    }

    // --- Blogs ---
    if ($action === 'create_blog') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cover_image = trim($_POST['cover_image'] ?? '') ?: null;

        if ($title && $content) {
            try {
                $stmt = $pdo->prepare('INSERT INTO blogs (title, content, cover_image) VALUES (?, ?, ?)');
                $stmt->execute([$title, $content, $cover_image]);
                setFlash('Blog post created!', 'success');
            } catch (Exception $e) {
                setFlash('Failed to create blog post.', 'error');
            }
        } else {
            setFlash('Title and content are required.', 'error');
        }
        header('Location: admin.php?panel=blogs');
        exit;
    }

    if ($action === 'update_blog') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cover_image = trim($_POST['cover_image'] ?? '') ?: null;

        if ($id && $title && $content) {
            try {
                $stmt = $pdo->prepare('UPDATE blogs SET title = ?, content = ?, cover_image = ? WHERE id = ?');
                $stmt->execute([$title, $content, $cover_image, $id]);
                setFlash('Blog post updated!', 'success');
            } catch (Exception $e) {
                setFlash('Failed to update blog post.', 'error');
            }
        }
        header('Location: admin.php?panel=blogs');
        exit;
    }

    if ($action === 'delete_blog') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('Blog post deleted.', 'success');
            } catch (Exception $e) {
                setFlash('Failed to delete blog post.', 'error');
            }
        }
        header('Location: admin.php?panel=blogs');
        exit;
    }
}

// Current panel
$panel = $_GET['panel'] ?? 'dashboard';
$editAction = $_GET['action'] ?? '';
$editId = (int)($_GET['id'] ?? 0);

// Load data for panels
$projectsList = [];
$memoriesList = [];
$blogsList = [];
$editItem = null;
$totalProjects = 0;
$totalMemories = 0;
$totalBlogs = 0;

if (isLoggedIn() && $pdo) {
    try {
        // Dashboard counts
        $totalProjects = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
        $totalMemories = (int)$pdo->query('SELECT COUNT(*) FROM memories')->fetchColumn();
        $totalBlogs = (int)$pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn();

        if ($panel === 'projects') {
            $projectsList = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
            if ($editAction === 'edit' && $editId) {
                $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
                $stmt->execute([$editId]);
                $editItem = $stmt->fetch();
            }
        }

        if ($panel === 'memories') {
            $memoriesList = $pdo->query('SELECT * FROM memories ORDER BY created_at DESC')->fetchAll();
        }

        if ($panel === 'blogs') {
            $blogsList = $pdo->query('SELECT * FROM blogs ORDER BY created_at DESC')->fetchAll();
            if ($editAction === 'edit' && $editId) {
                $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
                $stmt->execute([$editId]);
                $editItem = $stmt->fetch();
            }
        }
    } catch (Exception $e) {
        // Silently fail
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — Anup Dahal Portfolio</title>
  <meta name="robots" content="noindex, nofollow">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Outfit:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<?php if ($flash): ?>
  <div class="toast-container">
    <div class="toast <?= e($flash['type']) ?> auto-hide"><?= e($flash['message']) ?></div>
  </div>
<?php endif; ?>

<?php if (!isLoggedIn()): ?>
<!-- LOGIN VIEW -->
<div class="login-wrapper">
  <div class="login-card">
    <div class="login-logo">AD</div>
    <div class="login-subtitle">Admin Panel</div>

    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label class="form-label" for="loginUsername">Username</label>
        <input type="text" id="loginUsername" name="username" class="form-input" placeholder="Enter username" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginPassword">Password</label>
        <input type="password" id="loginPassword" name="password" class="form-input" placeholder="Enter password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="login-btn">Sign In</button>
      <?php if ($error): ?>
        <div class="login-error" style="display:block;"><?= e($error) ?></div>
      <?php endif; ?>
    </form>

    <a href="index.php" class="login-back">&larr; Back to Portfolio</a>
  </div>
</div>

<?php else: ?>
<!-- DASHBOARD VIEW -->
<div class="admin-layout">

  <!-- Mobile Toggle -->
  <input type="checkbox" id="sidebarToggle" class="sidebar-toggle-input">
  <label for="sidebarToggle" class="mobile-toggle">&#9776;</label>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-logo">AD</div>
    <div class="sidebar-subtitle">Admin Panel</div>

    <ul class="sidebar-nav">
      <li><a href="admin.php?panel=dashboard" class="<?= $panel === 'dashboard' ? 'active' : '' ?>"><span class="nav-icon">&#128200;</span> Dashboard</a></li>
      <li><a href="admin.php?panel=projects" class="<?= $panel === 'projects' ? 'active' : '' ?>"><span class="nav-icon">&#128187;</span> Projects</a></li>
      <li><a href="admin.php?panel=memories" class="<?= $panel === 'memories' ? 'active' : '' ?>"><span class="nav-icon">&#128248;</span> Memories</a></li>
      <li><a href="admin.php?panel=blogs" class="<?= $panel === 'blogs' ? 'active' : '' ?>"><span class="nav-icon">&#128221;</span> Blogs</a></li>
    </ul>

    <div class="sidebar-footer">
      <a href="index.php" style="display:block;text-align:center;font-size:0.85rem;color:var(--pine);margin-bottom:0.8rem;text-decoration:none;">&larr; View Portfolio</a>
      <a href="admin.php?logout=1" class="logout-btn">
        <span>&#9211;</span> Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">

    <?php if ($panel === 'dashboard'): ?>
    <!-- Dashboard Panel -->
    <div class="admin-header">
      <h1 class="admin-title">Dashboard</h1>
    </div>

    <div class="dash-stats">
      <div class="dash-stat">
        <div class="dash-stat-icon">&#128187;</div>
        <div class="dash-stat-num"><?= $totalProjects ?></div>
        <div class="dash-stat-label">Projects</div>
      </div>
      <div class="dash-stat">
        <div class="dash-stat-icon">&#128248;</div>
        <div class="dash-stat-num"><?= $totalMemories ?></div>
        <div class="dash-stat-label">Memories</div>
      </div>
      <div class="dash-stat">
        <div class="dash-stat-icon">&#128221;</div>
        <div class="dash-stat-num"><?= $totalBlogs ?></div>
        <div class="dash-stat-label">Blog Posts</div>
      </div>
    </div>

    <p style="color:var(--pine);font-size:0.9rem;">
      Use the sidebar to manage your portfolio content. Changes are reflected on the live site immediately.
    </p>
    <?php endif; ?>

    <?php if ($panel === 'projects'): ?>
    <!-- Projects Panel -->
    <div class="panel-header">
      <h2 class="panel-title">Projects</h2>
      <a href="admin.php?panel=projects&action=add" class="add-btn">+ Add Project</a>
    </div>

    <?php if ($editAction === 'confirm_delete' && $editId): ?>
      <div class="admin-form-card" style="border-color:rgba(231,76,60,0.3);">
        <h3 class="admin-form-title" style="color:var(--danger);">Delete Project?</h3>
        <p style="color:var(--mist);margin-bottom:1.5rem;">Are you sure you want to delete this project? This action cannot be undone.</p>
        <div class="form-actions">
          <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="delete_project">
            <input type="hidden" name="id" value="<?= $editId ?>">
            <button type="submit" class="save-btn" style="background:var(--danger);">Yes, Delete</button>
          </form>
          <a href="admin.php?panel=projects" class="cancel-btn">Cancel</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($editAction === 'add' || ($editAction === 'edit' && $editItem)): ?>
      <!-- Project Form -->
      <div class="admin-form-card">
        <h3 class="admin-form-title"><?= $editAction === 'edit' ? 'Edit Project' : 'Add Project' ?></h3>
        <form method="POST" action="admin.php">
          <input type="hidden" name="action" value="<?= $editAction === 'edit' ? 'update_project' : 'create_project' ?>">
          <?php if ($editAction === 'edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label" for="projTitle">Title *</label>
            <input type="text" id="projTitle" name="title" class="form-input" placeholder="Project title" value="<?= e($editItem['title'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="projDesc">Description *</label>
            <textarea id="projDesc" name="description" class="form-textarea" placeholder="Project description" required><?= e($editItem['description'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="projImage">Image URL</label>
            <input type="text" id="projImage" name="image_url" class="form-input" placeholder="https://example.com/image.jpg" value="<?= e($editItem['image_url'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="projTech">Tech Stack</label>
            <input type="text" id="projTech" name="tech_stack" class="form-input" placeholder="HTML,CSS,PHP (comma separated)" value="<?= e($editItem['tech_stack'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="projLive">Live URL</label>
            <input type="text" id="projLive" name="live_url" class="form-input" placeholder="https://yourproject.com" value="<?= e($editItem['live_url'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="projGithub">GitHub URL</label>
            <input type="text" id="projGithub" name="github_url" class="form-input" placeholder="https://github.com/user/repo" value="<?= e($editItem['github_url'] ?? '') ?>">
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Save Project</button>
            <a href="admin.php?panel=projects" class="cancel-btn">Cancel</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Tech Stack</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($projectsList)): ?>
            <tr><td colspan="5" class="admin-empty">No projects yet.</td></tr>
          <?php else: ?>
            <?php foreach ($projectsList as $p): ?>
              <tr>
                <td><?= e($p['title']) ?></td>
                <td><?= e(truncateStr($p['description'], 60)) ?></td>
                <td><?= e($p['tech_stack'] ?? '-') ?></td>
                <td><?= formatDate($p['created_at']) ?></td>
                <td>
                  <a href="admin.php?panel=projects&action=edit&id=<?= (int)$p['id'] ?>" class="action-btn edit">Edit</a>
                  <a href="admin.php?panel=projects&action=confirm_delete&id=<?= (int)$p['id'] ?>" class="action-btn delete">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <?php if ($panel === 'memories'): ?>
    <!-- Memories Panel -->
    <div class="panel-header">
      <h2 class="panel-title">Memories</h2>
      <a href="admin.php?panel=memories&action=add" class="add-btn">+ Add Memory</a>
    </div>

    <?php if ($editAction === 'confirm_delete' && $editId): ?>
      <div class="admin-form-card" style="border-color:rgba(231,76,60,0.3);">
        <h3 class="admin-form-title" style="color:var(--danger);">Delete Memory?</h3>
        <p style="color:var(--mist);margin-bottom:1.5rem;">Are you sure you want to delete this memory? This action cannot be undone.</p>
        <div class="form-actions">
          <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="delete_memory">
            <input type="hidden" name="id" value="<?= $editId ?>">
            <button type="submit" class="save-btn" style="background:var(--danger);">Yes, Delete</button>
          </form>
          <a href="admin.php?panel=memories" class="cancel-btn">Cancel</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($editAction === 'add'): ?>
      <!-- Memory Form -->
      <div class="admin-form-card">
        <h3 class="admin-form-title">Add Memory</h3>
        <form method="POST" action="admin.php">
          <input type="hidden" name="action" value="create_memory">

          <div class="form-group">
            <label class="form-label" for="memTitle">Title *</label>
            <input type="text" id="memTitle" name="title" class="form-input" placeholder="Memory title" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="memDesc">Description</label>
            <textarea id="memDesc" name="description" class="form-textarea" placeholder="Short description (optional)" style="min-height:80px;"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="memImage">Image URL *</label>
            <input type="text" id="memImage" name="image_url" class="form-input" placeholder="https://example.com/photo.jpg" required>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Save Memory</button>
            <a href="admin.php?panel=memories" class="cancel-btn">Cancel</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($memoriesList)): ?>
            <tr><td colspan="4" class="admin-empty">No memories yet.</td></tr>
          <?php else: ?>
            <?php foreach ($memoriesList as $m): ?>
              <tr>
                <td><?= e($m['title']) ?></td>
                <td><?= e(truncateStr($m['description'] ?? '', 40)) ?></td>
                <td><?= formatDate($m['created_at']) ?></td>
                <td>
                  <a href="admin.php?panel=memories&action=confirm_delete&id=<?= (int)$m['id'] ?>" class="action-btn delete">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <?php if ($panel === 'blogs'): ?>
    <!-- Blogs Panel -->
    <div class="panel-header">
      <h2 class="panel-title">Blog Posts</h2>
      <a href="admin.php?panel=blogs&action=add" class="add-btn">+ New Post</a>
    </div>

    <?php if ($editAction === 'confirm_delete' && $editId): ?>
      <div class="admin-form-card" style="border-color:rgba(231,76,60,0.3);">
        <h3 class="admin-form-title" style="color:var(--danger);">Delete Blog Post?</h3>
        <p style="color:var(--mist);margin-bottom:1.5rem;">Are you sure you want to delete this blog post? This action cannot be undone.</p>
        <div class="form-actions">
          <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="delete_blog">
            <input type="hidden" name="id" value="<?= $editId ?>">
            <button type="submit" class="save-btn" style="background:var(--danger);">Yes, Delete</button>
          </form>
          <a href="admin.php?panel=blogs" class="cancel-btn">Cancel</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($editAction === 'add' || ($editAction === 'edit' && $editItem)): ?>
      <!-- Blog Form -->
      <div class="admin-form-card">
        <h3 class="admin-form-title"><?= $editAction === 'edit' ? 'Edit Blog Post' : 'New Blog Post' ?></h3>
        <form method="POST" action="admin.php">
          <input type="hidden" name="action" value="<?= $editAction === 'edit' ? 'update_blog' : 'create_blog' ?>">
          <?php if ($editAction === 'edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label" for="blogTitle">Title *</label>
            <input type="text" id="blogTitle" name="title" class="form-input" placeholder="Blog post title" value="<?= e($editItem['title'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="blogContent">Content *</label>
            <textarea id="blogContent" name="content" class="form-textarea" placeholder="Write your blog post..." style="min-height:200px;" required><?= e($editItem['content'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="blogCover">Cover Image URL</label>
            <input type="text" id="blogCover" name="cover_image" class="form-input" placeholder="https://example.com/cover.jpg" value="<?= e($editItem['cover_image'] ?? '') ?>">
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Publish</button>
            <a href="admin.php?panel=blogs" class="cancel-btn">Cancel</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Excerpt</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($blogsList)): ?>
            <tr><td colspan="4" class="admin-empty">No blog posts yet.</td></tr>
          <?php else: ?>
            <?php foreach ($blogsList as $b): ?>
              <tr>
                <td><?= e($b['title']) ?></td>
                <td><?= e(truncateStr($b['content'] ?? '', 50)) ?></td>
                <td><?= formatDate($b['created_at']) ?></td>
                <td>
                  <a href="admin.php?panel=blogs&action=edit&id=<?= (int)$b['id'] ?>" class="action-btn edit">Edit</a>
                  <a href="admin.php?panel=blogs&action=confirm_delete&id=<?= (int)$b['id'] ?>" class="action-btn delete">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </main>
</div>
<?php endif; ?>

</body>
</html>
