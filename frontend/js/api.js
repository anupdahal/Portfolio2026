/* ═══════════════════════════════════════════════
   API Communication Layer
   ═══════════════════════════════════════════════ */

const API = (() => {
  // Configure your backend API URL here
  // For PHP backend: point to your PHP server URL
  // Examples: 'http://localhost:8000/api' (PHP built-in server)
  //           'http://localhost/portfolio/backend-php/api' (Apache)
  const BASE_URL = window.API_BASE_URL || 'http://localhost:8000/api';

  function getToken() {
    return localStorage.getItem('portfolio_token');
  }

  function setToken(token) {
    localStorage.setItem('portfolio_token', token);
  }

  function clearToken() {
    localStorage.removeItem('portfolio_token');
  }

  function isAuthenticated() {
    const token = getToken();
    if (!token) return false;
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.exp * 1000 > Date.now();
    } catch {
      return false;
    }
  }

  async function request(endpoint, options = {}) {
    const url = `${BASE_URL}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    const token = getToken();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || `HTTP ${response.status}`);
      }

      return data;
    } catch (err) {
      if (err.name === 'TypeError' && err.message.includes('fetch')) {
        throw new Error('Unable to connect to server. Is the backend running?');
      }
      throw err;
    }
  }

  // Auth
  async function login(username, password) {
    const data = await request('/login', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    });
    if (data.token) {
      setToken(data.token);
    }
    return data;
  }

  function logout() {
    clearToken();
  }

  // Projects
  async function getProjects(params = {}) {
    const query = new URLSearchParams(params).toString();
    return request(`/projects${query ? '?' + query : ''}`);
  }

  async function createProject(project) {
    return request('/projects', {
      method: 'POST',
      body: JSON.stringify(project),
    });
  }

  async function updateProject(id, project) {
    return request(`/projects/${id}`, {
      method: 'PUT',
      body: JSON.stringify(project),
    });
  }

  async function deleteProject(id) {
    return request(`/projects/${id}`, { method: 'DELETE' });
  }

  // Memories
  async function getMemories(params = {}) {
    const query = new URLSearchParams(params).toString();
    return request(`/memories${query ? '?' + query : ''}`);
  }

  async function createMemory(memory) {
    return request('/memories', {
      method: 'POST',
      body: JSON.stringify(memory),
    });
  }

  async function deleteMemory(id) {
    return request(`/memories/${id}`, { method: 'DELETE' });
  }

  // Blogs
  async function getBlogs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return request(`/blogs${query ? '?' + query : ''}`);
  }

  async function getBlog(id) {
    return request(`/blogs/${id}`);
  }

  async function createBlog(blog) {
    return request('/blogs', {
      method: 'POST',
      body: JSON.stringify(blog),
    });
  }

  async function updateBlog(id, blog) {
    return request(`/blogs/${id}`, {
      method: 'PUT',
      body: JSON.stringify(blog),
    });
  }

  async function deleteBlog(id) {
    return request(`/blogs/${id}`, { method: 'DELETE' });
  }

  // Health
  async function healthCheck() {
    return request('/health');
  }

  return {
    login, logout, isAuthenticated, getToken,
    getProjects, createProject, updateProject, deleteProject,
    getMemories, createMemory, deleteMemory,
    getBlogs, getBlog, createBlog, updateBlog, deleteBlog,
    healthCheck,
  };
})();
