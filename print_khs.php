<?php
require_once('koneksi.php');

// Mengecek apakah parameter NIM dikirimkan
if(isset($_GET['nim'])){
    $nim = $_GET['nim'];
    
    // Mengambil data spesifik berdasarkan NIM
    $sql = "SELECT * FROM tabel_nilai WHERE NIM = :nim";
    $stmt = $koneksi->prepare($sql);
    $stmt->execute([':nim' => $nim]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika data tidak ditemukan
    if(!$data){
        die("Data mahasiswa tidak ditemukan.");
    }
} else {
    die("NIM tidak dipilih.");
}

// Memulai Output Buffering
ob_start();
?>
<style>
    /* CSS langsung diletakkan tanpa tag head */
    .header {
        text-align: center;
        margin-bottom: 10px;
    }
    .header h4, .header h5 {
        margin: 3px 0;
        font-family: Arial, sans-serif;
    }
    hr {
        border: 0.5px solid black;
        margin-bottom: 20px;
        width: 100%;
    }
    .title-doc {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        text-decoration: underline;
        margin-bottom: 5px;
        font-family: Arial, sans-serif;
    }
    .subtitle-doc {
        text-align: center;
        font-size: 11px;
        margin-bottom: 25px;
        font-family: Arial, sans-serif;
    }
    
    /* Tabel Identitas */
    .info-table {
        width: 100%;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    .info-table td {
        padding: 3px 0;
        vertical-align: top;
    }
    
    /* Tabel Nilai & Predikat */
    .main-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    .main-table th, .main-table td {
        border: 1px solid black;
        padding: 7px;
    }
    .main-table th {
        background-color: #f8f9fa;
        text-align: center;
        font-weight: bold;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    
    /* Catatan */
    .catatan {
        font-size: 10px;
        margin-top: 15px;
        line-height: 1.4;
        font-family: Arial, sans-serif;
    }
    
    /* Tanda Tangan */
    .ttd-table {
        width: 100%;
        margin-top: 40px;
        text-align: center;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
</style>

<!-- Langsung menggunakan tag <page> sebagai root dokumen HTML -->
<page backtop="15mm" backbottom="15mm" backleft="20mm" backright="20mm">
    
    <!-- Header -->
    <div class="header">
        <h4>PROGRAM STUDI SISTEM INFORMASI</h4>
        <h4>KARTU HASIL STUDI</h4>
        <h5>TAHUN AKADEMIK 2026/2027</h5>
    </div>
    
    <hr>
    
    <div class="title-doc">KARTU HASIL STUDI MAHASISWA</div>
    <div class="subtitle-doc">Daftar Nilai Akademik</div>

    <!-- Informasi Mahasiswa -->
    <table align="center" class="info-table">
        <tr>
            <td width="100"><b>NIM</b></td>
            <td width="15">:</td>
            <td width="310"><?php echo htmlspecialchars($data['NIM']); ?></td>
            <td rowspan="4" align="right">
                <img src="images/<?php echo htmlspecialchars($data['FOTO']); ?>" style="width: 75px; height: 95px; object-fit: cover; border: 1px solid #000;">
            </td>
        </tr>
        <tr>
            <td><b>Nama</b></td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['NAMA']); ?></td>
        </tr>
        <tr>
            <td><b>Kelas</b></td>
            <td>:</td>
            <td><?php echo htmlspecialchars($data['KELAS']); ?></td>
        </tr>
        <tr>
            <td><b>Tanggal Lahir</b></td>
            <td>:</td>
            <td><?php echo date('d-m-Y', strtotime($data['TANGGAL_LAHIR'])); ?></td>
        </tr>
    </table>

    <!-- Tabel Nilai Mata Kuliah -->
    <table align="center" class="main-table">
        <thead>
            <tr>
                <th width="40">No</th>
                <th width="380">Mata Kuliah</th>
                <th width="80">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Web 2</td>
                <td class="text-center"><?php echo $data['N_WEB2']; ?></td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Bahasa Inggris</td>
                <td class="text-center"><?php echo $data['B_ING2']; ?></td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Bahasa Indonesia</td>
                <td class="text-center"><?php echo $data['B_INDO']; ?></td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><b>Total Nilai</b></td>
                <td class="text-center"><b><?php echo $data['TOTAL']; ?></b></td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"><b>Rata-rata</b></td>
                <td class="text-center"><b><?php echo number_format($data['RATA_RATA'], 2); ?></b></td>
            </tr>
        </tbody>
    </table>

    <!-- Tabel Predikat & Keterangan -->
    <table align="center" class="main-table">
        <tbody>
            <tr>
                <td width="150"><b>Keterangan</b></td>
                <td width="350"><?php echo $data['KETERANGAN']; ?></td>
            </tr>
            <tr>
                <td><b>Predikat</b></td>
                <td><?php echo $data['PREDIKAT']; ?></td>
            </tr>
            <tr>
                <td><b>Yudisium</b></td>
                <td><?php echo $data['YUDISIUM']; ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Catatan -->
    <div class="catatan">
        <b>Catatan:</b><br>
        Nilai akhir dihitung berdasarkan rata-rata dari tiga mata kuliah.<br>
        Kriteria nilai:<br>
        A = 80-100 (Sangat Memuaskan)<br>
        B = 70-79.99 (Memuaskan)<br>
        C = 60-69.99 (Cukup)<br>
        D = 50-59.99 (Kurang)<br>
        E = di bawah 50 (Sangat Kurang)
    </div>

    <!-- Tanda Tangan -->
    <table align="center" class="ttd-table">
        <tr>
            <td width="250">
                Mengetahui,<br>
                Dosen Pengampu<br><br><br><br><br>
                _________________________
            </td>
            <td width="250">
                Batang, <?php echo date('d-m-Y'); ?><br>
                Mahasiswa<br><br><br><br><br>
                <b><?php echo strtoupper(htmlspecialchars($data['NAMA'])); ?></b>
            </td>
        </tr>
    </table>

</page>
<?php
$html = ob_get_contents();
ob_end_clean();

require 'html2pdf/autoload.php';

try {
    $pdf = new Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en'); 
    $pdf->writeHTML($html);
    
    $nama_file = 'KHS_' . str_replace(' ', '_', $data['NAMA']) . '.pdf';
    $pdf->output($nama_file, 'I');
} catch (Exception $e) {
    $pdf->clean();
    print 'Terjadi kesalahan saat mencetak PDF: ' . $e->getMessage();
}
?>