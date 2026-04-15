<?php
require_once __DIR__ . '/includes/config.php';

// Fetch projects from DB
$projects = [];
$projectSearch = $_GET['search'] ?? '';
$projectPage = max(1, (int)($_GET['page'] ?? 1));
$projectLimit = 6;
$projectOffset = ($projectPage - 1) * $projectLimit;
$totalProjects = 0;

$pdo = getDB();
if ($pdo) {
    try {
        // Count
        if ($projectSearch) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects WHERE title LIKE ? OR description LIKE ?');
            $stmt->execute(["%$projectSearch%", "%$projectSearch%"]);
        } else {
            $stmt = $pdo->query('SELECT COUNT(*) as total FROM projects');
        }
        $totalProjects = (int)$stmt->fetch()['total'];

        // Fetch
        if ($projectSearch) {
            $stmt = $pdo->prepare('SELECT * FROM projects WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
            $stmt->execute(["%$projectSearch%", "%$projectSearch%", $projectLimit, $projectOffset]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM projects ORDER BY created_at DESC LIMIT ? OFFSET ?');
            $stmt->execute([$projectLimit, $projectOffset]);
        }
        $projects = $stmt->fetchAll();
    } catch (Exception $e) {
        $projects = [];
    }
}

// Fallback projects if DB unavailable
if (empty($projects) && !$projectSearch) {
    $projects = [
        ['id' => 0, 'title' => 'Stock Dashboard', 'description' => 'A complete IPO tracking and profit management system built with real-time calculation and analytics dashboard.', 'tech_stack' => 'HTML,CSS,PHP,Chart.js', 'live_url' => '', 'github_url' => '', 'image_url' => ''],
        ['id' => 0, 'title' => 'MeroSutra', 'description' => 'A smart municipal and property management system designed for local administrative operations.', 'tech_stack' => 'PHP,MySQL,Bootstrap', 'live_url' => '', 'github_url' => '', 'image_url' => ''],
        ['id' => 0, 'title' => 'BCA Notes Hub', 'description' => 'A student learning platform that organizes BCA notes, resources, and academic materials.', 'tech_stack' => 'HTML,CSS,PHP', 'live_url' => '', 'github_url' => '', 'image_url' => ''],
        ['id' => 0, 'title' => 'School Management System', 'description' => 'A full-stack school management system connecting administration, teachers, and students.', 'tech_stack' => 'PHP,MySQL,HTML,CSS', 'live_url' => '', 'github_url' => '', 'image_url' => ''],
        ['id' => 0, 'title' => 'ERP System', 'description' => 'Full school ERP system with student management, marks, attendance, and reporting modules.', 'tech_stack' => 'PHP,MySQL,HTML,CSS', 'live_url' => '', 'github_url' => '', 'image_url' => ''],
    ];
    $totalProjects = count($projects);
}

$totalProjectPages = max(1, (int)ceil($totalProjects / $projectLimit));

// Fetch memories from DB
$memories = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM memories ORDER BY created_at DESC LIMIT 12');
        $stmt->execute();
        $memories = $stmt->fetchAll();
    } catch (Exception $e) {
        $memories = [];
    }
}

// Fetch blogs from DB
$blogs = [];
$blogSearch = $_GET['blog_search'] ?? '';
if ($pdo) {
    try {
        if ($blogSearch) {
            $stmt = $pdo->prepare('SELECT id, title, LEFT(content, 200) as excerpt, cover_image, created_at FROM blogs WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT 6');
            $stmt->execute(["%$blogSearch%", "%$blogSearch%"]);
        } else {
            $stmt = $pdo->query('SELECT id, title, LEFT(content, 200) as excerpt, cover_image, created_at FROM blogs ORDER BY created_at DESC LIMIT 6');
        }
        $blogs = $stmt->fetchAll();
    } catch (Exception $e) {
        $blogs = [];
    }
}

// Project emoji helper
function getProjectEmoji($title) {
    $map = ['stock' => '&#128200;', 'mero' => '&#127968;', 'bca' => '&#128218;', 'note' => '&#128218;', 'school' => '&#127979;', 'erp' => '&#127891;', 'dashboard' => '&#128202;'];
    $lower = strtolower($title);
    foreach ($map as $key => $emoji) {
        if (strpos($lower, $key) !== false) return $emoji;
    }
    return '&#128187;';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anup Dahal — Portfolio</title>
  <meta name="description" content="Anup Dahal's professional portfolio showcasing web development, programming projects, experience, education, and contact information.">
  <meta name="author" content="Anup Dahal">
  <meta name="robots" content="index, follow">
  <meta name="keywords" content="Anup Dahal, Portfolio, Web Developer, Frontend Developer, Full-stack Developer, HTML, CSS, PHP, Projects">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Anup Dahal — Portfolio">
  <meta property="og:description" content="Explore Anup Dahal's professional portfolio featuring web development projects, programming skills, and experience.">
  <meta property="og:image" content="assets/anupImg.jpeg">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Anup Dahal — Portfolio">
  <meta name="twitter:description" content="Professional portfolio of Anup Dahal, showcasing projects, skills, and experience in web development.">
  <meta name="twitter:image" content="assets/anupImg.jpeg">

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Outfit:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- CSS-only mobile menu toggle -->
<input type="checkbox" id="menuToggle" class="menu-toggle-input">

<!-- NAV -->
<nav>
  <a href="#hero" class="nav-logo">AD</a>

  <ul class="nav-center">
    <li><a href="#about">About</a></li>
    <li><a href="#skills">Skills</a></li>
    <li><a href="#education">Education</a></li>
    <li><a href="#experience">Experience</a></li>
    <li><a href="#projects">Projects</a></li>
    <li><a href="#memories">Memories</a></li>
    <li><a href="#blog">Blog</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>

  <div class="nav-right">
    <a href="?toggle_theme=1<?= $projectSearch ? '&search=' . urlencode($projectSearch) : '' ?>" class="theme-toggle" aria-label="Toggle theme">
      <?= $theme === 'dark' ? '&#9788;' : '&#9790;' ?>
    </a>
    <label for="menuToggle" class="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </label>
  </div>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu">
  <label for="menuToggle" class="mobile-menu-close">&#10005;</label>
  <a href="#about">About</a>
  <a href="#skills">Skills</a>
  <a href="#education">Education</a>
  <a href="#experience">Experience</a>
  <a href="#projects">Projects</a>
  <a href="#memories">Memories</a>
  <a href="#blog">Blog</a>
  <a href="#contact">Contact</a>
</div>

<!-- HERO -->
<section id="hero">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <svg class="mountain-bg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#040810"/>
        <stop offset="100%" stop-color="#0d1b2a"/>
      </linearGradient>
      <linearGradient id="snowGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#edf6f9" stop-opacity="0.9"/>
        <stop offset="100%" stop-color="#a8dadc" stop-opacity="0.3"/>
      </linearGradient>
    </defs>
    <rect width="1440" height="900" fill="url(#skyGrad)"/>
    <g fill="white" opacity="0.5">
      <circle cx="120" cy="60" r="1.2"/><circle cx="220" cy="30" r="0.8"/>
      <circle cx="380" cy="80" r="1"/><circle cx="550" cy="20" r="1.4"/>
      <circle cx="700" cy="55" r="0.7"/><circle cx="870" cy="35" r="1.1"/>
      <circle cx="1000" cy="70" r="0.9"/><circle cx="1150" cy="25" r="1.3"/>
      <circle cx="1300" cy="60" r="0.8"/><circle cx="1420" cy="40" r="1"/>
      <circle cx="60" cy="120" r="0.6"/><circle cx="450" cy="140" r="0.9"/>
      <circle cx="760" cy="100" r="0.7"/><circle cx="950" cy="130" r="1.1"/>
      <circle cx="1200" cy="110" r="0.8"/>
    </g>
    <ellipse cx="720" cy="200" rx="600" ry="150" fill="rgba(82,183,136,0.04)"/>
    <ellipse cx="720" cy="250" rx="400" ry="80" fill="rgba(45,106,79,0.06)"/>
    <polygon points="0,600 150,320 300,480 500,250 650,400 800,200 950,380 1100,280 1250,420 1440,300 1440,900 0,900" fill="#0a1520" opacity="0.9"/>
    <polygon points="0,680 100,480 250,560 450,350 600,500 750,300 900,460 1050,360 1200,520 1350,400 1440,480 1440,900 0,900" fill="#0d1b2a"/>
    <polygon points="450,350 480,380 420,380" fill="url(#snowGrad)" opacity="0.8"/>
    <polygon points="750,300 785,340 715,340" fill="url(#snowGrad)" opacity="0.9"/>
    <polygon points="1050,360 1080,395 1020,395" fill="url(#snowGrad)" opacity="0.7"/>
    <polygon points="0,750 200,640 400,700 600,620 800,680 1000,600 1200,660 1440,620 1440,900 0,900" fill="#1a3328" opacity="0.85"/>
    <rect x="0" y="820" width="1440" height="80" fill="#080e1a"/>
    <circle cx="1100" cy="120" r="40" fill="rgba(237,246,249,0.08)"/>
    <circle cx="1100" cy="120" r="32" fill="rgba(237,246,249,0.12)"/>
    <circle cx="1100" cy="120" r="22" fill="rgba(237,246,249,0.18)"/>
  </svg>

  <div class="hero-content">
    <h1 class="hero-name">Anup<br><span>Dahal</span></h1>
    <p class="hero-tagline" style="margin-top:1.5rem;">
      <strong>I</strong> <span class="typing-text">build systems that solve real problems.</span>
    </p>
    <div class="hero-cta">
      <a href="#projects" class="btn-primary">View Projects</a>
      <a href="#contact" class="btn-outline">Get in Touch</a>
    </div>
  </div>

  <div class="scroll-hint">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">01</span>
      <h2 class="section-title">About Me</h2>
      <div class="section-line"></div>
    </div>

    <div class="about-grid">
      <div class="about-text">
        <p>
          Hi, I'm <strong>Anup Dahal</strong> — a student and aspiring developer passionate about building
          practical systems that solve real-world problems.
        </p>
        <p>
          Originally from <strong>Bikaner, India</strong> and raised in the hills of
          <strong>Okhaldhunga, Nepal</strong>, I'm currently based in
          <strong>Gauradaha, Jhapa</strong>.
        </p>
        <p>
          I enjoy creating simple and efficient systems that make everyday tasks easier and more useful in real life.
          I also have experience working as an assistant computer operator at Gauradaha Municipality, handling digital workflows and system operations.
        </p>
        <p>
          Outside of tech, I love mountains, travel, and nature, and I actively follow the Nepal Stock Market (NEPSE) with a growing interest in investment.
        </p>
        <p>Quiet by nature, focused by mindset — I believe in continuous learning and building things that matter.</p>

        <div class="about-stats">
          <div class="stat-card">
            <div class="stat-num">5+</div>
            <div class="stat-label">Projects</div>
          </div>
          <div class="stat-card">
            <div class="stat-num">2+</div>
            <div class="stat-label">Years Experience</div>
          </div>
          <div class="stat-card">
            <div class="stat-num">BCA</div>
            <div class="stat-label">Degree (Ongoing)</div>
          </div>
          <div class="stat-card">
            <div class="stat-num">10+</div>
            <div class="stat-label">Technologies</div>
          </div>
        </div>

        <div class="interests-wrap">
          <div class="interests-title">// Interests</div>
          <div class="interests-pills">
            <span class="pill">&#128187; Web Dev</span>
            <span class="pill">&#127911; Music</span>
            <span class="pill">&#9997;&#65039; Writing</span>
            <span class="pill">&#127951; Cricket</span>
            <span class="pill">&#127956;&#65039; Mountains</span>
            <span class="pill">&#129523; Travel</span>
            <span class="pill">&#128200; NEPSE</span>
          </div>
        </div>
      </div>

      <div class="about-photo">
        <img src="assets/anupImg.jpeg" alt="Anup Dahal" loading="lazy">
      </div>
    </div>
  </div>
</section>

<!-- SKILLS -->
<section id="skills">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">02</span>
      <h2 class="section-title">Skills</h2>
      <div class="section-line"></div>
    </div>

    <div class="skills-grid">
      <div class="skill-category">
        <div class="skill-cat-icon">&#127760;</div>
        <div class="skill-cat-title">Frontend Development</div>
        <div class="skill-items">
          <span class="skill-tag">HTML5</span>
          <span class="skill-tag">CSS3</span>
          <span class="skill-tag">Bootstrap</span>
          <span class="skill-tag">Responsive Design</span>
        </div>
      </div>

      <div class="skill-category">
        <div class="skill-cat-icon">&#9881;&#65039;</div>
        <div class="skill-cat-title">Backend Development</div>
        <div class="skill-items">
          <span class="skill-tag">PHP</span>
          <span class="skill-tag">REST API</span>
          <span class="skill-tag">MySQL</span>
          <span class="skill-tag">SQL</span>
        </div>
      </div>

      <div class="skill-category">
        <div class="skill-cat-icon">&#128736;&#65039;</div>
        <div class="skill-cat-title">Tools &amp; Others</div>
        <div class="skill-items">
          <span class="skill-tag">Git</span>
          <span class="skill-tag">GitHub</span>
          <span class="skill-tag">VS Code</span>
          <span class="skill-tag">Figma</span>
          <span class="skill-tag">Linux</span>
        </div>
      </div>

      <div class="skill-category">
        <div class="skill-cat-icon">&#128161;</div>
        <div class="skill-cat-title">Concepts</div>
        <div class="skill-items">
          <span class="skill-tag">OOP</span>
          <span class="skill-tag">MVC</span>
          <span class="skill-tag">CRUD</span>
          <span class="skill-tag">Authentication</span>
          <span class="skill-tag">Database Design</span>
          <span class="skill-tag">API Design</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- EDUCATION -->
<section id="education">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">03</span>
      <h2 class="section-title">Education</h2>
      <div class="section-line"></div>
    </div>

    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="edu-period">2064 – 2068 BS</div>
        <div class="edu-school">Shree Suryodaya Nimna Madyamik Bidhyalaya</div>
        <div class="edu-location">&#128205; Dhimdime, Gauradaha-3, Jhapa</div>
        <span class="edu-badge">Grades 0–3</span>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="edu-period">2069 – 2072 BS</div>
        <div class="edu-school">Shree Red Star Boarding English School</div>
        <div class="edu-location">&#128205; Gauradaha-3, Shantitol, Jhapa</div>
        <span class="edu-badge">Grades 2–5</span>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="edu-period">2073 – 2074 BS</div>
        <div class="edu-school">Gyanjyoti Secondary School</div>
        <div class="edu-location">&#128205; Gauradaha-2, Sayed Gate, Jhapa</div>
        <span class="edu-badge">Grades 7–8</span>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="edu-period">2075 – 2078 BS</div>
        <div class="edu-school">Janata Secondary School</div>
        <div class="edu-location">&#128205; Gauradaha-1, Dipu Chok, Jhapa</div>
        <span class="edu-badge">Grades 9–12 · Computer Engineering</span>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot" style="background: var(--amber); box-shadow: 0 0 0 4px rgba(233,196,106,0.15);"></div>
        <div class="edu-period">2022 AD – Present</div>
        <div class="edu-school">Model College Damak</div>
        <div class="edu-location">&#128205; New Amda Road, Damak-10, Jhapa</div>
        <span class="edu-badge" style="border-color: rgba(233,196,106,0.4); color: var(--amber);">BCA · 8 Semesters · Ongoing</span>
      </div>
    </div>
  </div>
</section>

<!-- EXPERIENCE -->
<section id="experience">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">04</span>
      <h2 class="section-title">Experience</h2>
      <div class="section-line"></div>
    </div>

    <div class="exp-grid">
      <div class="exp-card current full">
        <div class="exp-top">
          <span class="exp-icon">&#127963;&#65039;</span>
          <span class="exp-status active live-dot">Present</span>
        </div>
        <div class="exp-role">Assistant Computer Operator</div>
        <div class="exp-org">Gauradaha Municipality Office</div>
        <div class="exp-period">2079/10/11 BS — Present · Gauradaha, Jhapa</div>
        <div class="exp-desc">Managing digital workflows, handling municipal systems, and supporting administrative operations.</div>
        <div class="exp-tags">
          <span class="exp-tag">Revenue System</span>
          <span class="exp-tag">Registration</span>
          <span class="exp-tag">Social Security</span>
          <span class="exp-tag">Citizenship Recommendation</span>
          <span class="exp-tag">Office Documentation</span>
        </div>
      </div>

      <div class="exp-card">
        <div class="exp-top">
          <span class="exp-icon">&#128188;</span>
          <span class="exp-status ojt">OJT</span>
        </div>
        <div class="exp-role">On-the-Job Training</div>
        <div class="exp-org">Gauradaha Municipality Office</div>
        <div class="exp-period">6 Months · Grade 10 &amp; 12 Internship</div>
        <div class="exp-desc">Hands-on training on municipal systems, workflows, and basic administrative operations.</div>
        <div class="exp-tags">
          <span class="exp-tag">Municipal Workflow</span>
          <span class="exp-tag">Office Systems</span>
          <span class="exp-tag">Documentation</span>
        </div>
      </div>

      <div class="exp-card workshop">
        <div class="exp-top">
          <span class="exp-icon">&#9889;</span>
          <span class="exp-status ws">Workshop</span>
        </div>
        <div class="exp-role">MERN Stack Development Workshop</div>
        <div class="exp-org">Model College Damak</div>
        <div class="exp-period">3-Day Intensive Training</div>
        <div class="exp-desc">Intensive hands-on workshop on full-stack web development using modern technologies.</div>
        <div class="exp-tags">
          <span class="exp-tag">PHP</span>
          <span class="exp-tag">MySQL</span>
          <span class="exp-tag">REST API</span>
          <span class="exp-tag">Full Stack</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PROJECTS -->
<section id="projects">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">05</span>
      <h2 class="section-title">Projects</h2>
      <div class="section-line"></div>
    </div>

    <div class="projects-toolbar">
      <form method="GET" action="index.php#projects" class="projects-search-form">
        <input type="text" name="search" class="filter-input" placeholder="Search projects..." value="<?= e($projectSearch) ?>">
        <button type="submit" class="filter-btn">Search</button>
        <?php if ($projectSearch): ?>
          <a href="index.php#projects" class="filter-btn">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (empty($projects)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">&#128194;</div>
        <p class="empty-state-text">No projects found.</p>
      </div>
    <?php else: ?>
      <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
          <a href="#project-<?= (int)$project['id'] ?>" class="project-card">
            <div class="project-thumb">
              <?php if (!empty($project['image_url'])): ?>
                <img src="<?= e($project['image_url']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
              <?php else: ?>
                <?= getProjectEmoji($project['title']) ?>
              <?php endif; ?>
            </div>
            <div class="project-body">
              <div class="project-title"><?= e($project['title']) ?></div>
              <div class="project-desc"><?= e(truncateStr($project['description'], 100)) ?></div>
              <?php if (!empty($project['tech_stack'])): ?>
                <div class="project-tech">
                  <?php foreach (explode(',', $project['tech_stack']) as $tech): ?>
                    <span><?= e(trim($tech)) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <div class="project-links">
                <?php if (!empty($project['live_url'])): ?>
                  <span class="project-link">Live &rarr;</span>
                <?php endif; ?>
                <?php if (!empty($project['github_url'])): ?>
                  <span class="project-link">GitHub &rarr;</span>
                <?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($totalProjectPages > 1): ?>
        <div class="projects-pagination">
          <?php for ($i = 1; $i <= $totalProjectPages; $i++): ?>
            <a href="?page=<?= $i ?><?= $projectSearch ? '&search=' . urlencode($projectSearch) : '' ?>#projects" class="page-btn <?= $i === $projectPage ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Project Detail Modals (CSS :target) -->
<?php foreach ($projects as $project): ?>
  <?php if (!empty($project['id'])): ?>
  <div id="project-<?= (int)$project['id'] ?>" class="project-modal-overlay">
    <div class="project-modal-card">
      <a href="#projects" class="project-modal-close">&#10005;</a>
      <h2 class="project-modal-title"><?= e($project['title']) ?></h2>
      <p class="project-modal-desc"><?= e($project['description']) ?></p>
      <?php if (!empty($project['tech_stack'])): ?>
        <div class="project-tech">
          <?php foreach (explode(',', $project['tech_stack']) as $tech): ?>
            <span><?= e(trim($tech)) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="project-modal-links">
        <?php if (!empty($project['live_url'])): ?>
          <a href="<?= e($project['live_url']) ?>" class="btn-primary" target="_blank" style="font-size:0.85rem;padding:0.6rem 1.5rem;">View Live</a>
        <?php endif; ?>
        <?php if (!empty($project['github_url'])): ?>
          <a href="<?= e($project['github_url']) ?>" class="btn-outline" target="_blank" style="font-size:0.85rem;padding:0.6rem 1.5rem;">GitHub</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
<?php endforeach; ?>

<!-- MEMORIES -->
<section id="memories">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">06</span>
      <h2 class="section-title">Memories</h2>
      <div class="section-line"></div>
    </div>

    <?php if (empty($memories)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">&#128248;</div>
        <p class="empty-state-text">Gallery coming soon!</p>
      </div>
    <?php else: ?>
      <div class="memories-grid">
        <?php foreach ($memories as $memory): ?>
          <a href="#memory-<?= (int)$memory['id'] ?>" class="memory-card">
            <img src="<?= e($memory['image_url']) ?>" alt="<?= e($memory['title']) ?>" loading="lazy">
            <div class="memory-overlay">
              <h3><?= e($memory['title']) ?></h3>
              <?php if (!empty($memory['description'])): ?>
                <p><?= e($memory['description']) ?></p>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Memory Modals (CSS :target) -->
<?php foreach ($memories as $memory): ?>
  <div id="memory-<?= (int)$memory['id'] ?>" class="memory-modal">
    <div class="memory-modal-content">
      <a href="#memories" class="modal-close">&#10005;</a>
      <img src="<?= e($memory['image_url']) ?>" alt="<?= e($memory['title']) ?>">
      <div class="memory-modal-info">
        <h3><?= e($memory['title']) ?></h3>
        <?php if (!empty($memory['description'])): ?>
          <p><?= e($memory['description']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<!-- BLOG -->
<section id="blog">
  <div class="section-wrapper">
    <div class="section-header">
      <span class="section-num">07</span>
      <h2 class="section-title">Blog</h2>
      <div class="section-line"></div>
    </div>

    <div class="blog-toolbar">
      <form method="GET" action="index.php#blog" class="blog-search-form">
        <?php if ($projectSearch): ?>
          <input type="hidden" name="search" value="<?= e($projectSearch) ?>">
        <?php endif; ?>
        <input type="text" name="blog_search" class="filter-input" placeholder="Search blog posts..." value="<?= e($blogSearch) ?>">
        <button type="submit" class="filter-btn">Search</button>
      </form>
    </div>

    <?php if (empty($blogs)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">&#128221;</div>
        <p class="empty-state-text">No blog posts yet. Stay tuned!</p>
      </div>
    <?php else: ?>
      <div class="blog-grid">
        <?php foreach ($blogs as $blog): ?>
          <a href="blog.php?id=<?= (int)$blog['id'] ?>" class="blog-card">
            <div class="blog-cover">
              <?php if (!empty($blog['cover_image'])): ?>
                <img src="<?= e($blog['cover_image']) ?>" alt="<?= e($blog['title']) ?>" loading="lazy">
              <?php else: ?>
                <span class="blog-cover-placeholder">&#128221;</span>
              <?php endif; ?>
            </div>
            <div class="blog-body">
              <div class="blog-date"><?= formatDate($blog['created_at']) ?></div>
              <div class="blog-title"><?= e($blog['title']) ?></div>
              <div class="blog-excerpt"><?= e($blog['excerpt'] ?? '') ?>...</div>
              <span class="blog-read-more">Read More &rarr;</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- CONTACT -->
<section id="contact">
  <div class="section-wrapper">
    <div class="contact-inner">
      <div class="section-header" style="justify-content: center; margin-bottom: 1rem;">
        <span class="section-num">08</span>
        <h2 class="section-title">Let's Connect</h2>
      </div>

      <p class="contact-subtitle">Got a project in mind or just want to say hello? Feel free to reach out.</p>

      <div class="contact-cards">
        <div class="contact-row">
          <span class="contact-icon">&#9993;&#65039;</span>
          <div class="contact-info">
            <div class="contact-type">Email</div>
            <div class="contact-val"><a href="mailto:dahal6270@gmail.com">dahal6270@gmail.com</a></div>
          </div>
        </div>
        <div class="contact-row">
          <span class="contact-icon">&#128222;</span>
          <div class="contact-info">
            <div class="contact-type">Phone</div>
            <div class="contact-val"><a href="tel:+9779804902634">+977 9804902634</a></div>
          </div>
        </div>
        <div class="contact-row">
          <span class="contact-icon">&#128205;</span>
          <div class="contact-info">
            <div class="contact-type">Address</div>
            <div class="contact-val">Nawatoli, Gauradaha Municipality Ward No. 2, Nepal</div>
          </div>
        </div>
      </div>

      <a href="mailto:dahal6270@gmail.com" class="btn-primary" style="font-size: 1rem; padding: 1rem 2.5rem;">Send a Message &rarr;</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <span>With &#9829; Anup Dahal</span>
  <div class="footer-links">
    <a href="admin.php">Admin</a>
  </div>
</footer>

</body>
</html>
