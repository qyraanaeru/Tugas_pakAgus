<?php
require_once __DIR__ . '/auth_lib.php';

// Sudah login -> langsung ke halaman data nilai
if (is_logged_in()) {
    redirect('index.php');
}
// Pernah centang "Remember me" -> login otomatis lewat cookie
if (remember_login()) {
    redirect('index.php');
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Silakan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            if (!empty($_POST['remember'])) {
                remember_issue((int)$user['id']);
            }
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}

auth_header('Login');
?>
    <div class="avatar"><?= icon_avatar() ?></div>
    <h1 class="auth-title">Customer Login</h1>

    <?php render_alert(flash_get(), $error); ?>

    <form method="post" action="login.php">
        <?= csrf_field() ?>

        <label class="field">
            <?= icon_user() ?>
            <input type="text" name="username" placeholder="Username" value="<?= e($username) ?>"
                   required autofocus autocomplete="username">
        </label>

        <label class="field">
            <?= icon_lock() ?>
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        </label>

        <div class="row-between">
            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <a class="italic plain" href="forgot_password.php">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-main">Login</button>
    </form>

    <p class="below">Belum pernah login? <a href="register.php">Sign in / Daftar</a></p>
<?php auth_footer(); ?>
