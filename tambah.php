<?php
require_once('koneksi.php');

// Proses tambah data nilai ke database
if(isset($_POST['create'])){
    $nim    = $_POST['nim'];
    $nama   = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir']; 
    $kelas  = $_POST['kelas'];
    $n_web2 = $_POST['n_web2'];
    $b_ing2 = $_POST['b_ing2'];
    $b_indo = $_POST['b_indo'];

    // --- PROSES UPLOAD FOTO ---
    $foto = "default.jpg"; // Default file
    
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file = $_FILES['foto']['name'];
        $ukuran_file = $_FILES['foto']['size'];
        $tmp_file = $_FILES['foto']['tmp_name'];
        
        // HANYA MENGIZINKAN JPG/JPEG
        $ekstensi_diperbolehkan = array('jpg', 'jpeg');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));

        // Validasi Ekstensi
        if(in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            // Validasi Ukuran File (Maksimal 100KB = 102400 Bytes)
            if($ukuran_file <= 102400) {
                $foto = $nim . '_' . time() . '.' . $ekstensi; // Menambahkan timestamp agar unik
                
                // Images
                if (!is_dir('images')) {
                    mkdir('images', 0755, true);
                }
                
                move_uploaded_file($tmp_file, 'images/' . $foto);
            } else {
                echo '<script>alert("UKURAN FILE TERLALU BESAR! Maksimal 100KB."); window.history.back();</script>';
                exit; 
            }
        } else {
            echo '<script>alert("EKSTENSI FILE TIDAK DIIZINKAN! Hanya format JPG/JPEG."); window.history.back();</script>';
            exit; 
        }
    } else {
        echo '<script>alert("Foto wajib diunggah!"); window.history.back();</script>';
        exit;
    }

    // Proses Hitung
    $total = $n_web2 + $b_ing2 + $b_indo;
    $rata_rata = $total / 3;

    // Menentukan Keterangan 
    $keterangan = ($rata_rata >= 60) ? "BERHASIL" : "MENGULANG";

    // Menentukan Predikat dan Yudisium
    switch (true) {
        case ($rata_rata > 80):
            $predikat = "A"; $yudisium = "Sangat Memuaskan"; break;
        case ($rata_rata >= 70):
            $predikat = "B"; $yudisium = "Memuaskan"; break;
        case ($rata_rata >= 60):
            $predikat = "C"; $yudisium = "Cukup"; break;
        case ($rata_rata >= 50):
            $predikat = "D"; $yudisium = "Kurang"; break;
        default:
            $predikat = "E"; $yudisium = "Sangat Kurang"; break;
    }

    // Simpan ke database
    $data = [$nim, $nama, $tanggal_lahir, $foto, $kelas, $n_web2, $b_ing2, $b_indo, $total, $rata_rata, $keterangan, $predikat, $yudisium];
    
    $sql = 'INSERT INTO tabel_nilai (NIM, NAMA, TANGGAL_LAHIR, FOTO, KELAS, N_WEB2, B_ING2, B_INDO, TOTAL, RATA_RATA, KETERANGAN, PREDIKAT, YUDISIUM) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $row = $koneksi->prepare($sql);
    
    if($row->execute($data)){
        echo '<script>alert("Berhasil Tambah Data Penilaian!");window.location="index.php"</script>';
    } else {
        echo '<script>alert("Gagal menambahkan data.");</script>';
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Tambah Data Nilai</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Form Tambah Data Nilai</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <h5 class="mb-3 border-bottom pb-2">DATA MAHASISWA</h5>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>NIM</label>
                                    <input type="text" class="form-control" name="nim" required placeholder="Masukkan NIM">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>NAMA MAHASISWA</label>
                                    <input type="text" class="form-control" name="nama" required placeholder="Nama Lengkap">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>KELAS</label>
                                    <input type="text" class="form-control" name="kelas" required placeholder="Contoh : M51">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>TANGGAL LAHIR</label>
                                    <input type="date" class="form-control" name="tanggal_lahir" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>FOTO RESMI (Max 100KB, format JPG/JPEG)</label>
                                    <!-- Batasi pilihan file explorer ke jpg/jpeg -->
                                    <input type="file" class="form-control-file" name="foto" accept=".jpg, .jpeg" required>
                                    <small class="text-danger">* Foto disarankan berukuran rasio 3x4</small>
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4 border-bottom pb-2">INPUT NILAI</h5>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Nilai Web 2</label>
                                    <input type="number" class="form-control" name="n_web2" required placeholder="0-100" min="0" max="100">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Nilai Bahasa Inggris 2</label>
                                    <input type="number" class="form-control" name="b_ing2" required placeholder="0-100" min="0" max="100">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Nilai Bahasa Indonesia</label>
                                    <input type="number" class="form-control" name="b_indo" required placeholder="0-100" min="0" max="100">
                                </div>
                            </div>
                            
                            <div class="mt-4 text-right">
                                <a href="index.php" class="btn btn-secondary btn-md">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-md" name="create">
                                    <i class="fa fa-save"></i> Simpan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>