<?php
echo "File lib_func Berhasil Dipanggil!<br>";

// Nyalakan error reporting untuk melacak penyebab layar putih
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('lib_func.php');

// Proses login sekarang ditangani oleh login.php
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function header_web() {
    echo '
    <div class="jumbotron jumbotron-fluid bg-dark text-white text-center mb-0" style="padding: 2rem 1rem;">
        <div class="container">
            <h1 class="display-4"><i class="fa fa-graduation-cap"></i> PORTAL AKADEMIK</h1>
            <p class="lead">Sistem Informasi Penilaian Mahasiswa</p>
        </div>
    </div>';
}

function navigator_web() {
    echo '
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="dashboard.php">SIAKAD</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link text-white" href="dashboard.php"><i class="fa fa-home"></i> Beranda</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#"><i class="fa fa-user"></i> Profile</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#"><i class="fa fa-download"></i> Download</a></li>
            </ul>
            <ul class="navbar-nav ml-auto">';
            // Tombol Login/Logout di ujung kanan atas
            if(!isset($_SESSION['status_login'])) {
                echo '<li class="nav-item"><a class="btn btn-warning font-weight-bold" href="login.php"><i class="fa fa-sign-in"></i> LOG IN</a></li>';
            } else {
                echo '<li class="nav-item"><a class="btn btn-danger font-weight-bold" href="logout.php"><i class="fa fa-sign-out"></i> LOG OUT</a></li>';
            }
    echo '  </ul>
        </div>
    </nav>';
}

function form_login() {
    echo '
    <div class="card shadow-sm mb-3" id="form-login">
        <div class="card-header bg-success text-white text-center font-weight-bold">
            <i class="fa fa-lock"></i> LOGIN USER
        </div>
        <div class="card-body text-center">
            <p class="card-text text-muted small">Silakan login untuk mengakses data nilai mahasiswa.</p>
            <a href="login.php" class="btn btn-success btn-sm btn-block font-weight-bold">LOG IN</a>
            <a href="register.php" class="btn btn-outline-secondary btn-sm btn-block mt-2">Belum punya akun? Daftar</a>
        </div>
    </div>';
}

function info_user() {
    echo '
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-info text-white text-center font-weight-bold">
            <i class="fa fa-user-circle"></i> INFO USER
        </div>
        <div class="card-body text-center">
            <h5 class="card-title">Selamat Datang, ' . htmlspecialchars($_SESSION['username'] ?? 'Admin') . '!</h5>
            <p class="card-text text-muted">Anda memiliki akses ke kelola nilai.</p>
            <a href="index.php" class="btn btn-primary btn-sm btn-block"><i class="fa fa-cogs"></i> Kelola Data Nilai</a>
        </div>
    </div>';
}

function layar_user() {
    // Memeriksa status login
    if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
        info_user();
    } else {
        form_login();
    }
}

function info_client() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = $_SERVER['HTTP_USER_AGENT'];
    echo '
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white text-center font-weight-bold">
            <i class="fa fa-desktop"></i> INFO CLIENT
        </div>
        <div class="card-body" style="font-size: 11px;">
            <strong>IP:</strong> ' . $ip . '<br>
            <strong>Browser/OS:</strong> ' . substr($browser, 0, 40) . '...
        </div>
    </div>';
}

function menu_kiri() {
    layar_user();
    echo '<br>';
    info_client();
}

function rss_berita_it() {
    echo '
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-danger text-white text-center font-weight-bold">
            <i class="fa fa-newspaper-o"></i> BERITA IT
        </div>
        <div class="card-body p-2 text-center">
            <small>Menampilkan RSS dari detikinet.com</small>
            <hr class="mt-1 mb-1">
            <ul class="text-left" style="font-size:12px; padding-left:20px;">
                <li><a href="#">Tren AI 2026 Meningkat</a></li>
                <li><a href="#">Tips Cyber Security</a></li>
            </ul>
        </div>
    </div>';
}

function kutipan() {
    echo '
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark text-center font-weight-bold">
            <i class="fa fa-quote-left"></i> KUTIPAN HARI INI
        </div>
        <div class="card-body p-2 text-center">
            <blockquote class="blockquote mb-0" style="font-size: 13px;">
                <p>"Pendidikan adalah senjata paling ampuh yang bisa Anda gunakan untuk mengubah dunia."</p>
                <footer class="blockquote-footer">Nelson Mandela</footer>
            </blockquote>
        </div>
    </div>';
}

function menu_kanan() {
    rss_berita_it();
    kutipan();
}

function footer_web() {
    echo '
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <small>Copyright &copy; ' . date("Y") . ' - Chintya Ayu Ramadhani</small>
    </footer>';
}
?>