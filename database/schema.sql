CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT         NOT NULL,
    role          VARCHAR(20)  NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
-- role: 'user' | 'admin'

CREATE TABLE IF NOT EXISTS devices (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS components (
    id               SERIAL PRIMARY KEY,
    device_id        INTEGER      NOT NULL REFERENCES devices(id) ON DELETE CASCADE,
    name             VARCHAR(100) NOT NULL,
    description      TEXT         NOT NULL,
    purpose          TEXT         NOT NULL,
    difficulty_level INTEGER      NOT NULL DEFAULT 1,
    image_path       TEXT
);
-- difficulty_level: 1=basic, 2=intermediate, 3=advanced

CREATE TABLE IF NOT EXISTS levels (
    id                 SERIAL PRIMARY KEY,
    device_id          INTEGER      NOT NULL REFERENCES devices(id) ON DELETE CASCADE,
    level_number       INTEGER      NOT NULL,
    title              VARCHAR(150) NOT NULL,
    description        TEXT,
    min_score_required INTEGER      NOT NULL DEFAULT 70,
    UNIQUE (device_id, level_number)
);

CREATE TABLE IF NOT EXISTS questions (
    id            SERIAL PRIMARY KEY,
    level_id      INTEGER     NOT NULL REFERENCES levels(id)     ON DELETE CASCADE,
    component_id  INTEGER              REFERENCES components(id) ON DELETE SET NULL,
    question_text TEXT        NOT NULL,
    question_type VARCHAR(30) NOT NULL DEFAULT 'single_choice',
    difficulty    INTEGER     NOT NULL DEFAULT 1
);
-- question_type: 'single_choice' | 'true_false' | 'matching' | 'scenario'

CREATE TABLE IF NOT EXISTS answers (
    id          SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    answer_text TEXT    NOT NULL,
    is_correct  BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS quiz_sessions (
    id               SERIAL PRIMARY KEY,
    user_id          INTEGER   NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    level_id         INTEGER   NOT NULL REFERENCES levels(id) ON DELETE CASCADE,
    score            INTEGER   NOT NULL,
    total_questions  INTEGER   NOT NULL,
    correct_answers  INTEGER   NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_progress (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER   NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    level_id   INTEGER   NOT NULL REFERENCES levels(id) ON DELETE CASCADE,
    best_score INTEGER   NOT NULL DEFAULT 0,
    completed  BOOLEAN   NOT NULL DEFAULT FALSE,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, level_id)
);

CREATE TABLE IF NOT EXISTS learned_components (
    id           SERIAL PRIMARY KEY,
    user_id      INTEGER   NOT NULL REFERENCES users(id)      ON DELETE CASCADE,
    component_id INTEGER   NOT NULL REFERENCES components(id) ON DELETE CASCADE,
    learned_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, component_id)
);
