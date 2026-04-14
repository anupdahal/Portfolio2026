/* ═══════════════════════════════════════════════
   Portfolio — Main Application
   ═══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  initLoader();
  initTheme();
  initNav();
  initTyping();
  initScrollAnimations();
  loadProjects();
  loadMemories();
  loadBlogs();
});

/* ─── PAGE LOADER ─── */
function initLoader() {
  window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');
    if (loader) {
      setTimeout(() => loader.classList.add('hidden'), 300);
    }
  });
}

/* ─── TOAST NOTIFICATIONS ─── */
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('hiding');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/* ─── THEME TOGGLE ─── */
function initTheme() {
  const saved = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  updateThemeIcon(saved);

  document.getElementById('themeToggle').addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    updateThemeIcon(next);
  });
}

function updateThemeIcon(theme) {
  const btn = document.getElementById('themeToggle');
  btn.textContent = theme === 'dark' ? '\u2600' : '\u263E';
}

/* ─── NAV ─── */
function initNav() {
  // Scroll effect
  window.addEventListener('scroll', () => {
    document.querySelector('nav').classList.toggle('scrolled', window.scrollY > 50);
    highlightActiveNav();
  });

  // Hamburger
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');

  if (hamburger) {
    hamburger.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
      document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
  }
}

function highlightActiveNav() {
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-center a');
  let current = '';

  sections.forEach(s => {
    if (window.scrollY >= s.offsetTop - 200) current = s.id;
  });

  navLinks.forEach(link => {
    link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
  });
}

/* ─── TYPING EFFECT ─── */
function initTyping() {
  const el = document.getElementById('typing');
  if (!el) return;

  const words = [
    "build systems that solve real problems.",
    "create tools that simplify daily tasks.",
    "love mountains, nature, and clean design.",
    "turn ideas into practical applications."
  ];

  let i = 0, j = 0, isDeleting = false;

  function type() {
    const currentWord = words[i];
    el.textContent = currentWord.substring(0, j);

    if (!isDeleting && j < currentWord.length) {
      j++;
      setTimeout(type, 70);
    } else if (isDeleting && j > 0) {
      j--;
      setTimeout(type, 40);
    } else {
      isDeleting = !isDeleting;
      if (!isDeleting) i = (i + 1) % words.length;
      setTimeout(type, 1000);
    }
  }

  type();
}

/* ─── SCROLL ANIMATIONS ─── */
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.fade-in, .timeline-item').forEach(el => {
    observer.observe(el);
  });
}

/* ─── PROJECTS ─── */
let currentProjectPage = 1;
let currentProjectFilter = '';

async function loadProjects(page = 1, search = '') {
  const grid = document.getElementById('projectsGrid');
  const pagination = document.getElementById('projectsPagination');
  if (!grid) return;

  // Show skeletons
  grid.innerHTML = Array(3).fill('<div class="skeleton skeleton-card"></div>').join('');

  try {
    const params = { page, limit: 6 };
    if (search) params.search = search;

    const result = await API.getProjects(params);
    currentProjectPage = page;

    if (result.data.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="grid-column: 1/-1;">
          <div class="empty-state-icon">📂</div>
          <p class="empty-state-text">No projects found.</p>
        </div>`;
      if (pagination) pagination.innerHTML = '';
      return;
    }

    grid.innerHTML = result.data.map(project => `
      <div class="project-card fade-in visible" onclick="openProjectModal(${JSON.stringify(project).replace(/"/g, '&quot;')})">
        <div class="project-thumb">
          ${project.image_url
            ? `<img src="${escapeHtml(project.image_url)}" alt="${escapeHtml(project.title)}" loading="lazy">`
            : getProjectEmoji(project.title)}
        </div>
        <div class="project-body">
          <div class="project-title">${escapeHtml(project.title)}</div>
          <div class="project-desc">${escapeHtml(truncate(project.description, 100))}</div>
          ${project.tech_stack ? `
            <div class="project-tech">
              ${project.tech_stack.split(',').map(t => `<span>${escapeHtml(t.trim())}</span>`).join('')}
            </div>` : ''}
          <div class="project-links">
            ${project.live_url ? `<a href="${escapeHtml(project.live_url)}" class="project-link" target="_blank" onclick="event.stopPropagation()">Live &rarr;</a>` : ''}
            ${project.github_url ? `<a href="${escapeHtml(project.github_url)}" class="project-link" target="_blank" onclick="event.stopPropagation()">GitHub &rarr;</a>` : ''}
          </div>
        </div>
      </div>
    `).join('');

    // Pagination
    if (pagination && result.pagination.pages > 1) {
      pagination.innerHTML = Array.from({ length: result.pagination.pages }, (_, i) =>
        `<button class="page-btn ${i + 1 === page ? 'active' : ''}" onclick="loadProjects(${i + 1}, '${search}')">${i + 1}</button>`
      ).join('');
    } else if (pagination) {
      pagination.innerHTML = '';
    }
  } catch (err) {
    grid.innerHTML = renderFallbackProjects();
  }
}

function renderFallbackProjects() {
  const fallback = [
    { icon: '📈', title: 'Stock Dashboard', desc: 'IPO tracking & profit management system with real-time analytics.', tech: 'HTML,CSS,JavaScript,Chart.js' },
    { icon: '🏠', title: 'MeroSutra', desc: 'Smart municipal and property management system.', tech: 'PHP,MySQL,Bootstrap' },
    { icon: '📚', title: 'BCA Notes Hub', desc: 'Student learning platform for academic resources.', tech: 'HTML,CSS,JavaScript' },
    { icon: '🏫', title: 'School Management System', desc: 'Full-stack system connecting admin, teachers, and students.', tech: 'Node.js,Express,MySQL,React' },
    { icon: '🎓', title: 'ERP System', desc: 'School ERP with marks, attendance, and reporting.', tech: 'PHP,MySQL,JavaScript' },
  ];

  return fallback.map(p => `
    <div class="project-card fade-in visible">
      <div class="project-thumb">${p.icon}</div>
      <div class="project-body">
        <div class="project-title">${p.title}</div>
        <div class="project-desc">${p.desc}</div>
        <div class="project-tech">${p.tech.split(',').map(t => `<span>${t.trim()}</span>`).join('')}</div>
      </div>
    </div>`).join('');
}

function getProjectEmoji(title) {
  const map = { stock: '📈', mero: '🏠', bca: '📚', note: '📚', school: '🏫', erp: '🎓', dashboard: '📊' };
  const lower = title.toLowerCase();
  for (const [key, emoji] of Object.entries(map)) {
    if (lower.includes(key)) return emoji;
  }
  return '💻';
}

/* Project Modal */
function openProjectModal(project) {
  const modal = document.getElementById('projectModal');
  if (!modal) return;

  document.getElementById('projectModalTitle').textContent = project.title;
  document.getElementById('projectModalDesc').textContent = project.description;

  const techEl = document.getElementById('projectModalTech');
  techEl.innerHTML = project.tech_stack
    ? project.tech_stack.split(',').map(t => `<span>${escapeHtml(t.trim())}</span>`).join('')
    : '';

  const linksEl = document.getElementById('projectModalLinks');
  linksEl.innerHTML = '';
  if (project.live_url) linksEl.innerHTML += `<a href="${escapeHtml(project.live_url)}" class="btn-primary" target="_blank" style="font-size:0.85rem;padding:0.6rem 1.5rem;">View Live</a>`;
  if (project.github_url) linksEl.innerHTML += `<a href="${escapeHtml(project.github_url)}" class="btn-outline" target="_blank" style="font-size:0.85rem;padding:0.6rem 1.5rem;">GitHub</a>`;

  modal.style.display = 'flex';
}

function closeProjectModal() {
  document.getElementById('projectModal').style.display = 'none';
}

function handleProjectSearch() {
  const search = document.getElementById('projectSearch').value.trim();
  loadProjects(1, search);
}

/* ─── MEMORIES ─── */
async function loadMemories(page = 1) {
  const grid = document.getElementById('memoriesGrid');
  if (!grid) return;

  grid.innerHTML = Array(4).fill('<div class="skeleton" style="aspect-ratio:4/3;border-radius:16px;"></div>').join('');

  try {
    const result = await API.getMemories({ page, limit: 12 });

    if (result.data.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="grid-column: 1/-1;">
          <div class="empty-state-icon">📸</div>
          <p class="empty-state-text">No memories yet. Check back soon!</p>
        </div>`;
      return;
    }

    grid.innerHTML = result.data.map(memory => `
      <div class="memory-card" onclick="openMemoryModal('${escapeHtml(memory.image_url)}', '${escapeHtml(memory.title)}', '${escapeHtml(memory.description || '')}')">
        <img src="${escapeHtml(memory.image_url)}" alt="${escapeHtml(memory.title)}" loading="lazy">
        <div class="memory-overlay">
          <h3>${escapeHtml(memory.title)}</h3>
          ${memory.description ? `<p>${escapeHtml(memory.description)}</p>` : ''}
        </div>
      </div>
    `).join('');
  } catch {
    grid.innerHTML = `
      <div class="empty-state" style="grid-column: 1/-1;">
        <div class="empty-state-icon">📸</div>
        <p class="empty-state-text">Gallery coming soon!</p>
      </div>`;
  }
}

function openMemoryModal(src, title, desc) {
  const modal = document.getElementById('memoryModal');
  if (!modal) return;
  document.getElementById('memoryModalImg').src = src;
  document.getElementById('memoryModalTitle').textContent = title;
  document.getElementById('memoryModalDesc').textContent = desc;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeMemoryModal() {
  document.getElementById('memoryModal').classList.remove('open');
  document.body.style.overflow = '';
}

/* ─── BLOGS ─── */
async function loadBlogs(page = 1, search = '') {
  const grid = document.getElementById('blogGrid');
  if (!grid) return;

  grid.innerHTML = Array(3).fill('<div class="skeleton skeleton-card"></div>').join('');

  try {
    const params = { page, limit: 6 };
    if (search) params.search = search;

    const result = await API.getBlogs(params);

    if (result.data.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="grid-column: 1/-1;">
          <div class="empty-state-icon">📝</div>
          <p class="empty-state-text">No blog posts yet. Stay tuned!</p>
        </div>`;
      return;
    }

    grid.innerHTML = result.data.map(blog => `
      <div class="blog-card" onclick="openBlogPost(${blog.id})">
        <div class="blog-cover">
          ${blog.cover_image
            ? `<img src="${escapeHtml(blog.cover_image)}" alt="${escapeHtml(blog.title)}" loading="lazy">`
            : '<span class="blog-cover-placeholder">📝</span>'}
        </div>
        <div class="blog-body">
          <div class="blog-date">${formatDate(blog.created_at)}</div>
          <div class="blog-title">${escapeHtml(blog.title)}</div>
          <div class="blog-excerpt">${escapeHtml(blog.excerpt || '')}...</div>
          <span class="blog-read-more">Read More &rarr;</span>
        </div>
      </div>
    `).join('');
  } catch {
    grid.innerHTML = `
      <div class="empty-state" style="grid-column: 1/-1;">
        <div class="empty-state-icon">📝</div>
        <p class="empty-state-text">Blog coming soon!</p>
      </div>`;
  }
}

async function openBlogPost(id) {
  const modal = document.getElementById('blogModal');
  if (!modal) return;

  try {
    const blog = await API.getBlog(id);
    document.getElementById('blogModalDate').textContent = formatDate(blog.created_at);
    document.getElementById('blogModalTitle').textContent = blog.title;
    document.getElementById('blogModalBody').innerHTML = formatBlogContent(blog.content);
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  } catch {
    showToast('Failed to load blog post.', 'error');
  }
}

function closeBlogModal() {
  document.getElementById('blogModal').classList.remove('open');
  document.body.style.overflow = '';
}

function handleBlogSearch() {
  const search = document.getElementById('blogSearch').value.trim();
  loadBlogs(1, search);
}

/* ─── UTILITIES ─── */
function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function truncate(str, len) {
  if (!str) return '';
  return str.length > len ? str.substring(0, len) + '...' : str;
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatBlogContent(content) {
  if (!content) return '';
  return content.split('\n').map(p => p.trim() ? `<p>${escapeHtml(p)}</p>` : '').join('');
}
