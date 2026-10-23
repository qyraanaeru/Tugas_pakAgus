-- Jalankan di database `data_mahasiswa` (phpMyAdmin > tab SQL)

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kode OTP untuk lupa password (yang disimpan hanya hash-nya, bukan kodenya)
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    otp_hash   VARCHAR(255) NOT NULL,
    attempts   TINYINT NOT NULL DEFAULT 0,
    used       TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX idx_user_created (user_id, created_at),
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Token "Remember me" (cookie login otomatis)
CREATE TABLE IF NOT EXISTS remember_tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    selector   CHAR(24) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun lama dipindahkan: username "admin", password "admin123".
-- PENTING: ganti 'admin@example.com' dengan email asli Anda agar OTP lupa password bisa diterima.
INSERT INTO users (username, email, password) VALUES
('admin', 'admin@example.com', '$2b$12$tCnVggUSzSliASgn65RaeO6tm/UoNt1i2peGxY.ORxBMjf.KjMtV.')
ON DUPLICATE KEY UPDATE username = username;
