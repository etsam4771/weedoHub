-- Dictionary Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS dictionary_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dictionary_db;

-- Main words table
CREATE TABLE IF NOT EXISTS words (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word_name VARCHAR(255) NOT NULL,
    meaning_hindi TEXT NOT NULL,
    meaning_english TEXT NOT NULL,
    example TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_word_name (word_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories/Sub-dictionaries table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Word collections - linking words to categories
CREATE TABLE IF NOT EXISTS word_collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word_id INT NOT NULL,
    category_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_word_category (word_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data
INSERT INTO words (word_name, meaning_hindi, meaning_english, example) VALUES
('Knowledge', 'ज्ञान', 'Information, understanding, or skill acquired through experience or education', 'Knowledge is power when applied wisely.'),
('Wisdom', 'बुद्धि', 'The quality of having experience, knowledge, and good judgment', 'With age comes wisdom and understanding.'),
('Learning', 'सीखना', 'The acquisition of knowledge or skills through study, experience, or teaching', 'Continuous learning is essential for growth.'),
('Education', 'शिक्षा', 'The process of receiving or giving systematic instruction', 'Education opens doors to opportunities.'),
('Dictionary', 'शब्दकोश', 'A book or electronic resource that lists words in alphabetical order', 'A dictionary helps you understand new words.');

-- Insert sample categories
INSERT INTO categories (category_name, description) VALUES
('Important Words', 'Collection of important vocabulary words'),
('Daily Use', 'Words used in daily conversation'),
('Academic', 'Words for academic purposes');

-- Link some words to categories
INSERT INTO word_collections (word_id, category_id) VALUES
(1, 1), (2, 1), (3, 2), (4, 1), (5, 3);
