CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT         NOT NULL,
    role          VARCHAR(20)  NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- role: 'user' | 'admin'

CREATE TABLE IF NOT EXISTS devices (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS components (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    device_id        INT          NOT NULL,
    name             VARCHAR(100) NOT NULL,
    description      TEXT         NOT NULL,
    purpose          TEXT         NOT NULL,
    difficulty_level INT          NOT NULL DEFAULT 1,
    image_path       TEXT,
    slot_position    VARCHAR(50)  DEFAULT NULL,
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- difficulty_level: 1=basic, 2=intermediate, 3=advanced
-- slot_position: drag-and-drop target id, e.g. 'cpu-socket', 'ram-slot'

CREATE TABLE IF NOT EXISTS levels (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    device_id          INT          NOT NULL,
    level_number       INT          NOT NULL,
    title              VARCHAR(150) NOT NULL,
    description        TEXT,
    game_type          ENUM('learn','drag_drop','matching','quiz') NOT NULL DEFAULT 'quiz',
    min_score_required INT          NOT NULL DEFAULT 70,
    UNIQUE KEY uq_device_level (device_id, level_number),
    FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- game_type: 'learn'=info page, 'drag_drop'=place on diagram, 'matching'=think&match, 'quiz'=final test

CREATE TABLE IF NOT EXISTS questions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    level_id      INT         NOT NULL,
    component_id  INT         DEFAULT NULL,
    question_text TEXT        NOT NULL,
    question_type VARCHAR(30) NOT NULL DEFAULT 'single_choice',
    difficulty    INT         NOT NULL DEFAULT 1,
    FOREIGN KEY (level_id)     REFERENCES levels(id)     ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- question_type: 'single_choice' | 'true_false' | 'matching' | 'drag_drop' | 'scenario'

CREATE TABLE IF NOT EXISTS answers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT     NOT NULL,
    answer_text TEXT    NOT NULL,
    is_correct  TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_sessions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT       NOT NULL,
    level_id         INT       NOT NULL,
    score            INT       NOT NULL,
    total_questions  INT       NOT NULL,
    correct_answers  INT       NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_progress (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT        NOT NULL,
    level_id   INT        NOT NULL,
    best_score INT        NOT NULL DEFAULT 0,
    completed  TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_level (user_id, level_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS learned_components (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT       NOT NULL,
    component_id INT       NOT NULL,
    learned_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_component (user_id, component_id),
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES components(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
