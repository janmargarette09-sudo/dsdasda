-- Teacher Load Assignment System — Database Schema
-- Run this first to create all tables

CREATE DATABASE IF NOT EXISTS teacher_load_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teacher_load_system;

-- Users table (Program Chair / Admin accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin','chair') DEFAULT 'chair',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    department VARCHAR(50),
    max_units DECIMAL(4,1) DEFAULT 24.0,
    min_units DECIMAL(4,1) DEFAULT 12.0,
    employment_type ENUM('full_time','part_time','contractual') DEFAULT 'full_time',
    status ENUM('active','inactive','on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teachers_status (status),
    INDEX idx_teachers_dept (department)
) ENGINE=InnoDB;

-- Teacher expertise / specializations
CREATE TABLE IF NOT EXISTS teacher_expertise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_area VARCHAR(100) NOT NULL,
    proficiency_level ENUM('primary','secondary','tertiary') DEFAULT 'primary',
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    units DECIMAL(3,1) NOT NULL DEFAULT 3.0,
    lecture_hours DECIMAL(4,1) DEFAULT 3.0,
    lab_hours DECIMAL(4,1) DEFAULT 0.0,
    department VARCHAR(50),
    semester ENUM('1st','2nd','summer') DEFAULT '1st',
    year_level INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subjects_dept (department),
    INDEX idx_subjects_active (is_active)
) ENGINE=InnoDB;

-- Subject prerequisites
CREATE TABLE IF NOT EXISTS subject_prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    prerequisite_id INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_prereq (subject_id, prerequisite_id)
) ENGINE=InnoDB;

-- Schedule time slots
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    day_of_week ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    section VARCHAR(20),
    school_year VARCHAR(20),
    semester ENUM('1st','2nd','summer') DEFAULT '1st',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    INDEX idx_schedules_day (day_of_week)
) ENGINE=InnoDB;

-- Teacher availability slots
CREATE TABLE IF NOT EXISTS teacher_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    day_of_week ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_preferred TINYINT(1) DEFAULT 1,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Assignments (teacher ↔ schedule)
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    schedule_id INT NOT NULL,
    assigned_by INT,
    assignment_type ENUM('auto','manual') DEFAULT 'auto',
    rationale TEXT,
    status ENUM('active','removed','pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_teacher_schedule (teacher_id, schedule_id),
    INDEX idx_assignments_teacher (teacher_id),
    INDEX idx_assignments_schedule (schedule_id)
) ENGINE=InnoDB;

-- Audit log
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action)
) ENGINE=InnoDB;

