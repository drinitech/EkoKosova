-- Skema per Postgres (Neon), perdoret ne prodhim ne Vercel.
-- Skema origjinale per MySQL (MAMP lokalisht) mbetet ne ekokosova.sql.

CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quotes (
    id SERIAL PRIMARY KEY,
    thenje_text VARCHAR(255) NOT NULL,
    autori VARCHAR(255) NOT NULL,
    autor_img VARCHAR(255) DEFAULT NULL
);

CREATE TABLE reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin SMALLINT NOT NULL DEFAULT 0,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires TIMESTAMP DEFAULT NULL,
    password_changed_at TIMESTAMP DEFAULT NULL
);
