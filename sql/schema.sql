-- OMDS database schema
-- How to apply safely:
-- 1. Start MySQL in XAMPP/WAMP.
-- 2. Open http://localhost/OMDSAI/install.php in your browser, OR
--    in phpMyAdmin: create database `omds`, then Import this file.
-- 3. Run setup_admin.php if you imported SQL by hand (install.php already creates the admin).
-- This file does not drop existing tables.

CREATE DATABASE IF NOT EXISTS omds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE omds;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_user_id CHAR(10) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    gmail VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(80) NOT NULL DEFAULT '',
    last_name VARCHAR(80) NOT NULL DEFAULT '',
    middle_name VARCHAR(80) NOT NULL DEFAULT '',
    suffix VARCHAR(20) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    blood_type VARCHAR(16) NOT NULL DEFAULT '',
    role ENUM('user', 'personnel', 'organization', 'admin') NOT NULL DEFAULT 'user',
    account_status ENUM('active', 'pending_personnel', 'denied_personnel', 'inactive') NOT NULL DEFAULT 'active',
    is_disabled TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    date_of_birth DATE NULL,
    sex VARCHAR(20) NOT NULL DEFAULT '',
    religion VARCHAR(80) NOT NULL DEFAULT '',
    contact_number VARCHAR(40) NOT NULL DEFAULT '',
    emergency_contact VARCHAR(120) NOT NULL DEFAULT '',
    emergency_phone VARCHAR(40) NOT NULL DEFAULT '',
    medical_conditions TEXT,
    allergies TEXT,
    current_medications TEXT,
    medical_history TEXT,
    general_status TEXT,
    profile_picture VARCHAR(255) NOT NULL DEFAULT '',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS personnel_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    organization_name VARCHAR(160) NOT NULL,
    specialization VARCHAR(160) NOT NULL DEFAULT '',
    professional_role VARCHAR(160) NOT NULL DEFAULT '',
    professional_id_number VARCHAR(80) NOT NULL DEFAULT '',
    company_address VARCHAR(255) NOT NULL DEFAULT '',
    company_contact VARCHAR(160) NOT NULL DEFAULT '',
    company_id_file VARCHAR(255) NOT NULL DEFAULT '',
    review_status ENUM('pending', 'approved', 'denied') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    denial_reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS organizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    address VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('invited', 'active', 'inactive') NOT NULL DEFAULT 'active',
    invitation_token VARCHAR(64) NULL UNIQUE,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS organization_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    member_role VARCHAR(80) NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_org_user (organization_id, user_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qr_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medical_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT UNSIGNED NOT NULL,
    category VARCHAR(120) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS record_access (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT UNSIGNED NOT NULL,
    grantee_user_id INT UNSIGNED NOT NULL,
    purpose VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('pending', 'granted', 'revoked', 'denied') NOT NULL DEFAULT 'pending',
    is_emergency TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    granted_at DATETIME NULL,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (grantee_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    target_type VARCHAR(80) NOT NULL DEFAULT '',
    target_id VARCHAR(80) NOT NULL DEFAULT '',
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
);
