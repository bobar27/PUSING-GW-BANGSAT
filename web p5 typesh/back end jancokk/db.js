// db.js
// Sets up a local SQLite database file (app.db) and the users table.
// SQLite is used here because it needs zero external setup (no server,
// no credentials) — perfect for a simple project like this one.

const Database = require('better-sqlite3');
const path = require('path');

const db = new Database(path.join(__dirname, 'app.db'));

// Create the users table if it doesn't exist yet.
db.exec(`
  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
  )
`);

module.exports = db;