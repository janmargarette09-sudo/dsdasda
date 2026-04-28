-- Run this ONCE after schema.sql to set default password: admin123
INSERT INTO users (username, password_hash, full_name, email, role) VALUES (
    'admin',
    '$2y$10$Qpp98dRmJWWJY3bnC7evSeJV/J/O6ud84LBvuUaNKU03OJvYCbXo6',
    'Program Chair Admin',
    'admin@school.edu',
    'admin'
) ON DUPLICATE KEY UPDATE
    password_hash = '$2y$10$Qpp98dRmJWWJY3bnC7evSeJV/J/O6ud84LBvuUaNKU03OJvYCbXo6';
