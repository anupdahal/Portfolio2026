/* ═══════════════════════════════════════════════
   Admin Panel — Application
   ═══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  checkAuth();
  initSidebar();
});

/* ─── AUTH ─── */
function checkAuth() {
  if (API.isAuthenticated()) {
    showDashboard();
  } else {
    showLogin();
  }
}

function showLogin() {
  document.getElementById('loginView').style.display = 'flex';
  document.getElementById('dashboardView').style.display = 'none';
}

function showDashboard() {
  document.getElementById('loginView').style.display = 'none';
  document.getElementById('dashboardView').style.display = 'grid';
  loadDashboard();
}

async function handleLogin(e) {
  e.preventDefault();
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;
  const errorEl = document.getElementById('loginError');

  if (!username || !password) {
    errorEl.textContent = 'Please fill in all fields.';
    errorEl.style.display = 'block';
    return;
  }

  try {
    await API.login(username, password);
    errorEl.style.display = 'none';
    showDashboard();
    showToast('Welcome back!', 'success');
  } catch (err) {
    errorEl.textContent = err.message || 'Login failed.';
    errorEl.style.display = 'block';
  }
}

function handleLogout() {
  API.logout();
  showLogin();
  showToast('Logged out.', 'info');
}

/* ─── TOAST ─── */
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

/* ─── SIDEBAR NAV ─── */
function initSidebar() {
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const panel = link.getAttribute('data-panel');
      switchPanel(panel);

      // Close mobile sidebar
      document.querySelector('.admin-sidebar').classList.remove('open');
    });
  });
}

function switchPanel(panelName) {
  // Update nav
  document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
  document.querySelector(`.sidebar-nav a[data-panel="${panelName}"]`).classList.add('active');

  // Show panel
  document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`panel-${panelName}`).classList.add('active');

  // Load data
  switch (panelName) {
    case 'dashboard': loadDashboard(); break;
    case 'projects': loadAdminProjects(); break;
    case 'memories': loadAdminMemories(); break;
    case 'blogs': loadAdminBlogs(); break;
  }
}

function toggleMobileSidebar() {
  document.querySelector('.admin-sidebar').classList.toggle('open');
}

/* ─── DASHBOARD ─── */
async function loadDashboard() {
  try {
    const [projects, memories, blogs] = await Promise.all([
      API.getProjects({ limit: 1 }),
      API.getMemories({ limit: 1 }),
      API.getBlogs({ limit: 1 }),
    ]);

    document.getElementById('statProjects').textContent = projects.pagination.total;
    document.getElementById('statMemories').textContent = memories.pagination.total;
    document.getElementById('statBlogs').textContent = blogs.pagination.total;
  } catch {
    document.getElementById('statProjects').textContent = '-';
    document.getElementById('statMemories').textContent = '-';
    document.getElementById('statBlogs').textContent = '-';
  }
}

/* ─── PROJECTS CRUD ─── */
let editingProjectId = null;

async function loadAdminProjects() {
  const tbody = document.getElementById('projectsTableBody');
  tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--pine);">Loading...</td></tr>';

  try {
    const result = await API.getProjects({ limit: 50 });
    if (result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="admin-empty">No projects yet.</td></tr>';
      return;
    }
    tbody.innerHTML = result.data.map(p => `
      <tr>
        <td>${escapeHtml(p.title)}</td>
        <td>${escapeHtml(truncate(p.description, 60))}</td>
        <td>${escapeHtml(p.tech_stack || '-')}</td>
        <td>${formatDate(p.created_at)}</td>
        <td>
          <button class="action-btn edit" onclick='editProject(${JSON.stringify(p)})'>Edit</button>
          <button class="action-btn delete" onclick="deleteProject(${p.id})">Delete</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="5" class="admin-empty">Failed to load: ${escapeHtml(err.message)}</td></tr>`;
  }
}

function openProjectForm(project = null) {
  editingProjectId = project ? project.id : null;
  document.getElementById('projectFormTitle').textContent = project ? 'Edit Project' : 'Add Project';
  document.getElementById('projTitle').value = project ? project.title : '';
  document.getElementById('projDesc').value = project ? project.description : '';
  document.getElementById('projImage').value = project ? (project.image_url || '') : '';
  document.getElementById('projTech').value = project ? (project.tech_stack || '') : '';
  document.getElementById('projLive').value = project ? (project.live_url || '') : '';
  document.getElementById('projGithub').value = project ? (project.github_url || '') : '';
  document.getElementById('projectFormModal').classList.add('open');
}

function closeProjectForm() {
  document.getElementById('projectFormModal').classList.remove('open');
  editingProjectId = null;
}

function editProject(project) {
  openProjectForm(project);
}

async function saveProject() {
  const data = {
    title: document.getElementById('projTitle').value.trim(),
    description: document.getElementById('projDesc').value.trim(),
    image_url: document.getElementById('projImage').value.trim() || null,
    tech_stack: document.getElementById('projTech').value.trim() || null,
    live_url: document.getElementById('projLive').value.trim() || null,
    github_url: document.getElementById('projGithub').value.trim() || null,
  };

  if (!data.title || !data.description) {
    showToast('Title and description are required.', 'error');
    return;
  }

  try {
    if (editingProjectId) {
      await API.updateProject(editingProjectId, data);
      showToast('Project updated!', 'success');
    } else {
      await API.createProject(data);
      showToast('Project created!', 'success');
    }
    closeProjectForm();
    loadAdminProjects();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteProject(id) {
  if (!confirm('Are you sure you want to delete this project?')) return;
  try {
    await API.deleteProject(id);
    showToast('Project deleted.', 'success');
    loadAdminProjects();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

/* ─── MEMORIES CRUD ─── */
async function loadAdminMemories() {
  const tbody = document.getElementById('memoriesTableBody');
  tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--pine);">Loading...</td></tr>';

  try {
    const result = await API.getMemories({ limit: 50 });
    if (result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="admin-empty">No memories yet.</td></tr>';
      return;
    }
    tbody.innerHTML = result.data.map(m => `
      <tr>
        <td>${escapeHtml(m.title)}</td>
        <td>${escapeHtml(truncate(m.description || '', 40))}</td>
        <td>${formatDate(m.created_at)}</td>
        <td>
          <button class="action-btn delete" onclick="deleteMemory(${m.id})">Delete</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4" class="admin-empty">Failed to load: ${escapeHtml(err.message)}</td></tr>`;
  }
}

function openMemoryForm() {
  document.getElementById('memTitle').value = '';
  document.getElementById('memDesc').value = '';
  document.getElementById('memImage').value = '';
  document.getElementById('memoryFormModal').classList.add('open');
}

function closeMemoryForm() {
  document.getElementById('memoryFormModal').classList.remove('open');
}

async function saveMemory() {
  const data = {
    title: document.getElementById('memTitle').value.trim(),
    description: document.getElementById('memDesc').value.trim() || null,
    image_url: document.getElementById('memImage').value.trim(),
  };

  if (!data.title || !data.image_url) {
    showToast('Title and image URL are required.', 'error');
    return;
  }

  try {
    await API.createMemory(data);
    showToast('Memory added!', 'success');
    closeMemoryForm();
    loadAdminMemories();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteMemory(id) {
  if (!confirm('Are you sure you want to delete this memory?')) return;
  try {
    await API.deleteMemory(id);
    showToast('Memory deleted.', 'success');
    loadAdminMemories();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

/* ─── BLOGS CRUD ─── */
let editingBlogId = null;

async function loadAdminBlogs() {
  const tbody = document.getElementById('blogsTableBody');
  tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--pine);">Loading...</td></tr>';

  try {
    const result = await API.getBlogs({ limit: 50 });
    if (result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="admin-empty">No blog posts yet.</td></tr>';
      return;
    }
    tbody.innerHTML = result.data.map(b => `
      <tr>
        <td>${escapeHtml(b.title)}</td>
        <td>${escapeHtml(truncate(b.excerpt || '', 50))}</td>
        <td>${formatDate(b.created_at)}</td>
        <td>
          <button class="action-btn edit" onclick="editBlog(${b.id})">Edit</button>
          <button class="action-btn delete" onclick="deleteBlog(${b.id})">Delete</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4" class="admin-empty">Failed to load: ${escapeHtml(err.message)}</td></tr>`;
  }
}

function openBlogForm(blog = null) {
  editingBlogId = blog ? blog.id : null;
  document.getElementById('blogFormTitle').textContent = blog ? 'Edit Blog Post' : 'New Blog Post';
  document.getElementById('blogTitle').value = blog ? blog.title : '';
  document.getElementById('blogContent').value = blog ? blog.content : '';
  document.getElementById('blogCover').value = blog ? (blog.cover_image || '') : '';
  document.getElementById('blogFormModal').classList.add('open');
}

function closeBlogForm() {
  document.getElementById('blogFormModal').classList.remove('open');
  editingBlogId = null;
}

async function editBlog(id) {
  try {
    const blog = await API.getBlog(id);
    openBlogForm(blog);
  } catch (err) {
    showToast('Failed to load blog.', 'error');
  }
}

async function saveBlog() {
  const data = {
    title: document.getElementById('blogTitle').value.trim(),
    content: document.getElementById('blogContent').value.trim(),
    cover_image: document.getElementById('blogCover').value.trim() || null,
  };

  if (!data.title || !data.content) {
    showToast('Title and content are required.', 'error');
    return;
  }

  try {
    if (editingBlogId) {
      await API.updateBlog(editingBlogId, data);
      showToast('Blog post updated!', 'success');
    } else {
      await API.createBlog(data);
      showToast('Blog post created!', 'success');
    }
    closeBlogForm();
    loadAdminBlogs();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

async function deleteBlog(id) {
  if (!confirm('Are you sure you want to delete this blog post?')) return;
  try {
    await API.deleteBlog(id);
    showToast('Blog post deleted.', 'success');
    loadAdminBlogs();
    loadDashboard();
  } catch (err) {
    showToast(err.message, 'error');
  }
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
