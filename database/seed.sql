-- Teacher Load Assignment System — Seed Data
-- Run after schema.sql

USE teacher_load_system;

-- Sample teachers
INSERT INTO teachers (employee_id, first_name, last_name, email, phone, department, max_units, min_units, employment_type, status) VALUES
('T001', 'Maria', 'Santos', 'maria.santos@school.edu', '09171234501', 'Computer Science', 24.0, 12.0, 'full_time', 'active'),
('T002', 'Juan', 'Dela Cruz', 'juan.delacruz@school.edu', '09171234502', 'Computer Science', 24.0, 12.0, 'full_time', 'active'),
('T003', 'Ana', 'Reyes', 'ana.reyes@school.edu', '09171234503', 'Information Technology', 18.0, 9.0, 'part_time', 'active'),
('T004', 'Pedro', 'Garcia', 'pedro.garcia@school.edu', '09171234504', 'Mathematics', 24.0, 12.0, 'full_time', 'active'),
('T005', 'Lisa', 'Lim', 'lisa.lim@school.edu', '09171234505', 'Information Technology', 24.0, 12.0, 'full_time', 'active'),
('T006', 'Carlos', 'Tan', 'carlos.tan@school.edu', '09171234506', 'Computer Science', 15.0, 6.0, 'contractual', 'active');

-- Sample teacher expertise
INSERT INTO teacher_expertise (teacher_id, subject_area, proficiency_level) VALUES
(1, 'Programming', 'primary'),
(1, 'Database Systems', 'secondary'),
(2, 'Data Structures', 'primary'),
(2, 'Algorithms', 'primary'),
(3, 'Web Development', 'primary'),
(3, 'Networking', 'secondary'),
(4, 'Discrete Mathematics', 'primary'),
(4, 'Calculus', 'secondary'),
(5, 'System Administration', 'primary'),
(5, 'Cybersecurity', 'secondary'),
(6, 'Software Engineering', 'primary');

-- Sample subjects
INSERT INTO subjects (code, name, description, units, lecture_hours, lab_hours, department, semester, year_level) VALUES
('CS101', 'Introduction to Programming', 'Fundamentals of programming using Python', 3.0, 2.0, 1.0, 'Computer Science', '1st', 1),
('CS102', 'Data Structures', 'Arrays, linked lists, trees, graphs', 3.0, 2.0, 1.0, 'Computer Science', '2nd', 1),
('CS201', 'Database Systems', 'Relational DB design, SQL, normalization', 3.0, 2.0, 1.0, 'Computer Science', '1st', 2),
('CS202', 'Algorithms', 'Sorting, searching, dynamic programming', 3.0, 3.0, 0.0, 'Computer Science', '2nd', 2),
('IT101', 'Web Development', 'HTML, CSS, JavaScript, PHP', 3.0, 1.0, 2.0, 'Information Technology', '1st', 1),
('IT201', 'System Administration', 'Linux, Windows server management', 3.0, 2.0, 1.0, 'Information Technology', '1st', 2),
('MATH101', 'Discrete Mathematics', 'Logic, sets, graphs, combinatorics', 3.0, 3.0, 0.0, 'Mathematics', '1st', 1),
('CS301', 'Software Engineering', 'SDLC, agile, design patterns', 3.0, 2.0, 1.0, 'Computer Science', '1st', 3);

-- Sample schedules
INSERT INTO schedules (subject_id, day_of_week, start_time, end_time, room, section, school_year, semester) VALUES
(1, 'Mon', '08:00:00', '10:00:00', 'Lab 1', 'CS-1A', '2024-2025', '1st'),
(1, 'Wed', '08:00:00', '10:00:00', 'Lab 1', 'CS-1A', '2024-2025', '1st'),
(2, 'Tue', '10:00:00', '12:00:00', 'Room 201', 'CS-1B', '2024-2025', '2nd'),
(2, 'Thu', '10:00:00', '12:00:00', 'Room 201', 'CS-1B', '2024-2025', '2nd'),
(3, 'Mon', '13:00:00', '15:00:00', 'Lab 2', 'CS-2A', '2024-2025', '1st'),
(3, 'Wed', '13:00:00', '15:00:00', 'Lab 2', 'CS-2A', '2024-2025', '1st'),
(4, 'Fri', '08:00:00', '11:00:00', 'Room 301', 'CS-2B', '2024-2025', '2nd'),
(5, 'Tue', '14:00:00', '16:00:00', 'Lab 3', 'IT-1A', '2024-2025', '1st'),
(5, 'Thu', '14:00:00', '16:00:00', 'Lab 3', 'IT-1A', '2024-2025', '1st'),
(6, 'Mon', '10:00:00', '12:00:00', 'Lab 4', 'IT-2A', '2024-2025', '1st'),
(7, 'Wed', '08:00:00', '11:00:00', 'Room 101', 'MATH-1A', '2024-2025', '1st'),
(8, 'Fri', '13:00:00', '15:00:00', 'Room 202', 'CS-3A', '2024-2025', '1st');

-- Sample teacher availability
INSERT INTO teacher_availability (teacher_id, day_of_week, start_time, end_time, is_preferred) VALUES
(1, 'Mon', '08:00:00', '17:00:00', 1),
(1, 'Wed', '08:00:00', '17:00:00', 1),
(1, 'Fri', '08:00:00', '12:00:00', 1),
(2, 'Tue', '08:00:00', '17:00:00', 1),
(2, 'Thu', '08:00:00', '17:00:00', 1),
(3, 'Tue', '14:00:00', '18:00:00', 1),
(3, 'Thu', '14:00:00', '18:00:00', 1),
(4, 'Mon', '08:00:00', '17:00:00', 1),
(4, 'Wed', '08:00:00', '17:00:00', 1),
(5, 'Mon', '10:00:00', '17:00:00', 1),
(6, 'Fri', '13:00:00', '17:00:00', 1);

