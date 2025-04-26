-- Tạo database
CREATE DATABASE IF NOT EXISTS midterm_webapp;
USE midterm_webapp;

-- Bảng users để lưu thông tin người dùng
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,  -- UUID cho user ID
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,  -- Lưu mật khẩu đã được hash
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng email_verification để lưu mã xác thực email
CREATE TABLE email_verification (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(6) NOT NULL,  -- Mã xác thực 6 số
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used BOOLEAN DEFAULT FALSE
);

-- Bảng user_sessions để quản lý phiên đăng nhập
CREATE TABLE user_sessions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng password_reset để quản lý reset mật khẩu
CREATE TABLE password_resets (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng user_profiles để lưu thông tin profile bổ sung
CREATE TABLE user_profiles (
    user_id VARCHAR(36) PRIMARY KEY,
    avatar_url TEXT,
    bio TEXT,
    phone VARCHAR(20),
    address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng login_attempts_ip để lưu số lần đăng nhập sai theo IP và thời gian khóa
CREATE TABLE login_attempts_ip (
    ip VARCHAR(45) PRIMARY KEY,
    attempts INT DEFAULT 0,
    last_attempt DATETIME,
    lock_until DATETIME
);

-- Index để tối ưu tìm kiếm
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_user_sessions_token ON user_sessions(token);
CREATE INDEX idx_password_resets_token ON password_resets(token);
CREATE INDEX idx_email_verification_token ON email_verification(token);
CREATE INDEX idx_email_verification_email ON email_verification(email);