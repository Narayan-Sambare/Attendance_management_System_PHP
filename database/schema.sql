-- Database Schema for Attendance Management System

-- Create Database
CREATE DATABASE IF NOT EXISTS attendance_management;
USE attendance_management;

-- Students Table
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    class VARCHAR(50) NOT NULL,
    section VARCHAR(10),
    date_of_birth DATE,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active'
);

-- Courses/Subjects Table
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    total_sessions INT DEFAULT 0
);

-- Attendance Records Table
CREATE TABLE attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Excused') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, course_id, attendance_date)
);

-- Insert Sample Students
INSERT INTO students (roll_number, first_name, last_name, email, phone, class, section, date_of_birth) VALUES
('2024001', 'Rahul', 'Sharma', 'rahul.sharma@school.edu', '9876543210', '10th Grade', 'A', '2009-03-15'),
('2024002', 'Priya', 'Patel', 'priya.patel@school.edu', '9876543211', '10th Grade', 'A', '2009-05-20'),
('2024003', 'Amit', 'Kumar', 'amit.kumar@school.edu', '9876543212', '10th Grade', 'B', '2009-07-10'),
('2024004', 'Sneha', 'Singh', 'sneha.singh@school.edu', '9876543213', '10th Grade', 'A', '2009-04-25'),
('2024005', 'Vikram', 'Reddy', 'vikram.reddy@school.edu', '9876543214', '10th Grade', 'B', '2009-06-30');

-- Insert Sample Courses
INSERT INTO courses (course_code, course_name, class, total_sessions) VALUES
('MATH101', 'Mathematics', '10th Grade', 60),
('ENG101', 'English', '10th Grade', 50),
('SCI101', 'Science', '10th Grade', 55),
('SOC101', 'Social Studies', '10th Grade', 45);

-- Insert Sample Attendance Records
INSERT INTO attendance (student_id, course_id, attendance_date, status) VALUES
(1, 1, '2024-10-01', 'Present'),
(1, 1, '2024-10-02', 'Present'),
(1, 1, '2024-10-03', 'Absent'),
(2, 1, '2024-10-01', 'Present'),
(2, 1, '2024-10-02', 'Late'),
(2, 1, '2024-10-03', 'Present'),
(3, 1, '2024-10-01', 'Absent'),
(3, 1, '2024-10-02', 'Present'),
(3, 1, '2024-10-03', 'Present');

-- PL/SQL Procedure to Calculate Attendance Percentage
DELIMITER //

CREATE PROCEDURE calculate_attendance_percentage(
    IN p_student_id INT,
    IN p_course_id INT,
    OUT attendance_percentage DECIMAL(5,2)
)
BEGIN
    DECLARE total_classes INT;
    DECLARE attended_classes INT;
    
    -- Count total attendance records
    SELECT COUNT(*) INTO total_classes
    FROM attendance
    WHERE student_id = p_student_id AND course_id = p_course_id;
    
    -- Count present and late classes (both count as attendance)
    SELECT COUNT(*) INTO attended_classes
    FROM attendance
    WHERE student_id = p_student_id 
    AND course_id = p_course_id 
    AND status IN ('Present', 'Late');
    
    -- Calculate percentage
    IF total_classes > 0 THEN
        SET attendance_percentage = (attended_classes / total_classes) * 100;
    ELSE
        SET attendance_percentage = 0;
    END IF;
END //

DELIMITER ;

-- View for Student Attendance Summary
CREATE VIEW student_attendance_summary AS
SELECT 
    s.student_id,
    s.roll_number,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.class,
    s.section,
    c.course_code,
    c.course_name,
    COUNT(a.attendance_id) AS total_classes,
    SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) AS classes_attended,
    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS classes_missed,
    ROUND((SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) / 
           COUNT(a.attendance_id)) * 100, 2) AS attendance_percentage
FROM students s
LEFT JOIN attendance a ON s.student_id = a.student_id
LEFT JOIN courses c ON a.course_id = c.course_id
WHERE s.status = 'Active'
GROUP BY s.student_id, c.course_id;

-- Query to Generate Monthly Attendance Report
-- (Run this with specific date parameters)
SELECT 
    s.roll_number,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    c.course_name,
    DATE_FORMAT(a.attendance_date, '%Y-%m') AS month,
    COUNT(a.attendance_id) AS total_days,
    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present,
    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
    SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS late,
    ROUND((SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) / 
           COUNT(a.attendance_id)) * 100, 2) AS attendance_percentage
FROM students s
JOIN attendance a ON s.student_id = a.student_id
JOIN courses c ON a.course_id = c.course_id
GROUP BY s.student_id, c.course_id, DATE_FORMAT(a.attendance_date, '%Y-%m')
ORDER BY month DESC, student_name;