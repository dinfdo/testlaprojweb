-- Seed data for LEG Project
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', 'adminpass', 'admin'),
('User', 'user@example.com', 'userpass', 'user');

INSERT INTO leaderboard (user_id, score) VALUES
(1, 1200),
(2, 900);
