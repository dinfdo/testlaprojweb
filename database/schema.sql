PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- role poate fi:
-- 'user'
-- 'admin'


CREATE TABLE IF NOT EXISTS devices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS components (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    purpose TEXT NOT NULL,
    difficulty_level INTEGER NOT NULL DEFAULT 1,
    image_path TEXT,

    FOREIGN KEY (device_id) REFERENCES devices(id)
        ON DELETE CASCADE
);

-- difficulty_level:
-- 1 = basic
-- 2 = intermediate
-- 3 = advanced

CREATE TABLE IF NOT EXISTS levels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    device_id INTEGER NOT NULL,
    level_number INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    min_score_required INTEGER NOT NULL DEFAULT 70,

    FOREIGN KEY (device_id) REFERENCES devices(id)
        ON DELETE CASCADE,

    UNIQUE(device_id, level_number)
);

CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level_id INTEGER NOT NULL,
    component_id INTEGER,
    question_text TEXT NOT NULL,
    question_type TEXT NOT NULL DEFAULT 'single_choice',
    difficulty INTEGER NOT NULL DEFAULT 1,

    FOREIGN KEY (level_id) REFERENCES levels(id)
        ON DELETE CASCADE,

    FOREIGN KEY (component_id) REFERENCES components(id)
        ON DELETE SET NULL
);

-- question_type poate fi:
-- 'single_choice'
-- 'true_false'
-- 'matching'
-- 'scenario'



CREATE TABLE IF NOT EXISTS answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question_id INTEGER NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct INTEGER NOT NULL DEFAULT 0,

    FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE
);

-- is_correct:
-- 0 = fals
-- 1 = corect


CREATE TABLE IF NOT EXISTS quiz_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    level_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    total_questions INTEGER NOT NULL,
    correct_answers INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (level_id) REFERENCES levels(id)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS user_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    level_id INTEGER NOT NULL,
    best_score INTEGER NOT NULL DEFAULT 0,
    completed INTEGER NOT NULL DEFAULT 0,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (level_id) REFERENCES levels(id)
        ON DELETE CASCADE,

    UNIQUE(user_id, level_id)
);

-- completed:
-- 0 = nivel nefinalizat
-- 1 = nivel finalizat



CREATE TABLE IF NOT EXISTS learned_components (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    component_id INTEGER NOT NULL,
    learned_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (component_id) REFERENCES components(id)
        ON DELETE CASCADE,

    UNIQUE(user_id, component_id)
);