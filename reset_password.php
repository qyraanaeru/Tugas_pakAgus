<?php
require_once __DIR__ . '/auth_lib.php';

// Hanya boleh dibuka setelah OTP terverifikasi (berlaku 15 menit)
$uid = (int)($_SESSION['reset_user_id'] ?? 0);
$ok  = $uid > 0
    && (int)($_SESSION['reset_verified'] ?? 0) === $uid
    && time() - (int)($_SESSION['reset_verified_at'] ?? 0) <= 900;
if (!$ok) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_verified'], $_SESSION['reset_verified_at']);
    redirect('forgot_password.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['cpassword'] ?? '');

    if (!csrf_check()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Silakan coba lagi.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        $koneksi->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
        // Semua perangkat yang memakai "Remember me" harus login ulang
        $koneksi->prepare("DELETE FROM remember_tokens WHERE user_id = ?")->execute([$uid]);

        unset($_SESSION['reset_user_id'], $_SESSION['reset_verified'], $_SESSION['reset_verified_at']);
        flash_set('success', 'Password berhasil diubah. Silakan login dengan password baru.');
        redirect('login.php');
    }
}

auth_header('Password Baru');
?>
    <div class="avatar"><?= icon_avatar() ?></div>
    <h1 class="auth-title small">Password Baru</h1>
    <p class="auth-sub">Verifikasi berhasil. Silakan buat password baru untuk akun Anda.</p>

    <?php render_alert(null, $error); ?>

    <form method="post" action="reset_password.php">
        <?= csrf_field() ?>

        <label class="field">
            <?= icon_lock() ?>
            <input type="password" name="password" placeholder="Password baru (min. 8 karakter)"
                   required minlength="8" autofocus autocomplete="new-password">
        </label>

        <label class="field">
            <?= icon_lock() ?>
            <input type="password" name="cpassword" placeholder="Konfirmasi Password"
                   required minlength="8" autocomplete="new-password">
        </label>

        <button type="submit" class="btn-main">Simpan Password</button>
    </form>
<?php auth_footer(); ?>
