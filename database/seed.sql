-- Seed data for LeG project (PostgreSQL)

INSERT INTO devices (name, description) VALUES
(
    'Computer',
    'A computer is an electronic device made of several hardware components that work together to process, store and display information.'
);

INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path) VALUES
(1, 'CPU',           'The Central Processing Unit is the main processor of the computer.',                   'Executes instructions and performs calculations.',              1, NULL),
(1, 'RAM',           'Random Access Memory is temporary memory used while programs are running.',            'Stores data temporarily for quick access.',                    1, NULL),
(1, 'SSD',           'A Solid State Drive is a storage device that keeps data even when turned off.',        'Stores files, programs and the operating system permanently.',  1, NULL),
(1, 'Motherboard',   'The motherboard is the main circuit board of the computer.',                           'Connects and allows communication between the main components.', 2, NULL),
(1, 'GPU',           'The Graphics Processing Unit processes images and graphics.',                          'Helps render images, videos and games.',                        2, NULL),
(1, 'Power Supply',  'The power supply converts electricity from the wall into usable power.',               'Provides electrical power to all components.',                  2, NULL),
(1, 'Monitor',       'The monitor is an output device that displays visual information.',                    'Shows text, images, videos and the user interface.',            1, NULL),
(1, 'Keyboard',      'The keyboard is an input device used to type text and commands.',                      'Allows the user to enter information into the computer.',       1, NULL),
(1, 'Mouse',         'The mouse is an input device used to control the pointer on the screen.',              'Allows the user to click, select and interact with elements.',   1, NULL),
(1, 'Cooling System','The cooling system keeps components from overheating.',                                'Removes heat from components such as the CPU and GPU.',         3, NULL);

INSERT INTO levels (device_id, level_number, title, description, min_score_required) VALUES
(1, 1, 'Basic Components',        'Learn the most common computer components and their basic roles.', 70),
(1, 2, 'Internal Components',     'Learn how internal computer parts work together.',                70),
(1, 3, 'Troubleshooting Scenarios','Use your knowledge to identify components in practical situations.', 70);

-- Level 1 questions
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(1, 1, 'Which component executes instructions and performs calculations?',        'single_choice', 1),
(1, 2, 'Which component stores data temporarily while programs are running?',     'single_choice', 1),
(1, 3, 'Which component stores files permanently?',                               'single_choice', 1);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1, 'CPU',    TRUE),  (1, 'RAM',          FALSE), (1, 'Monitor',  FALSE), (1, 'Keyboard',     FALSE),
(2, 'SSD',    FALSE), (2, 'RAM',          TRUE),  (2, 'Power Supply', FALSE), (2, 'Mouse',    FALSE),
(3, 'SSD',    TRUE),  (3, 'RAM',          FALSE), (3, 'CPU',      FALSE), (3, 'Keyboard',     FALSE);

-- Level 2 questions
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(2, 4, 'Which component connects the main parts of the computer?',            'single_choice', 2),
(2, 5, 'Which component is mainly responsible for graphics processing?',      'single_choice', 2),
(2, 6, 'Which component provides electrical power to the computer parts?',    'single_choice', 2);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(4, 'Motherboard', TRUE),  (4, 'Monitor', FALSE), (4, 'Mouse',         FALSE), (4, 'SSD',          FALSE),
(5, 'GPU',         TRUE),  (5, 'Power Supply', FALSE), (5, 'Keyboard', FALSE), (5, 'RAM',          FALSE),
(6, 'Power Supply',TRUE),  (6, 'CPU',     FALSE), (6, 'Monitor',       FALSE), (6, 'SSD',          FALSE);

-- Level 3 questions
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(3, 10, 'If a computer overheats often, which component should be checked?',                         'scenario', 3),
(3, 2,  'If a computer becomes slow when many programs are open, which component needs an upgrade?', 'scenario', 3),
(3, 5,  'If games run poorly because graphics are weak, which component is most relevant?',          'scenario', 3);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(7, 'Cooling System', TRUE),  (7, 'Keyboard', FALSE), (7, 'Monitor',      FALSE), (7, 'Mouse',        FALSE),
(8, 'RAM',            TRUE),  (8, 'Monitor',  FALSE), (8, 'Keyboard',     FALSE), (8, 'Power Supply', FALSE),
(9, 'GPU',            TRUE),  (9, 'Mouse',    FALSE), (9, 'SSD',          FALSE), (9, 'Keyboard',     FALSE);

-- Placeholder users (passwords must be reset via the app — hashes are invalid here)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin',   'admin@example.com',   '$2y$10$placeholder.change.me.admin',   'admin'),
('student', 'student@example.com', '$2y$10$placeholder.change.me.student', 'user');
