<?php
// Nyalakan error reporting untuk melacak penyebab layar putih
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('lib_func.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Template</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background-color: #f4f6f9; }
    </style>
</head>
<body>

    <!-- Header & Navigasi -->
    <?php header_web(); ?>
    <?php navigator_web(); ?>

    <div class="container-fluid mt-4 mb-5">
        <div class="row">
            
            <!-- Menu Kiri -->
            <div class="col-lg-3 col-md-4">
                <?php menu_kiri(); ?>
            </div>

            <!-- Content Utama -->
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-sm" style="min-height: 400px;">
                    <div class="card-body">
                        <h4 class="border-bottom pb-2">Selamat Datang di Portal Akademik</h4>
                        <p class="text-justify mt-3">
                            Ini adalah halaman utama dari sistem informasi akademik. 
                            Silakan lakukan <b>LOG IN</b> melalui panel di sebelah kiri atau klik tombol di pojok kanan atas untuk mengakses data nilai mahasiswa.
                        </p>
                        <div class="text-center mt-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135810.png" width="200" alt="Ilustrasi Akademik" style="opacity: 0.5;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Kanan -->
            <div class="col-lg-3 col-md-12 mt-4 mt-lg-0">
                <?php menu_kanan(); ?>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <?php footer_web(); ?>

</body>
</html>