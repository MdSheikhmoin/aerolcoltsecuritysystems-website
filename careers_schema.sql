USE aerolcol_leads;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255) NOT NULL,
    employment_type VARCHAR(100) NOT NULL,
    experience VARCHAR(100) DEFAULT NULL,
    qualification TEXT DEFAULT NULL,
    description TEXT NOT NULL,
    responsibilities TEXT DEFAULT NULL,
    requirements TEXT DEFAULT NULL,
    compensation TEXT DEFAULT NULL,
    performance_expectations TEXT DEFAULT NULL,
    why_join TEXT DEFAULT NULL,
    status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS job_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('text', 'textarea', 'number', 'yes_no', 'select') NOT NULL DEFAULT 'text',
    options TEXT DEFAULT NULL,
    required TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_job_questions_job
        FOREIGN KEY (job_id)
        REFERENCES jobs(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,

    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(100) NOT NULL,
    whatsapp VARCHAR(100) DEFAULT NULL,
    current_location VARCHAR(255) DEFAULT NULL,
    qualification VARCHAR(255) DEFAULT NULL,
    years_experience DECIMAL(4,1) DEFAULT NULL,
    relevant_experience TEXT DEFAULT NULL,
    uae_experience TEXT DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,

    cv_original_name VARCHAR(255) DEFAULT NULL,
    cv_stored_name VARCHAR(255) DEFAULT NULL,
    cv_path VARCHAR(500) DEFAULT NULL,

    match_score DECIMAL(5,2) DEFAULT NULL,
    match_breakdown JSON DEFAULT NULL,

    status ENUM(
        'new',
        'review',
        'shortlisted',
        'interview',
        'rejected',
        'hired'
    ) NOT NULL DEFAULT 'new',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_applications_job
        FOREIGN KEY (job_id)
        REFERENCES jobs(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS application_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    answer TEXT DEFAULT NULL,

    CONSTRAINT fk_application_answers_application
        FOREIGN KEY (application_id)
        REFERENCES applications(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_application_answers_question
        FOREIGN KEY (question_id)
        REFERENCES job_questions(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_jobs_status ON jobs(status);
CREATE INDEX idx_jobs_slug ON jobs(slug);
CREATE INDEX idx_applications_job ON applications(job_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_score ON applications(match_score);
CREATE INDEX idx_applications_created ON applications(created_at);