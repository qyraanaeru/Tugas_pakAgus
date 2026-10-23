<?php
require_once('koneksi.php');

if(isset($_GET['nim'])){
    $nim_get = $_GET['nim'];
    
    $sql = "SELECT * FROM tabel_nilai WHERE NIM = ?";
    $row = $koneksi->prepare($sql);
    $row->execute([$nim_get]);
    $hasil = $row->fetch(PDO::FETCH_ASSOC);
    
    if(!$hasil){
        echo '<script>alert("Data tidak ditemukan!");window.location="index.php"</script>';
    }
}

if(isset($_POST['update'])){
    $nim    = $_POST['nim']; 
    $nama   = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $kelas  = $_POST['kelas'];
    $n_web2 = $_POST['n_web2'];
    $b_ing2 = $_POST['b_ing2'];
    $b_indo = $_POST['b_indo'];
    $foto_lama = $_POST['foto_lama'];

    // --- PROSES UPLOAD FOTO BARU ---
    $foto = $foto_lama; 
    
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nama_file = $_FILES['foto']['name'];
        $ukuran_file = $_FILES['foto']['size'];
        $tmp_file = $_FILES['foto']['tmp_name'];
        
        // HANYA MENGIZINKAN JPG/JPEG
        $ekstensi_diperbolehkan = array('jpg', 'jpeg');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));

        if(in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            if($ukuran_file <= 102400) {
                $foto = $nim . '_' . time() . '.' . $ekstensi;
                
                move_uploaded_file($tmp_file, 'images/' . $foto);
                
                // Hapus foto lama 
                if($foto_lama != 'default.jpg' && file_exists('images/'.$foto_lama)){
                    unlink('images/'.$foto_lama);
                }
            } else {
                echo '<script>alert("UKURAN FILE TERLALU BESAR! Maksimal 100KB."); window.history.back();</script>';
                exit;
            }
        } else {
            echo '<script>alert("EKSTENSI FILE TIDAK DIIZINKAN! Hanya format JPG/JPEG."); window.history.back();</script>';
            exit;
        }
    }

    // Proses Hitung
    $total = $n_web2 + $b_ing2 + $b_indo;
    $rata_rata = $total / 3;
    $keterangan = ($rata_rata >= 60) ? "BERHASIL" : "MENGULANG";

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

    $data = [$nama, $tanggal_lahir, $foto, $kelas, $n_web2, $b_ing2, $b_indo, $total, $rata_rata, $keterangan, $predikat, $yudisium, $nim];

    $sql = 'UPDATE tabel_nilai SET NAMA=?, TANGGAL_LAHIR=?, FOTO=?, KELAS=?, N_WEB2=?, B_ING2=?, B_INDO=?, TOTAL=?, RATA_RATA=?, KETERANGAN=?, PREDIKAT=?, YUDISIUM=? WHERE NIM=?';
    $row = $koneksi->prepare($sql);
    
    if($row->execute($data)){
        echo '<script>alert("Berhasil Edit Data!");window.location="index.php"</script>';
    } else {
        echo '<script>alert("Gagal edit data.");</script>';
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Edit Data Nilai</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Form Edit Data Nilai</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($hasil['FOTO']); ?>">
                            
                            <h5 class="mb-3 border-bottom pb-2">DATA MAHASISWA</h5>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>NIM (Tidak bisa diubah)</label>
                                    <input type="text" class="form-control" name="nim" value="<?php echo htmlspecialchars($hasil['NIM']); ?>" readonly>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>NAMA MAHASISWA</label>
                                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($hasil['NAMA']); ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>KELAS</label>
                                    <input type="text" class="form-control" name="kelas" value="<?php echo htmlspecialchars($hasil['KELAS']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>TANGGAL LAHIR</label>
                                    <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $hasil['TANGGAL_LAHIR']; ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>FOTO BARU (Kosongkan jika tidak diganti)</label>
                                    <input type="file" class="form-control-file" name="foto" accept=".jpg, .jpeg">
                                    <small class="text-danger">* Maksimal 100KB, format JPG/JPEG, rasio 3x4.</small>
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-4 border-bottom pb-2">INPUT NILAI</h5>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Nilai Web 2</label>
                                    <input type="number" class="form-control" name="n_web2" value="<?php echo $hasil['N_WEB2']; ?>" required min="0" max="100">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Nilai Bahasa Inggris 2</label>
                                    <input type="number" class="form-control" name="b_ing2" value="<?php echo $hasil['B_ING2']; ?>" required min="0" max="100">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Nilai Bahasa Indonesia</label>
                                    <input type="number" class="form-control" name="b_indo" value="<?php echo $hasil['B_INDO']; ?>" required min="0" max="100">
                                </div>
                            </div>
                            
                            <div class="mt-4 text-right">
                                <a href="index.php" class="btn btn-secondary btn-md">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-success btn-md" name="update">
                                    <i class="fa fa-save"></i> Update Data
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