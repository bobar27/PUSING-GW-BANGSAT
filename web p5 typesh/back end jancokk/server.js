// server.js
// Minimal Express backend for the sign in / sign up page.
// Handles: POST /api/signup, POST /api/login, POST /api/logout, GET /api/me

const express = require('express');
const session = require('express-session');
const bcrypt = require('bcryptjs');
const cors = require('cors');
const path = require('path');
const db = require('./db');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors({ origin: true, credentials: true }));
app.use(express.json());

// Sessions are stored in memory here for simplicity.
// Fine for local/dev use; for production swap in a persistent
// session store (e.g. connect-sqlite3, connect-redis, etc).
app.use(session({
  secret: process.env.SESSION_SECRET || 'change-this-secret-in-production',
  resave: false,
  saveUninitialized: false,
  cookie: {
    httpOnly: true,
    maxAge: 1000 * 60 * 60 * 24 // 1 day
  }
}));

// Serve the static frontend (index.html, auth.html, style.css) from /public
app.use(express.static(path.join(__dirname, 'public')));

// ---------- Helpers ----------

function isValidEmail(email) {
  return typeof email === 'string' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ---------- Routes ----------

// Create a new account
app.post('/api/signup', (req, res) => {
  const { name, email, password } = req.body || {};

  if (!name || !name.trim()) {
    return res.status(400).json({ error: 'Name is required.' });
  }
  if (!isValidEmail(email)) {
    return res.status(400).json({ error: 'A valid email is required.' });
  }
  if (!password || password.length < 8) {
    return res.status(400).json({ error: 'Password must be at least 8 characters.' });
  }

  const existing = db.prepare('SELECT id FROM users WHERE email = ?').get(email.toLowerCase());
  if (existing) {
    return res.status(409).json({ error: 'An account with that email already exists.' });
  }

  const passwordHash = bcrypt.hashSync(password, 10);

  const result = db.prepare(
    'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
  ).run(name.trim(), email.toLowerCase(), passwordHash);

  req.session.userId = result.lastInsertRowid;

  return res.status(201).json({
    user: { id: result.lastInsertRowid, name: name.trim(), email: email.toLowerCase() }
  });
});

// Log in to an existing account
app.post('/api/login', (req, res) => {
  const { email, password } = req.body || {};

  if (!isValidEmail(email) || !password) {
    return res.status(400).json({ error: 'Email and password are required.' });
  }

  const user = db.prepare('SELECT * FROM users WHERE email = ?').get(email.toLowerCase());
  if (!user) {
    return res.status(401).json({ error: 'Invalid email or password.' });
  }

  const passwordMatches = bcrypt.compareSync(password, user.password_hash);
  if (!passwordMatches) {
    return res.status(401).json({ error: 'Invalid email or password.' });
  }

  req.session.userId = user.id;

  return res.json({
    user: { id: user.id, name: user.name, email: user.email }
  });
});

// Log out
app.post('/api/logout', (req, res) => {
  req.session.destroy(() => {
    res.clearCookie('connect.sid');
    res.json({ ok: true });
  });
});

// Get the currently logged-in user (if any)
app.get('/api/me', (req, res) => {
  if (!req.session.userId) {
    return res.status(401).json({ error: 'Not logged in.' });
  }

  const user = db.prepare('SELECT id, name, email FROM users WHERE id = ?').get(req.session.userId);
  if (!user) {
    return res.status(401).json({ error: 'Not logged in.' });
  }

  res.json({ user });
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});