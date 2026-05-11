INSERT INTO devices (name, description) VALUES
('Computer', 'A computer is an electronic device made of several hardware components that work together to process, store and display information.');

INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path, slot_position) VALUES
(1, 'CPU',          'The Central Processing Unit is the main processor of the computer.',              'Executes instructions and performs calculations.',               1, NULL, 'cpu-socket'),
(1, 'RAM',          'Random Access Memory is temporary memory used while programs are running.',       'Stores data temporarily for quick access.',                     1, NULL, 'ram-slot'),
(1, 'SSD',          'A Solid State Drive is a storage device that keeps data even when turned off.',   'Stores files, programs and the operating system permanently.',  1, NULL, 'storage-bay'),
(1, 'Motherboard',  'The motherboard is the main circuit board of the computer.',                      'Connects and allows communication between the main components.', 2, NULL, 'board-slot'),
(1, 'GPU',          'The Graphics Processing Unit processes images and graphics.',                     'Helps render images, videos and games.',                        2, NULL, 'pcie-slot'),
(1, 'Power Supply', 'The power supply converts electricity from the wall into usable power.',          'Provides electrical power to all components.',                  2, NULL, 'psu-bay'),
(1, 'Monitor',      'The monitor is an output device that displays visual information.',               'Shows text, images, videos and the user interface.',            1, NULL, NULL),
(1, 'Keyboard',     'The keyboard is an input device used to type text and commands.',                 'Allows the user to enter information into the computer.',       1, NULL, NULL),
(1, 'Mouse',        'The mouse is an input device used to control the pointer on the screen.',         'Allows the user to click, select and interact with elements.',  1, NULL, NULL),
(1, 'Cooling System','The cooling system keeps components from overheating.',                          'Removes heat from components such as the CPU and GPU.',         3, NULL, 'cooler-mount');

-- Level 1: Learn (reading page with component info)
-- Level 2: Drag & Drop (place components on diagram)
-- Level 3: Matching (think & match)
-- Level 4: Final Quiz (mixed questions)
INSERT INTO levels (device_id, level_number, title, description, game_type, min_score_required) VALUES
(1, 1, 'Introducere componente', 'Descopera rolul componentelor de baza ale unui computer.',                      'learn',     0),
(1, 2, 'Plaseaza componentele',  'Trage fiecare componenta la locul ei in schema calculatorului.',                'drag_drop', 70),
(1, 3, 'Potriveste descrierile', 'Asociaza fiecare componenta cu descrierea sau rolul sau corect.',               'matching',  70),
(1, 4, 'Test final',             'Raspunde la intrebari mixte pentru a-ti demonstra cunostintele dobandite.',     'quiz',      70);

-- Level 2 drag_drop questions (one question per draggable component)
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(2, 1, 'Plaseaza CPU in schema calculatorului.',      'drag_drop', 1),
(2, 2, 'Plaseaza RAM in schema calculatorului.',      'drag_drop', 1),
(2, 3, 'Plaseaza SSD-ul in schema calculatorului.',   'drag_drop', 1),
(2, 4, 'Plaseaza placa de baza in schema.',           'drag_drop', 2),
(2, 5, 'Plaseaza GPU-ul in schema calculatorului.',   'drag_drop', 2),
(2, 6, 'Plaseaza sursa de alimentare in schema.',     'drag_drop', 2);

-- Level 3 matching questions
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(3, 1, 'Executa instructiunile si efectueaza calculele.',              'matching', 1),
(3, 2, 'Stocheaza temporar datele cat timp programele ruleaza.',       'matching', 1),
(3, 3, 'Pastreaza fisierele si sistemul de operare permanent.',        'matching', 1),
(3, 4, 'Conecteaza si permite comunicarea intre componentele principale.', 'matching', 2),
(3, 5, 'Proceseaza imaginile si grafica.',                             'matching', 2),
(3, 6, 'Furnizeaza energie electrica tuturor componentelor.',          'matching', 2);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(7,  'CPU',          1), (7,  'RAM',          0), (7,  'GPU',   0), (7,  'SSD',          0),
(8,  'RAM',          1), (8,  'SSD',          0), (8,  'CPU',   0), (8,  'Power Supply', 0),
(9,  'SSD',          1), (9,  'RAM',          0), (9,  'CPU',   0), (9,  'Keyboard',     0),
(10, 'Motherboard',  1), (10, 'Monitor',      0), (10, 'Mouse', 0), (10, 'SSD',          0),
(11, 'GPU',          1), (11, 'Power Supply', 0), (11, 'RAM',   0), (11, 'Keyboard',     0),
(12, 'Power Supply', 1), (12, 'CPU',          0), (12, 'GPU',   0), (12, 'SSD',          0);

-- Level 4 final quiz questions
INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(4, 1,  'Which component executes instructions and performs calculations?',                              'single_choice', 1),
(4, 2,  'Which component stores data temporarily while programs are running?',                           'single_choice', 1),
(4, 3,  'Which component stores files permanently?',                                                     'single_choice', 1),
(4, 4,  'Which component connects the main parts of the computer?',                                      'single_choice', 2),
(4, 5,  'Which component is mainly responsible for graphics processing?',                                'single_choice', 2),
(4, 6,  'Which component provides electrical power to the computer parts?',                              'single_choice', 2),
(4, 10, 'If a computer overheats often, which component should be checked?',                             'scenario',      3),
(4, 2,  'If a computer becomes slow when many programs are open, which component needs an upgrade?',     'scenario',      3),
(4, 5,  'If games run poorly because graphics are weak, which component is most relevant?',              'scenario',      3);

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(13, 'CPU',          1), (13, 'RAM',          0), (13, 'Monitor',      0), (13, 'Keyboard',     0),
(14, 'RAM',          1), (14, 'SSD',          0), (14, 'Power Supply', 0), (14, 'Mouse',        0),
(15, 'SSD',          1), (15, 'RAM',          0), (15, 'CPU',          0), (15, 'Keyboard',     0),
(16, 'Motherboard',  1), (16, 'Monitor',      0), (16, 'Mouse',        0), (16, 'SSD',          0),
(17, 'GPU',          1), (17, 'Power Supply', 0), (17, 'Keyboard',     0), (17, 'RAM',          0),
(18, 'Power Supply', 1), (18, 'CPU',          0), (18, 'Monitor',      0), (18, 'SSD',          0),
(19, 'Cooling System',1),(19, 'Keyboard',     0), (19, 'Monitor',      0), (19, 'Mouse',        0),
(20, 'RAM',          1), (20, 'Monitor',      0), (20, 'Keyboard',     0), (20, 'Power Supply', 0),
(21, 'GPU',          1), (21, 'Mouse',        0), (21, 'SSD',          0), (21, 'Keyboard',     0);

-- Placeholder users (passwords must be reset via the app — hashes are invalid here)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin',   'admin@example.com',   '$2y$10$placeholder.change.me.admin',   'admin'),
('student', 'student@example.com', '$2y$10$placeholder.change.me.student', 'user');
