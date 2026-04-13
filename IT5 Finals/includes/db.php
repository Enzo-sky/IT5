<?php
define('DB_FILE', __DIR__ . '/../database/voting_system.db');

function getDB() {
    if (!file_exists(dirname(DB_FILE))) {
        mkdir(dirname(DB_FILE), 0755, true);
    }
    
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create tables if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS elections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            status TEXT DEFAULT 'draft',
            voting_method TEXT DEFAULT 'plurality',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS positions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            election_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            max_selections INTEGER DEFAULT 1,
            FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS candidates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            position_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            party TEXT,
            photo_url TEXT,
            bio TEXT,
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS voters (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            voter_id TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            dob DATE,
            status TEXT DEFAULT 'unverified',
            has_voted INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS votes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            election_id INTEGER NOT NULL,
            voter_id INTEGER NOT NULL,
            position_id INTEGER NOT NULL,
            candidate_id INTEGER NOT NULL,
            rank INTEGER DEFAULT 1,
            voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (election_id) REFERENCES elections(id),
            FOREIGN KEY (voter_id) REFERENCES voters(id),
            FOREIGN KEY (position_id) REFERENCES positions(id),
            FOREIGN KEY (candidate_id) REFERENCES candidates(id)
        );
        
        INSERT OR IGNORE INTO users (id, username, password, role) VALUES (1, 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin');
        INSERT OR IGNORE INTO users (id, username, password, role) VALUES (2, 'student', '" . password_hash('student123', PASSWORD_DEFAULT) . "', 'student');

    ");
    
    return $db;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}