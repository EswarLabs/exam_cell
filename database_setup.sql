CREATE TABLE IF NOT EXISTS exam_cell_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS exam_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    pdf_filename VARCHAR(255),
    pdf_path VARCHAR(500),
    posted_by INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES exam_cell_admins(id)
);

INSERT INTO exam_cell_admins (username, password, full_name)
VALUES (
    'examcell',
    '$2a$12$3k17c4QWNcTU./7LcBBSxOhtVu5o0GY5690lcAVRTg1hGHwU3Otni',
    'Exam Cell Administrator'
);
