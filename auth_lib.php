<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/auth_config.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ====================== Utilitas Umum ====================== */

function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function mask_email(string $email): string
{
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
        return $email;
    }
    [$name, $domain] = $parts;
    $len = strlen($name);
    if ($len <= 2) {
        $masked = substr($name, 0, 1) . '*';
    } else {
        $masked = substr($name, 0, 1) . str_repeat('*', min($len - 2, 6)) . substr($name, -1);
    }
    return $masked . '@' . $domain;
}

/* ====================== Session & Remember Me ====================== */

function is_logged_in(): bool
{
    return !empty($_SESSION['status_login']);
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['status_login'] = true;
    $_SESSION['username']     = $user['username'];
    $_SESSION['user_id']      = (int)$user['id'];
}

function remember_set_cookie(string $value, int $expires): void
{
    setcookie(REMEMBER_COOKIE, $value, [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function remember_issue(int $userId): void
{
    global $koneksi;

    $koneksi->prepare("DELETE FROM remember_tokens WHERE expires_at < ?")
            ->execute([date('Y-m-d H:i:s')]);

    $selector  = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $expires   = time() + (REMEMBER_DAYS * 86400);

    $koneksi->prepare(
        "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)"
    )->execute([$userId, $selector, hash('sha256', $validator), date('Y-m-d H:i:s', $expires)]);

    remember_set_cookie($selector . ':' . $validator, $expires);
}

function remember_login(): bool
{
    global $koneksi;

    if (!defined('REMEMBER_COOKIE') || !isset($_COOKIE[REMEMBER_COOKIE])) {
        return false;
    }

    $cookie = $_COOKIE[REMEMBER_COOKIE];
    if ($cookie === '' || strpos($cookie, ':') === false) {
        return false;
    }
    [$selector, $validator] = explode(':', $cookie, 2);

    $stmt = $koneksi->prepare("SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > ?");
    $stmt->execute([$selector, date('Y-m-d H:i:s')]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !hash_equals($row['token_hash'], hash('sha256', $validator))) {
        remember_set_cookie('', time() - 3600);
        return false;
    }

    $stmt = $koneksi->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$row['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return false;
    }

    $koneksi->prepare("DELETE FROM remember_tokens WHERE id = ?")->execute([$row['id']]);
    login_user($user);
    remember_issue((int)$user['id']);
    return true;
}

function remember_forget(): void
{
    global $koneksi;

    if (!defined('REMEMBER_COOKIE') || !isset($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    $cookie = $_COOKIE[REMEMBER_COOKIE];
    if ($cookie !== '' && strpos($cookie, ':') !== false) {
        [$selector] = explode(':', $cookie, 2);
        $koneksi->prepare("DELETE FROM remember_tokens WHERE selector = ?")->execute([$selector]);
    }
    remember_set_cookie('', time() - 3600);
}

function do_logout(): void
{
    remember_forget();
    $_SESSION = [];
    session_regenerate_id(true);
}

/* ====================== Captcha ====================== */

function captcha_verify(string $input): bool
{
    $code = $_SESSION['captcha_code'] ?? '';
    $time = $_SESSION['captcha_time'] ?? 0;
    unset($_SESSION['captcha_code'], $_SESSION['captcha_time']);

    if ($code === '' || time() - $time > 600) {
        return false;
    }
    return hash_equals(strtoupper($code), strtoupper(trim($input)));
}

/* ====================== Pengiriman Email (PHPMailer) ====================== */

function kirim_otp($email_tujuan, $kode_otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chintyaaa45@gmail.com'; 
        $mail->Password   = 'fidp cjft kttw xygf'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('chintyaaa45@gmail.com', 'Portal Akademik');
        $mail->addAddress($email_tujuan);

        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi OTP Anda';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2>Verifikasi Akun</h2>
                <p>Kode OTP Anda adalah: <b style='font-size: 20px; color: #007bff;'>{$kode_otp}</b></p>
                <p>Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapa pun.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Gagal mengirim email: {$mail->ErrorInfo}");
        return false;
    }
}

function send_otp_mail(array $user, string $otp, string &$error): bool {
    $berhasil = kirim_otp($user['email'], $otp);
    if (!$berhasil) {
        $error = "Gagal mengirim email via SMTP Gmail.";
        return false;
    }
    return true;
}

/* ====================== OTP Lupa Password ====================== */

function otp_seconds_until_resend(int $userId): int
{
    global $koneksi;

    $stmt = $koneksi->prepare("SELECT created_at FROM password_resets WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return 0;
    }
    return max(0, OTP_RESEND_COOLDOWN - (time() - strtotime($row['created_at'])));
}

function otp_issue(array $user, string &$error = ''): bool
{
    global $koneksi;
    $userId = (int)$user['id'];

    $wait = otp_seconds_until_resend($userId);
    if ($wait > 0) {
        $error = "Tunggu $wait detik sebelum meminta kode baru.";
        return false;
    }

    $stmt = $koneksi->prepare("SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND created_at > ?");
    $stmt->execute([$userId, date('Y-m-d H:i:s', time() - 3600)]);
    if ((int)$stmt->fetchColumn() >= OTP_MAX_PER_HOUR) {
        $error = 'Terlalu banyak permintaan kode OTP. Silakan coba lagi dalam satu jam.';
        return false;
    }

    $otp = str_pad((string)random_int(0, (10 ** OTP_LENGTH) - 1), OTP_LENGTH, '0', STR_PAD_LEFT);

    $koneksi->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0")->execute([$userId]);

    $now = time();
    $koneksi->prepare(
        "INSERT INTO password_resets (user_id, otp_hash, created_at, expires_at) VALUES (?, ?, ?, ?)"
    )->execute([
        $userId,
        password_hash($otp, PASSWORD_DEFAULT),
        date('Y-m-d H:i:s', $now),
        date('Y-m-d H:i:s', $now + OTP_TTL),
    ]);
    $resetId = (int)$koneksi->lastInsertId();

    $mailError = '';
    if (!send_otp_mail($user, $otp, $mailError)) {
        $koneksi->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$resetId]);
        $error = 'Kode OTP gagal dikirim ke email. ' . $mailError;
        return false;
    }
    return true;
}

function otp_verify(int $userId, string $code, string &$error): bool
{
    global $koneksi;

    $stmt = $koneksi->prepare("SELECT * FROM password_resets WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $error = 'Kode OTP tidak berlaku lagi. Silakan kirim ulang kode.';
        return false;
    }
    if (strtotime($row['expires_at']) < time()) {
        $error = 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode.';
        return false;
    }
    if ((int)$row['attempts'] >= OTP_MAX_ATTEMPTS) {
        $error = 'Terlalu banyak percobaan yang salah. Silakan kirim ulang kode.';
        return false;
    }

    if (password_verify($code, $row['otp_hash'])) {
        $koneksi->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$row['id']]);
        return true;
    }

    $koneksi->prepare("UPDATE password_resets SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
    $sisa = OTP_MAX_ATTEMPTS - ((int)$row['attempts'] + 1);
    $error = $sisa > 0
        ? "Kode OTP salah. Sisa percobaan: $sisa."
        : 'Kode OTP salah dan batas percobaan habis. Silakan kirim ulang kode.';
    return false;
}

/* ====================== Tampilan Bersama ====================== */

function icon_avatar(): string
{
    return '<svg viewBox="0 0 60 60" aria-hidden="true"><g fill="none" stroke="#fff" stroke-width="2.4" stroke-linejoin="round">'
         . '<circle cx="30" cy="19" r="10.5"/>'
         . '<path d="M7 47c0-9 7-12 23-12s23 3 23 12z"/></g></svg>';
}

function icon_user(): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7.5" r="4.5"/>'
         . '<path d="M3 21c0-5 4-7.5 9-7.5s9 2.5 9 7.5z"/></svg>';
}

function icon_mail(): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 4.5h20v15H2z"/>'
         . '<path d="M2.5 6l9.5 7 9.5-7" fill="none" stroke="#5b8fd6" stroke-width="1.7"/></svg>';
}

function icon_lock(): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true">'
         . '<path fill-rule="evenodd" d="M6 10V7a6 6 0 0 1 12 0v3h1a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V11a1 1 0 0 1 1-1zM8 10h8V7a4_4 0 0 0-8 0z"/>'
         . '<circle cx="12" cy="15.5" r="1.9" fill="#4a7fcb"/></svg>';
}

function icon_refresh(): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.5-5.8"/><path d="M20 3v5h-5"/></svg>';
}

function auth_header(string $title): void
{
    ?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-body">
<main class="auth-wrap">
<div class="auth-box">
<?php
}

function auth_footer(): void
{
    ?>
</div>
</main>
</body>
</html>
<?php
}

function render_alert(?array $flash, string $error = ''): void
{
    if ($flash) {
        echo '<div class="alert ' . e($flash['type']) . '">' . e($flash['msg']) . '</div>';
    }
    if ($error !== '') {
        echo '<div class="alert error">' . e($error) . '</div>';
    }
}