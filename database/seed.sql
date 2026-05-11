INSERT INTO devices (name, description) VALUES
('Computer', 'A computer is an electronic device made of several hardware components that work together to process, store and display information.');

INSERT INTO components (device_id, name, description, purpose, difficulty_level, image_path, slot_position) VALUES
(1, 'CPU',          'Unitatea Centrală de Procesare este procesorul principal al calculatorului.',        'Execută instrucțiuni și efectuează calcule.',                         1, NULL, 'cpu-socket'),
(1, 'RAM',          'Memoria RAM este o memorie temporară folosită în timp ce programele rulează.',      'Stochează temporar date pentru acces rapid.',                         1, NULL, 'ram-slot'),
(1, 'SSD',          'Un SSD este un dispozitiv de stocare care păstrează datele chiar și când calculatorul este oprit.', 'Stochează permanent fișiere, programe și sistemul de operare.', 1, NULL, 'storage-bay'),
(1, 'Motherboard',  'Placa de bază este placa principală de circuite a calculatorului.',                'Conectează componentele principale și permite comunicarea dintre ele.', 2, NULL, 'board-slot'),
(1, 'GPU',          'Procesorul grafic procesează imaginile și elementele grafice.',                   'Ajută la afișarea imaginilor, videoclipurilor și jocurilor.',          2, NULL, 'pcie-slot'),
(1, 'Power Supply', 'Sursa de alimentare transformă energia electrică de la priză în energie utilizabilă.', 'Furnizează energie electrică tuturor componentelor.',              2, NULL, 'psu-bay'),
(1, 'Monitor',      'Monitorul este un dispozitiv de ieșire care afișează informații vizuale.',        'Afișează text, imagini, videoclipuri și interfața utilizatorului.',    1, NULL, NULL),
(1, 'Keyboard',     'Tastatura este un dispozitiv de intrare folosit pentru introducerea textului și a comenzilor.', 'Permite utilizatorului să introducă informații în calculator.', 1, NULL, NULL),
(1, 'Mouse',        'Mouse-ul este un dispozitiv de intrare folosit pentru controlarea cursorului de pe ecran.', 'Permite utilizatorului să facă click, să selecteze și să interacționeze cu elemente.', 1, NULL, NULL),
(1, 'Cooling System','Sistemul de răcire împiedică supraîncălzirea componentelor.',                    'Elimină căldura din componente precum CPU-ul și GPU-ul.',              3, NULL, 'cooler-mount');

INSERT INTO levels (device_id, level_number, title, description, game_type, min_score_required) VALUES
(1, 1, 'Introducere componente', 'Descopera rolul componentelor de baza ale unui computer.',                      'learn',     0),
(1, 2, 'Plaseaza componentele',  'Trage fiecare componenta la locul ei in schema calculatorului.',                'drag_drop', 70),
(1, 3, 'Potriveste descrierile', 'Asociaza fiecare componenta cu descrierea sau rolul sau corect.',               'matching',  70),
(1, 4, 'Test final',             'Raspunde la intrebari mixte pentru a-ti demonstra cunostintele dobandite.',     'quiz',      70);

INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(2, 1, 'Plaseaza CPU in schema calculatorului.',      'drag_drop', 1),
(2, 2, 'Plaseaza RAM in schema calculatorului.',      'drag_drop', 1),
(2, 3, 'Plaseaza SSD-ul in schema calculatorului.',   'drag_drop', 1),
(2, 4, 'Plaseaza placa de baza in schema.',           'drag_drop', 2),
(2, 5, 'Plaseaza GPU-ul in schema calculatorului.',   'drag_drop', 2),
(2, 6, 'Plaseaza sursa de alimentare in schema.',     'drag_drop', 2);

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

INSERT INTO questions (level_id, component_id, question_text, question_type, difficulty) VALUES
(4, 1,  'Care componentă execută instrucțiuni și efectuează calcule?',                                  'single_choice', 1),
(4, 2,  'Care componentă stochează temporar datele în timp ce programele rulează?',                     'single_choice', 1),
(4, 3,  'Care componentă stochează permanent fișierele?',                                               'single_choice', 1),
(4, 4,  'Care componentă conectează părțile principale ale calculatorului?',                            'single_choice', 2),
(4, 5,  'Care componentă este responsabilă în principal de procesarea graficii?',                       'single_choice', 2),
(4, 6,  'Care componentă furnizează energie electrică pieselor calculatorului?',                        'single_choice', 2),
(4, 10, 'Dacă un calculator se supraîncălzește des, ce componentă ar trebui verificată?',               'scenario',      3),
(4, 2,  'Dacă un calculator devine lent când sunt deschise multe programe, ce componentă trebuie îmbunătățită?', 'scenario',      3),
(4, 5,  'Dacă jocurile rulează slab deoarece grafica este slabă, care componentă este cea mai relevantă?', 'scenario',      3);

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

INSERT INTO users (username, email, password_hash, role) VALUES
('admin',   'admin@example.com',   '$2y$10$placeholder.change.me.admin',   'admin'),
('student', 'student@example.com', '$2y$10$placeholder.change.me.student', 'user');
