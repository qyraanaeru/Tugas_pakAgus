<?php
require_once __DIR__ . '/auth_lib.php';

// Halaman ini hanya bisa dibuka setelah lolos captcha di forgot_password.php
$uid = (int)($_SESSION['reset_user_id'] ?? 0);
if ($uid === 0) {
    redirect('forgot_password.php');
}

$stmt = $koneksi->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_verified']);
    redirect('forgot_password.php');
}
$masked = mask_email($user['email']);
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Silakan coba lagi.';
    } elseif (($_POST['action'] ?? '') === 'resend') {
        // Kirim ulang OTP (server menolak jika belum lewat 1 menit)
        $err = '';
        if (otp_issue($user, $err)) {
            $devNote = (SMTP_USER === '') ? ' (Mode uji coba: isi email ada di mail_log/mail.log)' : '';
            flash_set('success', "Kode OTP baru telah dikirim ke email $masked.$devNote");
            redirect('verify_otp.php');
        }
        $error = $err;
    } else {
        $code = preg_replace('/\D/', '', implode('', (array)($_POST['otp'] ?? [])));
        if (strlen($code) !== OTP_LENGTH) {
            $error = 'Masukkan ' . OTP_LENGTH . ' digit kode OTP.';
        } else {
            $err = '';
            if (otp_verify($uid, $code, $err)) {
                $_SESSION['reset_verified']    = $uid;
                $_SESSION['reset_verified_at'] = time();
                redirect('reset_password.php');
            }
            $error = $err;
        }
    }
}

$wait   = otp_seconds_until_resend($uid);
$flash  = flash_get();

auth_header('Verifikasi OTP');
?>
    <div class="avatar"><?= icon_avatar() ?></div>
    <h1 class="auth-title small">Verifikasi OTP</h1>

    <?php if ($flash === null && $error === ''): ?>
        <div class="alert">Masukkan kode OTP yang telah dikirim ke email <?= e($masked) ?>.</div>
    <?php else: render_alert($flash, $error); endif; ?>

    <p class="auth-sub">Masukkan <?= (int)OTP_LENGTH ?> digit kode OTP dari email Anda. Kode berlaku <?= (int)(OTP_TTL / 60) ?> menit.</p>

    <form method="post" action="verify_otp.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="verify">
        <div class="otp-inputs">
            <?php for ($i = 0; $i < OTP_LENGTH; $i++): ?>
                <input type="text" name="otp[]" inputmode="numeric" maxlength="1" pattern="[0-9]" required
                       autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>" aria-label="Digit ke-<?= $i + 1 ?>">
            <?php endfor; ?>
        </div>
        <button type="submit" class="btn-main">Verifikasi</button>
    </form>

    <form method="post" action="verify_otp.php" class="below">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="resend">
        Belum menerima kode?
        <button type="submit" class="link-btn" id="resendBtn" data-wait="<?= (int)$wait ?>"
                <?= $wait > 0 ? 'disabled' : '' ?>>Kirim ulang kode</button>
        <span id="resendTimer"></span>
    </form>

    <p class="below"><a href="forgot_password.php">&larr; Ganti akun</a> &nbsp;|&nbsp; <a href="login.php">Login</a></p>

    <script>
    (function () {
        // Kotak OTP: pindah otomatis, mendukung backspace dan tempel (paste)
        var boxes = [].slice.call(document.querySelectorAll('.otp-inputs input'));
        boxes.forEach(function (box, i) {
            box.addEventListener('input', function () {
                box.value = box.value.replace(/\D/g, '').slice(0, 1);
                if (box.value && boxes[i + 1]) boxes[i + 1].focus();
            });
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !box.value && boxes[i - 1]) boxes[i - 1].focus();
            });
            box.addEventListener('paste', function (e) {
                var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                if (!text) return;
                e.preventDefault();
                boxes.forEach(function (b, k) { b.value = text[k] || ''; });
                boxes[Math.min(text.length, boxes.length) - 1].focus();
            });
        });
        boxes[0].focus();

        // Hitung mundur sebelum tombol "Kirim ulang kode" aktif (1 menit)
        var btn = document.getElementById('resendBtn');
        var label = document.getElementById('resendTimer');
        var left = parseInt(btn.getAttribute('data-wait'), 10) || 0;
        function fmt(s) { return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }
        function tick() {
            if (left > 0) {
                btn.disabled = true;
                label.textContent = '(tersedia dalam ' + fmt(left) + ')';
                left--;
                setTimeout(tick, 1000);
            } else {
                btn.disabled = false;
                label.textContent = '';
            }
        }
        tick();
    })();
    </script>
<?php auth_footer(); ?>
