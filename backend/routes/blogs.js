const express = require('express');
const pool = require('../config/db');
const authenticateToken = require('../middleware/auth');

const router = express.Router();

// GET /api/blogs - Public
router.get('/', async (req, res) => {
  try {
    const { search, page = 1, limit = 6 } = req.query;
    let query = 'SELECT id, title, LEFT(content, 200) as excerpt, cover_image, created_at FROM blogs';
    const params = [];

    if (search) {
      query += ' WHERE title LIKE ? OR content LIKE ?';
      params.push(`%${search}%`, `%${search}%`);
    }

    // Count
    const countQuery = query.replace(/SELECT .* FROM/, 'SELECT COUNT(*) as total FROM');
    const [countResult] = await pool.query(countQuery, params);
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
    console.error('Get blogs error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// GET /api/blogs/:id - Public
router.get('/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const [rows] = await pool.query('SELECT * FROM blogs WHERE id = ?', [id]);

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Blog post not found.' });
    }

    res.json(rows[0]);
  } catch (err) {
    console.error('Get blog error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// POST /api/blogs - Protected
router.post('/', authenticateToken, async (req, res) => {
  try {
    const { title, content, cover_image } = req.body;

    if (!title || !content) {
      return res.status(400).json({ error: 'Title and content are required.' });
    }

    const [result] = await pool.query(
      'INSERT INTO blogs (title, content, cover_image) VALUES (?, ?, ?)',
      [title, content, cover_image || null]
    );

    const [newBlog] = await pool.query('SELECT * FROM blogs WHERE id = ?', [result.insertId]);
    res.status(201).json(newBlog[0]);
  } catch (err) {
    console.error('Create blog error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// PUT /api/blogs/:id - Protected
router.put('/:id', authenticateToken, async (req, res) => {
  try {
    const { id } = req.params;
    const { title, content, cover_image } = req.body;

    const [existing] = await pool.query('SELECT * FROM blogs WHERE id = ?', [id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Blog post not found.' });
    }

    await pool.query(
      'UPDATE blogs SET title = ?, content = ?, cover_image = ? WHERE id = ?',
      [
        title || existing[0].title,
        content || existing[0].content,
        cover_image !== undefined ? cover_image : existing[0].cover_image,
        id,
      ]
    );

    const [updated] = await pool.query('SELECT * FROM blogs WHERE id = ?', [id]);
    res.json(updated[0]);
  } catch (err) {
    console.error('Update blog error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// DELETE /api/blogs/:id - Protected
router.delete('/:id', authenticateToken, async (req, res) => {
  try {
    const { id } = req.params;

    const [existing] = await pool.query('SELECT * FROM blogs WHERE id = ?', [id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Blog post not found.' });
    }

    await pool.query('DELETE FROM blogs WHERE id = ?', [id]);
    res.json({ message: 'Blog post deleted successfully.' });
  } catch (err) {
    console.error('Delete blog error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

module.exports = router;
