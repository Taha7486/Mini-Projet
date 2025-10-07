-- Campus Events Management System Database Schema
-- Drop existing tables if they exist
DROP TABLE IF EXISTS attestations;
DROP TABLE IF EXISTS inscrit;
DROP TABLE IF EXISTS evenements;
DROP TABLE IF EXISTS organizer_requests;
DROP TABLE IF EXISTS organisateurs;
DROP TABLE IF EXISTS clubs;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS participants;
DROP TABLE IF EXISTS accounts;

-- Accounts table (base for all users)
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Participants table (regular users)
CREATE TABLE participants (
    participant_id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    year VARCHAR(20) NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    role ENUM('user', 'organizer') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_account (account_id),
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clubs table
CREATE TABLE clubs (
    club_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(admin_id),
    INDEX idx_name (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organisateurs table (organizers)
CREATE TABLE organisateurs (
    organisateur_id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    club_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE CASCADE,
    UNIQUE KEY unique_organizer_club (participant_id, club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Organizer requests table
CREATE TABLE organizer_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    club_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_type ENUM('become_organizer', 'change_clubs') DEFAULT 'become_organizer',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    decided_at TIMESTAMP NULL,
    decided_by INT NULL,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE CASCADE,
    FOREIGN KEY (decided_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Evenements table (events)
CREATE TABLE evenements (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(200) NOT NULL,
    date_event DATE NOT NULL,
    time_event VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    registered_count INT DEFAULT 0,
    image_url TEXT,
    club_id INT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(club_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES organisateurs(organisateur_id),
    INDEX idx_date (date_event),
    INDEX idx_club (club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inscrit table (event registrations)
CREATE TABLE inscrit (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES evenements(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (participant_id, event_id),
    INDEX idx_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attestations table (certificates)
CREATE TABLE attestations (
    attestation_id INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    pdf_path VARCHAR(255),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES inscrit(registration_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attestation (registration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account
-- Password: Admin123! (hashed with bcrypt)
INSERT INTO accounts (nom, email, password) VALUES 
('System Administrator', 'admin@campus.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO admins (account_id) VALUES (1);

-- Insert sample clubs
INSERT INTO clubs (nom, description, created_by) VALUES
('Computer Science Club', 'Technology and programming enthusiasts', 1),
('Engineering Society', 'For all engineering students', 1),
('Business Club', 'Business and entrepreneurship', 1),
('Arts & Culture Society', 'Arts, music, and cultural events', 1),
('Sports Committee', 'Sports and fitness activities', 1),
('Environmental Club', 'Sustainability and environment', 1);
