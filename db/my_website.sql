CREATE DATABASE IF NOT EXISTS my_website;
USE my_website;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) UNIQUE NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    user_phone VARCHAR(20),
    
    company_name VARCHAR(255) NOT NULL,
    company_industry VARCHAR(100),
    company_description TEXT,
    company_website VARCHAR(255),
    company_contact_email VARCHAR(100),
    company_contact_phone VARCHAR(20),
    
    job_title VARCHAR(255) NOT NULL,
    location_country VARCHAR(100) DEFAULT 'Algeria',
    location_state VARCHAR(100),
    location_city VARCHAR(100),
    contract_type ENUM('full-time', 'part-time', 'contract', 'any') DEFAULT 'any',
    working_hours ENUM('9-5', 'flexible', 'any') DEFAULT 'any',
    salary DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'DZD',
    time_period ENUM('per year', 'per month', 'per hour') DEFAULT 'per year',
    job_description TEXT NOT NULL,
    
    applicant_cover_letter TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cv_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255),

    
    degree VARCHAR(255) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    graduation_year INT NOT NULL,

    
    job_title VARCHAR(255),
    company VARCHAR(255),
    start_date DATE,
    end_date DATE,
    job_description TEXT,

   
    skills TEXT,

    -- CV Upload
    cv_file VARCHAR(255) NOT NULL, -- Store the file path

    
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    job_type ENUM('Full-Time', 'Part-Time', 'Remote') NOT NULL,
    salary INT NOT NULL
);
/*


CREATE DATABASE IF NOT EXISTS my_website;
USE my_website;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job Listings Table (Linking with Users)
CREATE TABLE job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Reference to users table
    company_name VARCHAR(255) NOT NULL,
    company_industry VARCHAR(100),
    company_description TEXT,
    company_website VARCHAR(255),
    company_contact_email VARCHAR(100),
    company_contact_phone VARCHAR(20),
    job_title VARCHAR(255) NOT NULL,
    location_country VARCHAR(100) DEFAULT 'Algeria',
    location_state VARCHAR(100),
    location_city VARCHAR(100),
    contract_type ENUM('full-time', 'part-time', 'contract', 'any') DEFAULT 'any',
    working_hours ENUM('9-5', 'flexible', 'any') DEFAULT 'any',
    salary DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'DZD',
    time_period ENUM('per year', 'per month', 'per hour') DEFAULT 'per year',
    job_description TEXT NOT NULL,
    applicant_cover_letter TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- CV Uploads Table (Allow Multiple Uploads Per User)
CREATE TABLE cv_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Reference to users table
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL, -- Removed UNIQUE to allow multiple CVs
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255),
    degree VARCHAR(255) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    graduation_year INT NOT NULL,
    job_title VARCHAR(255),
    company VARCHAR(255),
    start_date DATE,
    end_date DATE,
    job_description TEXT,
    skills TEXT,
    cv_file VARCHAR(255) NOT NULL, -- Store the file path
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Jobs Table (More Robust Salary & Data Consistency)
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    job_type ENUM('Full-Time', 'Part-Time', 'Remote') NOT NULL,
    salary DECIMAL(10,2) NOT NULL -- Changed from INT to DECIMAL for precision
);



*/