<?php
// Konfigurasi Dasar Aplikasi
define('APP_NAME', 'Portal Akademik');

// Konfigurasi "Remember Me"
define('REMEMBER_COOKIE', 'data_mahasiswa_remember_cookie');
define('REMEMBER_DAYS', 30);

// Konfigurasi OTP & Batasan Waktu
define('OTP_LENGTH', 6);
define('OTP_TTL', 600);            // Berlaku 10 menit (dalam detik)
define('OTP_RESEND_COOLDOWN', 60);  // Jeda kirim ulang 60 detik
define('OTP_MAX_PER_HOUR', 5);     // Maksimal permintaan per jam
define('OTP_MAX_ATTEMPTS', 3);     // Maksimal salah input OTP

// Konfigurasi Email / SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'chintyaaa45@gmail.com');  // Email Anda
define('SMTP_PASS', 'fidpcjftkttwxygf');   // App Password Google Anda
define('SMTP_SECURE', 'tls');
define('SMTP_VERIFY_PEER', true);
define('MAIL_FROM', 'chintyaaa45@gmail.com');
define('MAIL_FROM_NAME', 'Portal Akademik');