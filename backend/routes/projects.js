const express = require('express');
const pool = require('../config/db');
const authenticateToken = require('../middleware/auth');

const router = express.Router();

// GET /api/projects - Public
router.get('/', async (req, res) => {
  try {
    const { tech, search, page = 1, limit = 10 } = req.query;
    let query = 'SELECT * FROM projects';
    const params = [];
    const conditions = [];

    if (search) {
      conditions.push('(title LIKE ? OR description LIKE ?)');
      params.push(`%${search}%`, `%${search}%`);
    }

    if (tech) {
      conditions.push('tech_stack LIKE ?');
      params.push(`%${tech}%`);
    }

    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }

    // Get total count
    const [countResult] = await pool.query(
      query.replace('SELECT *', 'SELECT COUNT(*) as total'),
      params
    );
    const total = countResult[0].total;

    query += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    const offset = (parseInt(page) - 1) * parseInt(limit);
    params.push(parseInt(limit), offset);

    const [rows] = await pool.query(query, params);

    res.json({
      data: rows,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total,
        pages: Math.ceil(total / parseInt(limit)),
      },
    });
  } catch (err) {
    console.error('Get projects error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// POST /api/projects - Protected
router.post('/', authenticateToken, async (req, res) => {
  try {
    const { title, description, image_url, tech_stack, live_url, github_url } = req.body;

    if (!title || !description) {
      return res.status(400).json({ error: 'Title and description are required.' });
    }

    const [result] = await pool.query(
      'INSERT INTO projects (title, description, image_url, tech_stack, live_url, github_url) VALUES (?, ?, ?, ?, ?, ?)',
      [title, description, image_url || null, tech_stack || null, live_url || null, github_url || null]
    );

    const [newProject] = await pool.query('SELECT * FROM projects WHERE id = ?', [result.insertId]);
    res.status(201).json(newProject[0]);
  } catch (err) {
    console.error('Create project error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// PUT /api/projects/:id - Protected
router.put('/:id', authenticateToken, async (req, res) => {
  try {
    const { id } = req.params;
    const { title, description, image_url, tech_stack, live_url, github_url } = req.body;

    const [existing] = await pool.query('SELECT * FROM projects WHERE id = ?', [id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Project not found.' });
    }

    await pool.query(
      'UPDATE projects SET title = ?, description = ?, image_url = ?, tech_stack = ?, live_url = ?, github_url = ? WHERE id = ?',
      [
        title || existing[0].title,
        description || existing[0].description,
        image_url !== undefined ? image_url : existing[0].image_url,
        tech_stack !== undefined ? tech_stack : existing[0].tech_stack,
        live_url !== undefined ? live_url : existing[0].live_url,
        github_url !== undefined ? github_url : existing[0].github_url,
        id,
      ]
    );

    const [updated] = await pool.query('SELECT * FROM projects WHERE id = ?', [id]);
    res.json(updated[0]);
  } catch (err) {
    console.error('Update project error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// DELETE /api/projects/:id - Protected
router.delete('/:id', authenticateToken, async (req, res) => {
  try {
    const { id } = req.params;

    const [existing] = await pool.query('SELECT * FROM projects WHERE id = ?', [id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Project not found.' });
    }

    await pool.query('DELETE FROM projects WHERE id = ?', [id]);
    res.json({ message: 'Project deleted successfully.' });
  } catch (err) {
    console.error('Delete project error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

module.exports = router;
