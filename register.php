<?php
require_once __DIR__ . '/auth_lib.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error    = '';
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['cpassword'] ?? '');

    if (!csrf_check()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Silakan coba lagi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter, hanya huruf, angka, titik, dan garis bawah.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        $stmt = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar pada akun lain.';
            }
        }
    }

    if ($error === '') {
        try {
            $koneksi->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)")
                    ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash_set('success', 'Pendaftaran berhasil. Silakan login.');
            redirect('login.php');
        } catch (PDOException $ex) {
            $error = ($ex->getCode() === '23000')
                ? 'Username atau email sudah digunakan.'
                : 'Pendaftaran gagal. Silakan coba lagi.';
        }
    }
}

auth_header('Daftar');
?>
    <div class="avatar"><?= icon_avatar() ?></div>
    <h1 class="auth-title small">Sign In / Daftar</h1>
    <p class="auth-sub">Email akan terikat langsung dengan akun Anda dan dipakai untuk mengirim kode OTP jika lupa password.</p>

    <?php render_alert(null, $error); ?>

    <form method="post" action="register.php">
        <?= csrf_field() ?>

        <label class="field">
            <?= icon_user() ?>
            <input type="text" name="username" placeholder="Username" value="<?= e($username) ?>"
                   required autofocus autocomplete="username" maxlength="30">
        </label>

        <label class="field">
            <?= icon_mail() ?>
            <input type="email" name="email" placeholder="Email" value="<?= e($email) ?>"
                   required autocomplete="email" maxlength="100">
        </label>

        <label class="field">
            <?= icon_lock() ?>
            <input type="password" name="password" placeholder="Password (min. 8 karakter)"
                   required minlength="8" autocomplete="new-password">
        </label>

        <label class="field">
            <?= icon_lock() ?>
            <input type="password" name="cpassword" placeholder="Konfirmasi Password"
                   required minlength="8" autocomplete="new-password">
        </label>

        <button type="submit" class="btn-main">Daftar</button>
    </form>

    <p class="below">Sudah punya akun? <a href="login.php">Login</a></p>
<?php auth_footer(); ?>