<?php
require_once __DIR__ . '/includes/config.php';

$blogId = (int)($_GET['id'] ?? 0);
$blog = null;

if ($blogId) {
    $pdo = getDB();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
            $stmt->execute([$blogId]);
            $blog = $stmt->fetch();
        } catch (Exception $e) {
            $blog = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $blog ? e($blog['title']) . ' — ' : '' ?>Blog — Anup Dahal</title>
  <meta name="robots" content="index, follow">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Outfit:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">AD</a>
  <ul class="nav-center">
    <li><a href="index.php#about">About</a></li>
    <li><a href="index.php#projects">Projects</a></li>
    <li><a href="index.php#blog">Blog</a></li>
    <li><a href="index.php#contact">Contact</a></li>
  </ul>
  <div class="nav-right">
    <a href="?id=<?= $blogId ?>&toggle_theme=1" class="theme-toggle" aria-label="Toggle theme">
      <?= $theme === 'dark' ? '&#9788;' : '&#9790;' ?>
    </a>
  </div>
</nav>

<div class="blog-post-wrapper">
  <?php if (!$blog): ?>
    <div class="empty-state" style="padding-top:8rem;">
      <div class="empty-state-icon">&#128221;</div>
      <p class="empty-state-text">Blog post not found.</p>
      <a href="index.php#blog" class="btn-primary" style="margin-top:2rem;display:inline-block;">Back to Blog</a>
    </div>
  <?php else: ?>
    <div class="blog-post-header">
      <div class="blog-post-date"><?= formatDate($blog['created_at']) ?></div>
      <h1 class="blog-post-title"><?= e($blog['title']) ?></h1>
    </div>

    <div class="blog-post-content">
      <?php
      $paragraphs = explode("\n", $blog['content']);
      foreach ($paragraphs as $p) {
          $p = trim($p);
          if ($p !== '') {
              echo '<p>' . e($p) . '</p>';
          }
      }
      ?>
    </div>

    <div class="blog-post-back">
      <a href="index.php#blog" class="btn-outline">&larr; Back to Blog</a>
    </div>
  <?php endif; ?>
</div>

<!-- FOOTER -->
<footer>
  <span>With &#9829; Anup Dahal</span>
  <div class="footer-links">
    <a href="index.php">Home</a>
  </div>
</footer>

</body>
</html>
