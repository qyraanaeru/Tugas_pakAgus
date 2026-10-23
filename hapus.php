<?php
require_once('koneksi.php');

if(isset($_GET['nim'])){
    $nim = $_GET['nim'];
    
    // Ambil nama foto untuk dihapus dari folder
    $sql_foto = "SELECT FOTO FROM tabel_nilai WHERE NIM = ?";
    $row_foto = $koneksi->prepare($sql_foto);
    $row_foto->execute([$nim]);
    $hasil = $row_foto->fetch(PDO::FETCH_ASSOC);
    
    // Hapus file foto dari directory server (kecuali default.jpg)
    if($hasil && $hasil['FOTO'] != 'default.jpg' && !empty($hasil['FOTO'])){
        $file_path = 'images/' . $hasil['FOTO'];
        if(file_exists($file_path)){
            unlink($file_path);
        }
    }

    // Hapus baris data dari database
    $sql = "DELETE FROM tabel_nilai WHERE NIM = ?";
    $row = $koneksi->prepare($sql);
    
    if($row->execute([$nim])){
        echo '<script>alert("Data Penilaian Berhasil Dihapus!");window.location="index.php"</script>';
    } else {
        echo '<script>alert("Gagal menghapus data.");window.location="index.php"</script>';
    }
} else {
    // Jika diakses tanpa parameter nim
    header('Location: index.php');
}
?>