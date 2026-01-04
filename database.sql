CREATE DATABASE IF NOT EXISTS job_portal;
USE job_portal;

-- Admin table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
imple Jobs table with only essential fields
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Basic Info
    title VARCHAR(200) NOT NULL,
    company VARCHAR(100) NOT NULL,
    description TEXT,
    job_link VARCHAR(500) NOT NULL,
    
    -- Key Fields You Asked For
    work_mode ENUM('Work from Home', 'On-site', 'Hybrid') DEFAULT 'On-site',
    employment_type ENUM('Full-time', 'Part-time', 'Internship') DEFAULT 'Full-time',
    experience_level ENUM('Freshers', '0-2 years', '2-5 years', '5+ years') DEFAULT 'Freshers',
    
    -- Simple Location & Category
    location VARCHAR(100),
    category VARCHAR(50),
    
    -- Dates & Status
    posted_date DATE NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Basic indexes
CREATE INDEX idx_active_posted ON jobs(is_active, posted_date DESC);
CREATE INDEX idx_category ON jobs(category, is_active);
CREATE INDEX idx_work_mode ON jobs(work_mode, is_active);
ALTER TABLE jobs ADD COLUMN application_deadline DATE AFTER posted_date;
