<?php
require_once __DIR__ . '/auth_lib.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error      = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if (!csrf_check()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Silakan coba lagi.';
    } elseif (!captcha_verify((string)($_POST['captcha'] ?? ''))) {
        $error = 'Verifikasi captcha salah. Silakan coba lagi.';
    } elseif ($identifier === '') {
        $error = 'Masukkan username atau email Anda.';
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$identifier, strtolower($identifier)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Akun dengan username/email tersebut tidak ditemukan.';
        } else {
            $uid    = (int)$user['id'];
            $masked = mask_email($user['email']);
            $devNote = (SMTP_USER === '') ? ' (Mode uji coba: isi email ada di mail_log/mail.log)' : '';

            if (otp_seconds_until_resend($uid) > 0) {
                $_SESSION['reset_user_id'] = $uid;
                unset($_SESSION['reset_verified']);
                flash_set('success', "Kode OTP sudah dikirim ke email $masked. Silakan cek inbox Anda.");
                redirect('verify_otp.php');
            }

            $err = '';
            if (otp_issue($user, $err)) {
                $_SESSION['reset_user_id'] = $uid;
                unset($_SESSION['reset_verified']);
                flash_set('success', "Kode OTP telah dikirim ke email $masked yang terikat dengan akun Anda.$devNote");
                redirect('verify_otp.php');
            }
            $error = $err;
        }
    }
}

auth_header('Lupa Password');
?>
    <div class="avatar"><?= icon_avatar() ?></div>
    <h1 class="auth-title small">Lupa Password</h1>
    <p class="auth-sub">Masukkan username atau email akun Anda. Kode OTP akan dikirim ke email yang terikat dengan akun tersebut.</p>

    <?php render_alert(null, $error); ?>

    <form method="post" action="forgot_password.php">
        <?= csrf_field() ?>

        <label class="field">
            <?= icon_user() ?>
            <input type="text" name="identifier" placeholder="Username atau Email" value="<?= e($identifier) ?>"
                   required autofocus autocomplete="username">
        </label>

        <p class="captcha-label">Verifikasi bahwa Anda bukan robot</p>
        <div class="captcha-box">
            <img id="captchaImg" src="captcha.php?t=<?= time() ?>" alt="Gambar captcha" width="222" height="72">
            <button type="button" id="captchaRefresh" title="Ganti gambar" aria-label="Ganti gambar captcha">
                <?= icon_refresh() ?>
            </button>
        </div>
        <label class="field noicon">
            <input type="text" name="captcha" placeholder="Ketik kode pada gambar" required
                   autocomplete="off" autocapitalize="characters" maxlength="5">
        </label>

        <button type="submit" class="btn-main">Kirim Kode OTP</button>
    </form>

    <p class="below"><a href="login.php">&larr; Kembali ke Login</a></p>

    <script>
    document.getElementById('captchaRefresh').addEventListener('click', function () {
        document.getElementById('captchaImg').src = 'captcha.php?t=' + Date.now();
        var input = document.querySelector('input[name="captcha"]');
        input.value = '';
        input.focus();
    });
    </script>
<?php auth_footer(); ?>