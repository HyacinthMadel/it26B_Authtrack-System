-- ============================================================
-- AuthTrack Monitoring — Database Schema
-- FOLDER NAME: pangit  (place inside htdocs/pangit/)
-- ============================================================
-- HOW TO USE:
--   1. Open phpMyAdmin
--   2. Create database: authtrack_monitoring
--   3. Click the database → Import tab → choose this file → Go
--   4. Visit http://localhost/pangit/setup.php
--   5. Then DELETE setup.php for security!
-- ============================================================

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- ── TABLE: roles ─────────────────────────────────────────────
CREATE TABLE roles (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50)  NOT NULL UNIQUE,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TABLE: users ─────────────────────────────────────────────
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role_id    INT          NOT NULL DEFAULT 3,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id)
        REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_users_email   (email),
    INDEX idx_users_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TABLE: activity_logs ─────────────────────────────────────
CREATE TABLE activity_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NULL,
    action     VARCHAR(100) NOT NULL,
    details    TEXT         NULL,
    ip_address VARCHAR(45)  NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_logs_user_id (user_id),
    INDEX idx_logs_action  (action),
    INDEX idx_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SEED: Roles ───────────────────────────────────────────────
INSERT INTO roles (name) VALUES
    ('admin'),
    ('editor'),
    ('viewer');

-- ── SEED: Sample Users (passwords fixed by setup.php) ────────
INSERT INTO users (name, email, password, role_id) VALUES
('System Admin',  'admin@authtrack.com',  '$2y$12$placeholder.hash.will.be.fixed.by.setup.php.run.it', 1),
('Jane Editor',   'editor@authtrack.com', '$2y$12$placeholder.hash.will.be.fixed.by.setup.php.run.it', 2),
('Bob Viewer',    'viewer@authtrack.com', '$2y$12$placeholder.hash.will.be.fixed.by.setup.php.run.it', 3);

-- ── SEED: Sample Activity Logs ────────────────────────────────
INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES
(1, 'register',    'Account created (admin)',               '127.0.0.1'),
(2, 'register',    'Account created (editor)',              '127.0.0.1'),
(3, 'register',    'Account created (viewer)',              '127.0.0.1'),
(1, 'login',       'Admin logged in',                       '127.0.0.1'),
(2, 'login',       'Editor logged in',                      '127.0.0.1'),
(1, 'create_user', 'Created user: editor@authtrack.com',    '127.0.0.1'),
(1, 'logout',      'Admin logged out',                      '127.0.0.1');

-- ── VIEWS ─────────────────────────────────────────────────────
CREATE OR REPLACE VIEW view_users_with_logs AS
SELECT
    u.id        AS user_id,
    u.name      AS user_name,
    u.email,
    r.name      AS role,
    COUNT(l.id) AS log_count
FROM users u
INNER JOIN roles r         ON u.role_id = r.id
INNER JOIN activity_logs l ON l.user_id = u.id
GROUP BY u.id, u.name, u.email, r.name;

CREATE OR REPLACE VIEW view_all_users_latest_log AS
SELECT
    u.id              AS user_id,
    u.name            AS user_name,
    u.email,
    r.name            AS role,
    MAX(l.created_at) AS last_activity
FROM users u
LEFT JOIN roles r         ON u.role_id = r.id
LEFT JOIN activity_logs l ON l.user_id = u.id
GROUP BY u.id, u.name, u.email, r.name;
