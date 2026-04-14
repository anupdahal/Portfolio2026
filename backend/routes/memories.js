const express = require('express');
const pool = require('../config/db');
const authenticateToken = require('../middleware/auth');

const router = express.Router();

// GET /api/memories - Public
router.get('/', async (req, res) => {
  try {
    const { page = 1, limit = 12 } = req.query;

    const [countResult] = await pool.query('SELECT COUNT(*) as total FROM memories');
    const total = countResult[0].total;

    const offset = (parseInt(page) - 1) * parseInt(limit);
    const [rows] = await pool.query(
      'SELECT * FROM memories ORDER BY created_at DESC LIMIT ? OFFSET ?',
      [parseInt(limit), offset]
    );

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
    console.error('Get memories error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// POST /api/memories - Protected
router.post('/', authenticateToken, async (req, res) => {
  try {
    const { title, description, image_url } = req.body;

    if (!title || !image_url) {
      return res.status(400).json({ error: 'Title and image URL are required.' });
    }

    const [result] = await pool.query(
      'INSERT INTO memories (title, description, image_url) VALUES (?, ?, ?)',
      [title, description || null, image_url]
    );

    const [newMemory] = await pool.query('SELECT * FROM memories WHERE id = ?', [result.insertId]);
    res.status(201).json(newMemory[0]);
  } catch (err) {
    console.error('Create memory error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

// DELETE /api/memories/:id - Protected
router.delete('/:id', authenticateToken, async (req, res) => {
  try {
    const { id } = req.params;

    const [existing] = await pool.query('SELECT * FROM memories WHERE id = ?', [id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Memory not found.' });
    }

    await pool.query('DELETE FROM memories WHERE id = ?', [id]);
    res.json({ message: 'Memory deleted successfully.' });
  } catch (err) {
    console.error('Delete memory error:', err);
    res.status(500).json({ error: 'Server error.' });
  }
});

module.exports = router;
